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
            'type'      => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'pending';

        $contact = ContactUs::create($validated);

        return $this->successResponse($contact, 'Message sent successfully', 201);
    }

    public function show($id)
    {
        $contact = ContactUs::findOrFail($id);

        return $this->successResponse($contact);
    }

    public function update(Request $request, $id)
    {
        $contact = ContactUs::findOrFail($id);

        $validated = $request->validate([
            'type'   => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,closed',
        ]);

        $contact->update($validated);

        return $this->successResponse($contact, 'Message updated successfully');
    }

    public function destroy($id)
    {
        $contact = ContactUs::findOrFail($id);
        $contact->delete();

        return $this->successResponse(null, 'Message deleted successfully');
    }

    public function close($id)
    {
        $contact = ContactUs::findOrFail($id);
        $contact->update(['status' => 'closed']);

        return $this->successResponse($contact, 'Message closed successfully');
    }
}
