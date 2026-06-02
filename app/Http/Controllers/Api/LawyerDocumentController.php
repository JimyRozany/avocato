<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LawyerDocument;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LawyerDocumentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = LawyerDocument::with('lawyer')->latest();

        if ($request->user()->hasRole('avocato')) {
            $query->where('user_id', $request->user()->id);
        }

        return $this->successResponse($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:5120',
        ]);

        $validated['file_path'] = $request->file('file')->store('lawyer-documents', 'public');
        $validated['user_id'] = $request->user()->id;

        unset($validated['file']);

        $document = LawyerDocument::create($validated);

        return $this->successResponse($document->load('lawyer'), 'Document uploaded successfully', 201);
    }

    public function show(Request $request, $id)
    {
        $document = LawyerDocument::with('lawyer')->findOrFail($id);

        if ($request->user()->hasRole('avocato') && $document->user_id !== $request->user()->id) {
            return $this->errorResponse('Access denied', 403);
        }

        return $this->successResponse($document);
    }

    public function update(Request $request, $id)
    {
        $document = LawyerDocument::with('lawyer')->findOrFail($id);

        if ($request->user()->hasRole('avocato') && $document->user_id !== $request->user()->id) {
            return $this->errorResponse('Access denied', 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('file')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('lawyer-documents', 'public');
        }

        unset($validated['file']);

        $document->update($validated);

        return $this->successResponse($document, 'Document updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $document = LawyerDocument::findOrFail($id);

        if ($request->user()->hasRole('avocato') && $document->user_id !== $request->user()->id) {
            return $this->errorResponse('Access denied', 403);
        }

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return $this->successResponse(null, 'Document deleted successfully');
    }
}
