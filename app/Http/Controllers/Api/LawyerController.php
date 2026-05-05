<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LawyerController extends Controller
{
    use ApiResponse ; 
    // 1. Get all lawyers
    public function index()
    {
        $lawyers = User::role('avocato')->get();

        return $this->successResponse($lawyers) ;
     
    }

    // 2. Show single lawyer
    public function show($id)
    {
        $lawyer = User::role('avocato')->findOrFail($id);

        return $this->successResponse($lawyer) ;
    }

    // 3. Create lawyer
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'mobile'   => 'required',
            'password' => 'required|min:6',
        ]);

        $lawyer = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'mobile'     => $request->mobile,
            'password'   => Hash::make($request->password),
            'is_active'  => true,
        ]);

        $lawyer->assignRole('avocato');

         return $this->successResponse($lawyer ,'Lawyer created successfully' ,201) ;

        
    }

    // 4. Update lawyer
    public function update(Request $request, $id)
    {
        $lawyer = User::role('avocato')->findOrFail($id);

        $lawyer->update([
            'name'   => $request->name ?? $lawyer->name,
            'email'  => $request->email ?? $lawyer->email,
            'mobile' => $request->mobile ?? $lawyer->mobile,
        ]);

        if ($request->password) {
            $lawyer->update([
                'password' => Hash::make($request->password)
            ]);
        }
         return $this->successResponse($lawyer ,'Lawyer updated successfully',200) ;

        
    }

    // 5. Delete lawyer
    public function destroy($id)
    {
        $lawyer = User::role('avocato')->findOrFail($id);

        $lawyer->delete();
         return $this->successResponse("" ,'Lawyer deleted successfully',200) ;

    }

    // 6. Activate / Deactivate
    public function toggleStatus($id)
    {
        $lawyer = User::role('avocato')->findOrFail($id);

        $lawyer->is_active = !$lawyer->is_active;
        $lawyer->save();

         return $this->successResponse($lawyer ,'Status updated',200) ;
    }


    // 6. Get Cases by lawyer Id
    public function getLawyerCases(Request $request, $lawyerId)
    {
        $lawyer = User::role('avocato')->findOrFail($lawyerId);

        $cases = $lawyer->casesAsLawyer()
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return response()->json([
            'lawyer' => $lawyer->only(['id', 'name']),
            'cases'  => $cases
        ]);
    }
}