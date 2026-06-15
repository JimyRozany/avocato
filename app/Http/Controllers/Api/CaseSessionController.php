<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseSession;
use App\Traits\ApiResponse;
use App\Traits\HandleDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CaseSessionController extends Controller
{

   use HandleDocuments , ApiResponse;
    
   
    public function index()
    {
        $sessions = CaseSession::latest()->get();

        return $this->successResponse($sessions);
    }
   
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'session_date' => 'nullable|date',
            'decision' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'next_session_date' => 'nullable|date|after_or_equal:session_date',

            'documents' => 'nullable|array',
            'documents.*' => 'file|max:2048', // 2MB
        ]);

        DB::beginTransaction();

        try {
             $validated['session_date'] = now();
            $validated['created_by'] = auth()->id();
            $session = CaseSession::create($validated);

            // 👇 استخدام الـ Trait
            $this->uploadDocuments(
                $request,
                $validated['case_id'],
                auth()->id() ?? 1,
                $session->id
            );

            DB::commit();

            return $this->successResponse($session->load('documents', 'creator') ,'Created successfully' ,201 );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse( "",500 , $e->getMessage());
            
        }
    }
    
    public function show($id)
    {
                // return response()->json("dddddddddd" , 200) ;
        $session = CaseSession::with('documents', 'creator')->findOrFail($id);

        return $this->successResponse($session);
    }
   
    public function update(Request $request, $id)
    {
            
        $session = CaseSession::findOrFail($id);
        
        $validated = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'session_date' => 'nullable|date',
            'decision' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'next_session_date' => 'nullable|date|after_or_equal:session_date',

            'documents' => 'nullable|array',
            'documents.*' => 'file|max:2048',
        ]);
    


        DB::beginTransaction();

        try {
            $session->update($validated);

            // optional: replace documents
            if ($request->hasFile('documents')) {
                $this->replaceDocuments(
                    $request,
                    $validated['case_id'],
                    auth()->id() ?? 1,
                    $session->id
                );
            }

            DB::commit();

            return $this->successResponse($session->load('documents', 'creator') ,  'Updated successfully') ; 
            
           
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse( "",500 , $e->getMessage());
          
        }
    }

    public function destroy($id)
    {
        $session = CaseSession::findOrFail($id);

        $session->delete();
            return $this->successResponse("",'Deleted successfully') ; 

    }

    public function getByCase($caseId)
    {
        $sessions = CaseSession::with('documents', 'creator')
            ->where('case_id', $caseId)
            ->latest()
            ->get();

        return $this->successResponse($sessions);
    }
}
