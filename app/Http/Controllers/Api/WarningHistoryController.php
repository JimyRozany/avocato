<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WarningHistory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WarningHistoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = WarningHistory::with(['target:id,name,email', 'sender:id,name,email']);

        if ($request->user()->hasRole('avocato')) {
            $query->where('lawyer_id', $request->user()->id);
        }

        return $this->successResponse($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'reason'     => 'required|string',
            'warning_for' => 'nullable|string|in:lawyer,client',
        ]);

        $target = User::findOrFail($validated['user_id']);
        $validated['warning_for'] = $validated['warning_for'] ?? $this->detectWarningFor($target);
        $validated['lawyer_id'] = $validated['user_id'];
        $validated['sent_by'] = $request->user()->id;
        $validated['date'] = now();
        $validated['status'] ??= 'pending';
        $validated['warning_id'] = $this->generateWarningId();

        unset($validated['user_id']);

        $warning = WarningHistory::create($validated);

        return $this->successResponse(
            $warning->load(['target:id,name,email', 'sender:id,name,email']),
            'Warning created successfully',
            201
        );
    }

    public function show(Request $request, $id)
    {
        $warning = WarningHistory::with(['target', 'sender'])->findOrFail($id);

        if ($request->user()->hasRole('avocato') && $warning->lawyer_id !== $request->user()->id) {
            return $this->errorResponse('Access denied', 403);
        }

        return $this->successResponse($warning);
    }

    public function update(Request $request, $id)
    {
        $warning = WarningHistory::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'sometimes|string',
            'date'   => 'sometimes|date',
            'status' => 'sometimes|string|in:pending,active,resolved',
        ]);

        $warning->update($validated);

        return $this->successResponse(
            $warning->fresh()->load(['target:id,name,email', 'sender:id,name,email']),
            'Warning updated successfully'
        );
    }

    public function toggleStatus(Request $request, $id)
    {
        $warning = WarningHistory::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,active,resolved',
        ]);

        $warning->update($validated);

        return $this->successResponse(
            $warning->fresh()->load(['target:id,name,email', 'sender:id,name,email']),
            'Warning status updated successfully'
        );
    }

    public function destroy($id)
    {
        $warning = WarningHistory::findOrFail($id);
        $warning->delete();

        return $this->successResponse(null, 'Warning deleted successfully');
    }

    public function getByLawyer($lawyerId)
    {
        $lawyer = User::role('avocato')->findOrFail($lawyerId);

        $warnings = WarningHistory::with(['target:id,name,email', 'sender:id,name,email'])
            ->where('lawyer_id', $lawyer->id)
            ->latest()
            ->get();

        return $this->successResponse([
            'target'   => $lawyer->only('id', 'name', 'email'),
            'warnings' => $warnings,
        ]);
    }

    public function getByClient($clientId)
    {
        $client = User::where('type', 'client')->findOrFail($clientId);

        $warnings = WarningHistory::with(['target:id,name,email', 'sender:id,name,email'])
            ->where('lawyer_id', $client->id)
            ->where('warning_for', 'client')
            ->latest()
            ->get();

        return $this->successResponse([
            'target'   => $client->only('id', 'name', 'email'),
            'warnings' => $warnings,
        ]);
    }

    private function generateWarningId(): string
    {
        $last = WarningHistory::latest('id')->first();
        $nextNumber = $last ? ((int) substr($last->warning_id, 4)) + 1 : 1;

        return 'WRN-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function detectWarningFor(User $user): string
    {
        if ($user->hasRole('avocato')) {
            return 'lawyer';
        }

        return 'client';
    }
}
