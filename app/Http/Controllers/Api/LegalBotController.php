<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LegalBotService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LegalBotController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LegalBotService $legalBotService
    ) {
    }

    public function ask(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        try {
            $result = $this->legalBotService->ask($validated['question']);

            return $this->successResponse([
                'question' => $validated['question'],
                ...$result,
            ]);
        } catch (\Exception $e) {
            Log::error('LegalBot failed: ' . $e->getMessage());

            return $this->successResponse([
                'question' => $validated['question'],
                'answer' => 'عذراً، تعذر الاتصال بخدمة الذكاء الاصطناعي. يرجى المحاولة لاحقاً.',
                'matched_laws' => [],
            ]);
        }
    }
}
