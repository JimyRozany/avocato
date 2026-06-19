<?php

namespace App\Observers;

use App\Models\Legal;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class LegalObserver
{
    public function saved(Legal $legal): void
    {
        $text = $legal->full_text ?? $legal->name . ' ' . $legal->rule_description;

        $text = trim($text);
        if (empty($text)) {
            return;
        }

        try {
            $response = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);

            $legal->forceFill([
                'embedding' => $response->embeddings[0]->embedding,
            ])->saveQuietly();
        } catch (\Exception $e) {
            Log::error('LegalObserver: Failed to generate embedding for legal ID ' . $legal->id . ': ' . $e->getMessage());
        }
    }
}
