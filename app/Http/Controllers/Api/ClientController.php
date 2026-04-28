<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class ClientController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $clients = User::where('type', 'client')->paginate(10);

        return $this->successResponse($clients);
    }

    public function show($id)
{
    $client = User::where('type', 'client')->find($id);

    if (!$client) {
        return $this->errorResponse('Client not found', 404);
    }

    return $this->successResponse($client);
}
}
