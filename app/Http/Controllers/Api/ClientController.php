<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $clients = User::where('type', 'client')
            ->withCount('caseParticipations as total_cases')
            ->latest()
            ->paginate(10)
            ->through(fn ($client) => [
                'id'           => $client->id,
                'name'         => $client->name,
                'email'        => $client->email,
                'total_cases'  => (int) $client->total_cases,
                'rate'         => $client->rate,
                'is_active'    => $client->is_active,
                'created_at'   => $client->created_at,
                'updated_at'   => $client->updated_at,
            ]);

        return $this->successResponse($clients);
    }

    public function show($id)
    {
        $client = User::where('type', 'client')->withCount('caseParticipations as total_cases')->findOrFail($id);

        return $this->successResponse($client);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'required|string|max:20',
            'password' => 'required|min:6',
            'bio'      => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['type'] = 'client';
        $validated['is_active'] = false;

        $client = User::create($validated);

        return $this->successResponse($client, 'Client created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $client = User::where('type', 'client')->findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($client->id)],
            'mobile'   => 'sometimes|string|max:20',
            'password' => 'sometimes|min:6',
            'bio'      => 'nullable|string',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
         if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('clients', 'public');
        }

        $client->update($validated);

        return $this->successResponse($client->fresh(), 'Client updated successfully');
    }

    public function destroy($id)
    {
        $client = User::where('type', 'client')->findOrFail($id);
        $client->delete();

        return $this->successResponse(null, 'Client deleted successfully');
    }

    public function toggleStatus($id)
    {
        $client = User::where('type', 'client')->findOrFail($id);

        $client->is_active = !$client->is_active;
        $client->save();

        return $this->successResponse($client, 'Status updated');
    }

    public function overview()
    {
        $total  = User::where('type', 'client')->count();
        $active = User::where('type', 'client')->where('is_active', true)->count();
        $inactive = User::where('type', 'client')->where('is_active', false)->get();
        $pending = $inactive->whereNull('status')->count() + $inactive->where('status', 'pending')->count();
        $suspended = $inactive->where('status', 'suspended')->count();

        return $this->successResponse([
            'totalClients'        => $total,
            'activeClients'       => $active,
            'pendingVerification' => $pending,
            'suspendedAccounts'   => $suspended,
        ]);
    }

    public function clientOverview($id)
    {
        $client = User::where('type', 'client')->findOrFail($id);

        $cases = $client->caseParticipations()->with('case')->get()->pluck('case');

        return $this->successResponse([
            'totalCases'   => $cases->count(),
            'pendingCases' => $cases->where('status', CaseModel::STATUS_PENDING)->count(),
            'activeCases'  => $cases->where('status', CaseModel::STATUS_ACTIVE)->count(),
            'closedCases'  => $cases->where('status', CaseModel::STATUS_CLOSED)->count(),
        ]);
    }

    public function getClientCases($id)
    {
        $client = User::where('type', 'client')->findOrFail($id);

        $caseIds = $client->caseParticipations()->pluck('case_id');

        $cases = CaseModel::with(['client', 'lawyers:id,name,image', 'creator:id,name'])
            ->whereIn('id', $caseIds)
            ->latest()
            ->get();

        return $this->successResponse($cases);
    }

    
}
