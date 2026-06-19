<?php

namespace App\Console\Commands;

use App\Models\Legal;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class BackfillLegalEmbeddings extends Command
{
    protected $signature = 'legal:backfill-embeddings';
    protected $description = 'Generate embeddings for all legal records that are missing them';

    public function handle(): void
    {
        $legals = Legal::whereNull('embedding')->get();

        if ($legals->isEmpty()) {
            $this->info('No records need backfilling.');
            return;
        }

        $bar = $this->output->createProgressBar($legals->count());
        $bar->start();

        foreach ($legals as $legal) {
            $text = $legal->full_text ?? $legal->name . ' ' . $legal->rule_description;
            $text = trim($text);

            if (empty($text)) {
                $bar->advance();
                continue;
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
                $this->warn("Failed for legal ID {$legal->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Backfill complete!');
    }
}
