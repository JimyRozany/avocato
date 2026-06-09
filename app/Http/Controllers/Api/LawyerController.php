<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class LawyerController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $lawyers = User::role('avocato')
            ->withCount(['casesAsLawyer as active_cases' => function ($q) {
                $q->where('cases.status', CaseModel::STATUS_ACTIVE);
            }])
            ->latest()
            ->paginate(10)
            ->through(fn ($lawyer) => [
                'id'                        => $lawyer->id,
                'name'                      => $lawyer->name,
                'email'                     => $lawyer->email,
                'active_cases'              => (int) $lawyer->active_cases,
                'rate'                      => $lawyer->rate,
                'is_active'                 => $lawyer->is_active,
                'created_at'                => $lawyer->created_at,
                'updated_at'                => $lawyer->updated_at,
                'bar_association_number'    => $lawyer->bar_association_number,
                'office_location'           => $lawyer->office_location,
                'years_of_experience'       => $lawyer->years_of_experience,
                'specialty'                 => $lawyer->specialty,
                'bio'                       => $lawyer->bio,
                'image'                     => $lawyer->image,
            ]);

        return $this->successResponse($lawyers);
    }

    public function show($id)
    {
        $lawyer = User::role('avocato')->findOrFail($id);

        return $this->successResponse($lawyer);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|max:20',
            'password' => 'required|min:6',
            'bar_association_number' => 'nullable|string|max:255',
            'office_location' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'specialty' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = false;
        $validated['rate'] = rand(50, 200);

        $lawyer = User::create($validated);
        $lawyer->assignRole('avocato');

        return $this->successResponse($lawyer, 'Lawyer created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        // return response()->json($request->all(), 200);
        $lawyer = User::role('avocato')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($lawyer->id)],
            'mobile' => 'sometimes|string|max:20',
            'password' => 'sometimes|min:6',
            'bar_association_number' => 'nullable|string|max:255',
            'office_location' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'specialty' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('lawyers', 'public');
        }

        $lawyer->update($validated);

        return $this->successResponse($lawyer->fresh(), 'Lawyer updated successfully');
    }

    public function destroy($id)
    {
        $lawyer = User::role('avocato')->findOrFail($id);
        $lawyer->delete();

        return $this->successResponse(null, 'Lawyer deleted successfully');
    }

    public function overview()
    {
        $user = auth()->user();
        $cases = $user->casesAsLawyer()->get();
        // return response()->json($cases , 200) ;

        return $this->successResponse([
            'totalCases'     => $cases->count(),
            'pendingCases'   => $cases->where('status', CaseModel::STATUS_PENDING)->count(),
            'activeCases'    => $cases->where('status', CaseModel::STATUS_ACTIVE)->count(),
            'closedCases'    => $cases->where('status', CaseModel::STATUS_CLOSED)->count(),
            'suspendedCases' => $cases->where('status', CaseModel::STATUS_SUSPENDED)->count(),
            'flaggedCases'   => $cases->where('status', CaseModel::STATUS_FLAGGED)->count(),
        ]);
    }

    public function overviewStats()
    {
        $total     = User::role('avocato')->count();
        $active    = User::role('avocato')->where('is_active', true)->count();
        $inactive  = User::role('avocato')->where('is_active', false)->get();
        $pending   = $inactive->whereNull('status')->count() + $inactive->where('status', 'pending')->count();
        $suspended = $inactive->where('status', 'suspended')->count();

        return $this->successResponse([
            'totalLawyers'        => $total,
            'activeLawyers'       => $active,
            'pendingVerification' => $pending,
            'suspendedAccounts'   => $suspended,
        ]);
    }

    public function toggleStatus($id)
    {
        $lawyer = User::role('avocato')->findOrFail($id);

        $lawyer->is_active = !$lawyer->is_active;
        $lawyer->save();

        return $this->successResponse($lawyer, 'Status updated');
    }

    public function getLawyerCases(Request $request, $lawyerId)
    {
        $lawyer = User::role('avocato')->findOrFail($lawyerId);

        $cases = $lawyer->casesAsLawyer()
            ->with(['creator', 'parties.user', 'sessions'])
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest()->get();

        return $this->successResponse([
            'lawyer' => $lawyer->only([
                'id', 'name', 'email', 'mobile', 'is_active',
                'bar_association_number', 'office_location', 'years_of_experience', 'specialty', 'bio', 'rate'
            ]),
            'cases'  => $cases,
        ]);
    }
}
