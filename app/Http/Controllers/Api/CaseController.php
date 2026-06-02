<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\CaseModel;
use App\Traits\ApiResponse;
use App\Traits\HandleDocuments;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
    use ApiResponse , HandleDocuments;
    // 🔹 عرض كل القضايا
    public function index()
    {
        $cases = CaseModel::with(['creator', 'sessions', 'lawyers' , 'parties'])
            ->latest()
            ->paginate(10);

        return response()->json($cases);
    }

            // 🔹 إنشاء قضية جديدة
        public function store(StoreCaseRequest $request)
        {
            $data = $request->safe()->only([
                'case_number', 'title', 'description', 'type', 'court_name', 'start_date'
            ]);
            $data['status'] = CaseModel::STATUS_PENDING;
            $data['created_by'] = auth()->id();

            DB::beginTransaction();
            try {
                $case = CaseModel::create($data);

                $user = auth()->user();

                if ($user->hasRole('avocato')) {

                    $case->parties()->create([
                        'user_id' => $request->client_id,
                        'role_in_case' => $request->role_in_case ?? 'plaintiff',
                    ]);
                    $case->lawyers()->attach($user->id, ['side' => 'plaintiff']);

                } elseif ($user->hasRole('client')) {
                    $case->parties()->create([
                        'user_id' => $user->id,
                        'role_in_case' => $request->role_in_case ?? 'plaintiff',
                    ]);
                    $case->lawyers()->attach($request->lawyer_id, ['side' => $request->side ?? 'plaintiff']);

                } else {
                    if ($request->has('parties')) {
                        $case->parties()->createMany($request->parties);
                    }
                    if ($request->has('lawyers')) {
                        $lawyersData = [];
                        foreach ($request->lawyers as $lawyer) {
                            $lawyersData[$lawyer['lawyer_id']] = ['side' => $lawyer['side'] ?? null];
                        }
                        $case->lawyers()->attach($lawyersData);
                    }
                }

                if ($request->hasFile('documents')) {
                    $this->uploadDocuments(
                        $request,
                        $case->id,
                        auth()->id()
                    );
                }

                DB::commit();

                return $this->successResponse(
                    $case->load(['creator', 'parties', 'lawyers', 'documents']),
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
            'parties.user',
            'lawyers',
            'sessions',
            'documents',
            'judgments'
        ])->findOrFail($id);

        return response()->json($case);
    }

    // 🔹 تحديث قضية
    public function update(Request $request, $id)
    {
        $case = CaseModel::findOrFail($id);

        $validated = $request->validate([
            'case_number' => 'sometimes|unique:cases,case_number,' . $case->id,
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'court_name' => 'nullable|string',
            'start_date' => 'nullable|date',
        ]);

        $case->update($validated);

        return response()->json([
            'message' => 'Case updated successfully',
            'data' => $case
        ]);
    }

    // 🔹 حذف قضية
    public function destroy($id)
    {
        $case = CaseModel::findOrFail($id);

        $case->delete();

        return response()->json([
            'message' => 'Case deleted successfully'
        ]);
    }


   public function overview()
    {
        $cases = CaseModel::paginate(10);

        $allCases = CaseModel::get();

        $totalCases     = $allCases->count();
        $activeCases    = $allCases->where('status', CaseModel::STATUS_ACTIVE)->count();
        $closedCases    = $allCases->where('status', CaseModel::STATUS_CLOSED)->count();
        $pendingCases   = $allCases->where('status', CaseModel::STATUS_PENDING)->count();
        $suspendedCases = $allCases->where('status', CaseModel::STATUS_SUSPENDED)->count();
        $flaggedCases   = $allCases->where('status', CaseModel::STATUS_FLAGGED)->count();

        return $this->successResponse([
            "cases" => $cases,
            "totalCases" => $totalCases,
            "activeCases" => $activeCases,
            "closedCases" => $closedCases,
            "pendingCases" => $pendingCases,
            "suspendedCases" => $suspendedCases,
            "flaggedCases" => $flaggedCases,
        ]);
    }

}
