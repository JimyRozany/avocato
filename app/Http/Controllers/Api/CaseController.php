<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\CaseModel;
use App\Traits\ApiResponse;
use App\Traits\HandleDocuments;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
    use ApiResponse, HandleDocuments;
    // 🔹 عرض كل القضايا
    public function index()
    {
        $cases = CaseModel::with(['creator', 'client', 'lawyers', 'sessions', 'parties'])
            ->latest()
            ->paginate(10);

        return $this->successResponse($cases);
    }

    // 🔹 إنشاء قضية جديدة
    public function store(StoreCaseRequest $request)
    {
        $data = $request->safe()->only([
            'case_number',
            'title',
            'description',
            'type',
            'court_name',
            'start_date'
        ]);
        $data['status'] = CaseModel::STATUS_PENDING;
        $data['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $case = CaseModel::create($data);

            $user = auth()->user();

            if ($request->filled('client_id')) {
                $case->parties()->create([
                    'user_id'      => $request->client_id,
                    'role_in_case' => $request->role_in_case ?? 'plaintiff',
                ]);
            }

            if ($request->filled('lawyer_id')) {
                $case->lawyers()->attach($request->lawyer_id, [
                    'side' => $request->side ?? 'plaintiff',
                ]);
            }

            if ($user->hasRole('avocato')) {
                $case->lawyers()->attach($user->id, ['side' => 'plaintiff']);
            } elseif ($user->hasRole('client')) {
                $case->parties()->create([
                    'user_id'      => $user->id,
                    'role_in_case' => $request->role_in_case ?? 'plaintiff',
                ]);
            }

            if ($request->hasFile('documents')) {
                $this->uploadDocuments($request, $case->id, auth()->id());
            }

            DB::commit();

            return $this->successResponse(
                $case->load(['creator', 'client', 'lawyers', 'parties', 'documents.uploader']),
                'Created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Creation failed', 500, $e->getMessage());
        }
    }
    // 🔹 عرض قضية واحدة
    public function show($id)
    {
        $case = CaseModel::with([
            'creator',
            'client',
            'lawyers',
            'sessions',
            'documents.uploader',
            'judgments'
        ])->findOrFail($id);

        return $this->successResponse($case);
    }

    // 🔹 تحديث قضية
    public function update(Request $request, $id)
    {
        $case = CaseModel::findOrFail($id);


        // return response()->json($request->all(),200);

        $rules = [
            'case_number'  => 'sometimes|unique:cases,case_number,' . $case->id,
            'title'        => 'sometimes|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'nullable|string',
            'status'       => 'nullable|string',
            'court_name'   => 'nullable|string',
            'start_date'   => 'nullable|date',
            'role_in_case' => 'nullable|string',
            'side'         => 'nullable|string',
            'client_id'    => 'nullable|exists:users,id',
            'lawyer_id'    => 'nullable|exists:users,id',
        ];

        $validated = $request->validate($rules);

        $case->update(Arr::only($validated, [
            'case_number',
            'title',
            'description',
            'type',
            'status',
            'court_name',
            'start_date'
        ]));

        if ($request->filled('client_id')) {
            $case->parties()->whereHas('user.roles', fn($q) => $q->where('name', 'client'))
                ->delete();

            $case->parties()->create([
                'user_id'      => $request->client_id,
                'role_in_case' => $request->role_in_case ?? 'plaintiff',
            ]);
        }

        if ($request->filled('lawyer_id')) {
            $case->lawyers()->detach();

            $case->lawyers()->attach($request->lawyer_id, [
                'side' => $request->side ?? 'plaintiff',
            ]);
        }

        return $this->successResponse(
            $case->fresh()->load(['creator', 'client', 'lawyers', 'parties', 'documents.uploader']),
            'Updated successfully'
        );
    }

    // 🔹 حذف قضية
    public function destroy($id)
    {
        $case = CaseModel::findOrFail($id);

        $case->delete();

        return $this->successResponse(null, 'Deleted successfully');
    }


    public function forceClose($id)
    {
        $case = CaseModel::findOrFail($id);
        $case->update(['status' => CaseModel::STATUS_CLOSED]);

        return $this->successResponse($case->fresh()->load(['creator', 'client', 'lawyers', 'parties', 'documents.uploader']), 'Case closed successfully');
    }

    public function changeStatus(Request $request, $id)
    {
        $case = CaseModel::findOrFail($id);

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    CaseModel::STATUS_PENDING,
                    CaseModel::STATUS_ACTIVE,
                    CaseModel::STATUS_SUSPENDED,
                    CaseModel::STATUS_FLAGGED,
                    CaseModel::STATUS_CLOSED,
                ]),
            ],
        ]);

        $case->update(['status' => $validated['status']]);

        return $this->successResponse(
            $case->fresh()->load(['creator', 'client', 'lawyers', 'parties', 'documents.uploader']),
            'Status updated successfully'
        );
    }

    public function uploadDocumentsToCase(Request $request, $id)
    {
        $case = CaseModel::findOrFail($id);

        $request->validate([
            'documents'   => 'required|array',
            'documents.*' => 'required|file|max:51200',
            'titles'      => 'nullable|array',
            'titles.*'    => 'string|max:255',
            'types'       => 'nullable|array',
            'types.*'     => 'string|max:255',
        ]);

        $documents = $this->uploadDocuments($request, $case->id, auth()->id());
        foreach ($documents as $document) {
            $document->load('uploader');
        }


        return $this->successResponse($documents, 'Documents uploaded successfully', 201);
    }


    public function overview()
    {
        $allCases = CaseModel::all();

        return $this->successResponse([
            "totalCases"     => $allCases->where('deleted_at' , null)->count(),
            "activeCases"    => $allCases->where('status', CaseModel::STATUS_ACTIVE)->count(),
            "closedCases"    => $allCases->where('status', CaseModel::STATUS_CLOSED)->count(),
            "pendingCases"   => $allCases->where('status', CaseModel::STATUS_PENDING)->count(),
            "suspendedCases" => $allCases->where('status', CaseModel::STATUS_SUSPENDED)->count(),
            "flaggedCases"   => $allCases->where('status', CaseModel::STATUS_FLAGGED)->count(),
        ]);
    }

    public function dashboard()
    {
        return $this->successResponse([
            "totalUsers"       => \App\Models\User::count(),
            "activeCases"      => CaseModel::where('status', CaseModel::STATUS_ACTIVE)->count(),
            "pendingApprovals" => CaseModel::where('status', CaseModel::STATUS_PENDING)->count(),
            "closedCases"      => CaseModel::where('status', CaseModel::STATUS_CLOSED)->count(),
        ]);
    }

    public function caseChart()
    {
        $months = collect();
        for ($i = 2; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }

        $stats = CaseModel::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw("COUNT(CASE WHEN status = '" . CaseModel::STATUS_ACTIVE . "' THEN 1 END) as active_cases"),
            DB::raw("COUNT(CASE WHEN status = '" . CaseModel::STATUS_PENDING . "' THEN 1 END) as pending_cases"),
        )
            ->where('created_at', '>=', now()->subMonths(3)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $chart = $months->map(fn($month) => [
            'month'         => $month,
            'active_cases'  => (int) ($stats->get($month)?->active_cases ?? 0),
            'pending_cases' => (int) ($stats->get($month)?->pending_cases ?? 0),
        ]);

        return $this->successResponse($chart);
    }
}
