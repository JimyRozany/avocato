<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Legal;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $legals = Legal::latest()->paginate(10);
        return $this->successResponse($legals);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rule_number' => 'required|string|max:255|unique:legals,rule_number',
            'rule_description' => 'nullable|string',
        ]);

        $legal = Legal::create($validated);

        return $this->successResponse($legal, 'Created successfully', 201);
    }

    public function show($id)
    {
        $legal = Legal::findOrFail($id);
        return $this->successResponse($legal);
    }

    public function update(Request $request, $id)
    {
        $legal = Legal::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'rule_number' => 'sometimes|required|string|max:255|unique:legals,rule_number,' . $legal->id,
            'rule_description' => 'nullable|string',
        ]);

        $legal->update($validated);

        return $this->successResponse($legal, 'Updated successfully');
    }

    public function destroy($id)
    {
        $legal = Legal::findOrFail($id);
        $legal->delete();

        return $this->successResponse(null, 'Deleted successfully');
    }
}
