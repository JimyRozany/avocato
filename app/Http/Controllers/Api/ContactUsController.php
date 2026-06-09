<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $messages = ContactUs::latest()->get();

        return $this->successResponse($messages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'mobile'    => 'required|string|max:20',
            'message'   => 'required|string',
        ]);

        $contact = ContactUs::create($validated);

        return $this->successResponse($contact, 'Message sent successfully', 201);
    }

    public function show($id)
    {
        $contact = ContactUs::findOrFail($id);

        return $this->successResponse($contact);
    }

    public function destroy($id)
    {
        $contact = ContactUs::findOrFail($id);
        $contact->delete();

        return $this->successResponse(null, 'Message deleted successfully');
    }
}
