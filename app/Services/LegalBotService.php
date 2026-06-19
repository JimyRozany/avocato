<?php

namespace App\Services;

use App\Models\Legal;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class LegalBotService
{
    private const EMBEDDING_MODEL = 'text-embedding-3-small';
    private const CHAT_MODEL = 'gpt-4o-mini';
    private const SIMILARITY_THRESHOLD = 0.1;
    private const MAX_TOKENS = 500;
    private const TEMPERATURE = 0.3;
    private const TOP_K = 3;

    public function ask(string $question): array
    {
        $embedding = $this->embed($question);

        $topLaws = $this->findSimilarLaws($embedding);

        if (!empty($topLaws)) {
            $context = $this->buildContext($topLaws);
            $answer = $this->generateWithContext($question, $context);

            return [
                'answer' => $answer,
                'matched_laws' => $topLaws,
            ];
        }

        $answer = $this->generateGeneral($question);

        return [
            'answer' => $answer,
            'matched_laws' => [],
        ];
    }

    public function embed(string $text): array
    {
        $response = OpenAI::embeddings()->create([
            'model' => self::EMBEDDING_MODEL,
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding;
    }

    public function findSimilarLaws(array $embedding): array
    {
        $laws = Legal::whereNotNull('embedding')->get();

        if ($laws->isEmpty()) {
            return [];
        }

        $scored = [];
        foreach ($laws as $law) {
            $lawEmbedding = json_decode($law->embedding, true);
            if (!is_array($lawEmbedding)) {
                continue;
            }
            $score = $this->cosineSimilarity($embedding, $lawEmbedding);
            $scored[] = ['law' => $law, 'score' => $score];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        $top = array_slice($scored, 0, self::TOP_K);

        if (empty($top) || $top[0]['score'] < self::SIMILARITY_THRESHOLD) {
            return [];
        }

        return array_map(fn($item) => $item['law'], $top);
    }

    public function buildContext(array $laws): string
    {
        $context = '';
        foreach ($laws as $law) {
            $text = $law->full_text ?? $law->name . "\n" . $law->rule_description;
            $context .= "[{$law->name} ({$law->rule_number})]\n{$text}\n\n";
        }
        return $context;
    }

    public function generateWithContext(string $question, string $context): string
    {
        $response = OpenAI::chat()->create([
            'model' => self::CHAT_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'أنت مساعد قانوني متخصص في القانون المصري فقط. أجب عن سؤال المستخدم بناءً على القوانين المقدمة فقط. إذا لم تجد الإجابة في القوانين المقدمة، فقل "لا توجد معلومات كافية للإجابة على هذا السؤال في القوانين المسجلة." استشهد برقم القانون عند الإجابة. كن دقيقاً ومحايداً. ممنوع تماماً الإجابة عن أي سؤال غير قانوني أو خارج نطاق القانون المصري. إذا كان السؤال غير قانوني، قل "عذراً، أنا متخصص في الاستفسارات القانونية فقط."',
                ],
                [
                    'role' => 'user',
                    'content' => "القوانين:\n{$context}\n\nالسؤال: {$question}",
                ],
            ],
            'max_tokens' => self::MAX_TOKENS,
            'temperature' => self::TEMPERATURE,
        ]);

        return $response->choices[0]->message->content;
    }

    public function generateGeneral(string $question): string
    {
        $response = OpenAI::chat()->create([
            'model' => self::CHAT_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'أنت مساعد قانوني متخصص في القانون المصري فقط. أجب بناءً على معرفتك بالقانون المصري. إذا كنت لا تعرف الإجابة بدقة، قل "عذراً، لا توجد معلومات كافية لدي للإجابة على هذا السؤال." وضح أن هذه المعلومات هي معلومات عامة للاسترشاد بها فقط وقد تحتاج إلى استشارة محامٍ متخصص. ممنوع تماماً الإجابة عن أي سؤال غير قانوني أو خارج نطاق القانون المصري. إذا كان السؤال غير قانوني، قل "عذراً، أنا متخصص في الاستفسارات القانونية فقط." كن دقيقاً ومحايداً.',
                ],
                [
                    'role' => 'user',
                    'content' => $question,
                ],
            ],
            'max_tokens' => self::MAX_TOKENS,
            'temperature' => self::TEMPERATURE,
        ]);

        return $response->choices[0]->message->content;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;

        foreach ($a as $i => $val) {
            $dot += $val * ($b[$i] ?? 0);
            $normA += $val * $val;
            $normB += ($b[$i] ?? 0) * ($b[$i] ?? 0);
        }

        $denom = sqrt($normA) * sqrt($normB);

        return $denom === 0.0 ? 0.0 : $dot / $denom;
    }
}
