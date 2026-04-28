<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseRequest;
use Illuminate\Http\Request;

use App\Models\CaseModel;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
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
            $data = $request->validated();
            $data['created_by'] = auth()->id();

            $case = CaseModel::create($data);

            $case->parties()->createMany($request->parties);

            return response()->json($case, 201);
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

}
