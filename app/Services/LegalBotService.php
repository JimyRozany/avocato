<?php

namespace App\Services;

use App\Models\Legal;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class LegalBotService
{
    private const EMBEDDING_MODEL = 'text-embedding-3-small';
    private const CHAT_MODEL = 'gpt-4o-mini';
    private const SIMILARITY_THRESHOLD = 0.45;
    private const MAX_TOKENS = 500;
    private const TEMPERATURE = 0.3;
    private const TOP_K = 3;

    private array $stopWords = [
        'ما', 'هو', 'هي', 'هل', 'كيف', 'لماذا', 'أين', 'متى', 'من', 'إلى',
        'عن', 'على', 'في', 'مع', 'بين', 'كان', 'كانت', 'ليس', 'لا', 'لم',
        'لن', 'إن', 'أن', 'قد', 'سوف', 'هذا', 'هذه', 'ذلك', 'تلك', 'هؤلاء',
        'أو', 'و', 'ف', 'ب', 'ل', 'ثم', 'لكن', 'لقد', 'لأن', 'حتى', 'عند',
        'عندما', 'إذا', 'كل', 'بعض', 'أي', 'اي', 'الذي', 'التي', 'الذين',
        'به', 'بها', 'له', 'لها', 'لهم', 'عليه', 'عليها', 'منها', 'منه',
        'فيه', 'فيها', 'الا', 'ألا', 'اذا', 'بين', 'دون', 'غير', 'حول',
        'بعد', 'قبل', 'فوق', 'تحت', 'داخل', 'خارج', 'أمام', 'خلف',
    ];

    public function ask(string $question): array
    {
        $laws = $this->searchLaws($question);

        if (!empty($laws)) {
            $context = $this->buildContext($laws);
            $answer = $this->generateWithContext($question, $context);

            if (!$this->isInsufficientInfo($answer)) {
                return [
                    'answer' => $answer,
                    'matched_laws' => $laws,
                ];
            }
        }

        return [
            'answer' => $this->generateGeneral($question),
            'matched_laws' => [],
        ];
    }

    private function searchLaws(string $question): array
    {
        // 1. Semantic search via embedding similarity
        $laws = $this->searchSemantic($question);
        if (!empty($laws)) {
            return $laws;
        }

        // 2. FULLTEXT search on name + rule_description + full_text
        $keywords = $this->tokenize($question);
        if (!empty($keywords)) {
            $laws = $this->searchFulltext($keywords);
            if ($laws->isNotEmpty()) {
                return $laws->all();
            }

            // 3. LIKE search as last resort
            $laws = $this->searchLike($keywords);
            if ($laws->isNotEmpty()) {
                return $laws->all();
            }
        }

        return [];
    }

    private function searchSemantic(string $question): array
    {
        try {
            $embedding = $this->embed($question);
        } catch (\Exception $e) {
            Log::warning('Semantic search unavailable, skipping: ' . $e->getMessage());
            return [];
        }

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

    private function embed(string $text): array
    {
        $response = OpenAI::embeddings()->create([
            'model' => self::EMBEDDING_MODEL,
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding;
    }

    private function tokenize(string $text): array
    {
        $normalized = str_replace(
            ['أ', 'إ', 'آ', 'ة', 'ى', 'ؤ', 'ئ'],
            ['ا', 'ا', 'ا', 'ه', 'ي', 'و', 'ي'],
            $text
        );

        $words = preg_split('/[\s,،.؟?!\-\n\r]+/u', $normalized);

        return array_values(array_unique(array_filter(array_map('trim', $words), function ($word) {
            return mb_strlen($word) > 2 && !in_array($word, $this->stopWords);
        })));
    }

    private function searchFulltext(array $keywords)
    {
        $query = implode(' ', array_map(fn($w) => "+{$w}*", $keywords));

        return Legal::whereRaw(
            "MATCH(name, rule_description, full_text) AGAINST(? IN BOOLEAN MODE)",
            [$query]
        )->get();
    }

    private function searchLike(array $keywords)
    {
        $query = Legal::query();
        foreach ($keywords as $keyword) {
            $query->orWhere('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('rule_description', 'LIKE', "%{$keyword}%")
                  ->orWhere('full_text', 'LIKE', "%{$keyword}%");
        }
        return $query->get();
    }

    private function buildContext(array $laws): string
    {
        $context = '';
        foreach ($laws as $law) {
            $text = $law->full_text ?? $law->rule_description ?? $law->name;
            $context .= "[{$law->name} (مادة {$law->rule_number})]\n{$text}\n\n";
        }
        return $context;
    }

    private function generateWithContext(string $question, string $context): string
    {
        $response = OpenAI::chat()->create([
            'model' => self::CHAT_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'أنت مساعد قانوني متخصص في القانون المصري فقط. أجب عن سؤال المستخدم بناءً على القوانين المقدمة فقط. إذا لم تجد الإجابة في القوانين المقدمة، فقل "لا توجد معلومات كافية للإجابة على هذا السؤال في القوانين المسجلة." استشهد برقم المادة عند الإجابة. كن دقيقاً ومحايداً. ممنوع تماماً الإجابة عن أي سؤال غير قانوني.',
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

    private function generateGeneral(string $question): string
    {
        $response = OpenAI::chat()->create([
            'model' => self::CHAT_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'أنت مساعد قانوني متخصص في القانون المصري فقط. أجب بناءً على معرفتك بالقانون المصري. وضح أن هذه معلومات عامة للاسترشاد وقد تحتاج لاستشارة محامٍ متخصص. إذا كان السؤال غير قانوني، قل "عذراً، أنا متخصص في الاستفسارات القانونية فقط."',
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

    private function isInsufficientInfo(string $answer): bool
    {
        $indicators = [
            'لا توجد معلومات كافية',
            'غير موجود',
            'لا تتوفر معلومات',
            'ليست لدي معلومات',
            'لا يمكنني الإجابة',
        ];

        foreach ($indicators as $indicator) {
            if (str_contains($answer, $indicator)) {
                return true;
            }
        }

        return false;
    }
}
