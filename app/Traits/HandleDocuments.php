<?php

namespace App\Traits;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HandleDocuments
{
    public function uploadDocuments(Request $request, $caseId, $uploadedBy, $sessionId = null)
    {
        if (!$request->hasFile('documents')) {
            return [];
        }

        $documents = [];

        foreach ($request->file('documents') as $index => $file) {

            // store file
            $path = $file->store('documents', 'public');

            // create record
            $doc = Document::create([
                'case_id' => $caseId,
                'uploaded_by' => $uploadedBy,
                'file_path' => $path,
                'title' => $request->titles[$index] ?? null,
                'type' => $request->types[$index] ?? null,
                'case_session_id' => $sessionId, // optional لو ضفتها في الجدول
            ]);

            $documents[] = $doc;
        }

        return $documents;
    }

    public function replaceDocuments(Request $request, $caseId, $uploadedBy, $sessionId = null)
    {
        // delete old
        Document::where('case_id', $caseId)->delete();

        return $this->uploadDocuments($request, $caseId, $uploadedBy, $sessionId);
    }

    public function deleteDocument($documentId)
    {
        $document = Document::findOrFail($documentId);

        // delete file from storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return true;
    }
}