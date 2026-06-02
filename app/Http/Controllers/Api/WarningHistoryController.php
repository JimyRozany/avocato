<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WarningHistory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarningHistoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = WarningHistory::with(['lawyer:id,name,email', 'sender:id,name,email']);

        if ($request->user()->hasRole('avocato')) {
            $query->where('lawyer_id', $request->user()->id);
        }

        return $this->successResponse($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lawyer_id' => 'required|exists:users,id',
            'reason' => 'required|string',
            'date' => 'required|date',
            'status' => 'nullable|string|in:pending,active,resolved',
        ]);

        $validated['sent_by'] = $request->user()->id;
        $validated['status'] ??= 'pending';

        $validated['warning_id'] = $this->generateWarningId();

        $warning = WarningHistory::create($validated);

        return $this->successResponse(
            $warning->load(['lawyer:id,name,email', 'sender:id,name,email']),
            'Warning created successfully',
            201
        );
    }

    public function show(Request $request, $id)
    {
        $warning = WarningHistory::with(['lawyer', 'sender'])->findOrFail($id);


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
            'date' => 'sometimes|date',
            'status' => 'sometimes|string|in:pending,active,resolved',
        ]);

        $warning->update($validated);

        return $this->successResponse(
            $warning->fresh()->load(['lawyer:id,name,email', 'sender:id,name,email']),
            'Warning updated successfully'
        );
    }

    public function destroy($id)
    {
        $warning = WarningHistory::findOrFail($id);
        $warning->delete();

        return $this->successResponse(null, 'Warning deleted successfully');
    }

    private function generateWarningId(): string
    {
        $last = WarningHistory::latest('id')->first();
        $nextNumber = $last ? ((int) substr($last->warning_id, 4)) + 1 : 1;

        return 'WRN-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
