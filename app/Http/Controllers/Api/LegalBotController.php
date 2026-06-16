<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Legal;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LegalBotController extends Controller
{
    use ApiResponse;

    public function ask(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        $question = $validated['question'];
        $keywords = $this->tokenize($question);

        if (empty($keywords)) {
            return $this->successResponse([
                'question' => $question,
                'answer' => 'عذراً، لم أتمكن من فهم السؤال. يرجى إعادة الصياغة.',
                'matched_laws' => [],
            ]);
        }

        $results = $this->searchByFulltext($keywords);

        if ($results->isEmpty()) {
            $results = $this->searchByLike($keywords);
        }

        $answer = $results->isNotEmpty()
            ? $this->buildAnswer($results)
            : 'عذراً، لم أجد أي معلومات تطابق سؤالك في قاعدة القوانين المسجلة.';

        return $this->successResponse([
            'question' => $question,
            'answer' => $answer,
            'matched_laws' => $results,
        ]);
    }

    private function tokenize(string $text): array
    {
        $stopWords = [
            'ما', 'هو', 'هي', 'هل', 'كيف', 'لماذا', 'أين', 'متى', 'من', 'إلى',
            'عن', 'على', 'في', 'مع', 'بين', 'كان', 'كانت', 'ليس', 'لا', 'لم',
            'لن', 'إن', 'أن', 'قد', 'سوف', 'هذا', 'هذه', 'ذلك', 'تلك', 'هؤلاء',
            'أو', 'و', 'ف', 'ب', 'ل', 'ثم', 'لكن', 'لقد', 'لأن', 'حتى', 'عند',
            'عندما', 'إذا', 'كل', 'بعض', 'أي', 'اي', 'الذي', 'التي', 'الذين',
            'به', 'بها', 'له', 'لها', 'لهم', 'عليه', 'عليها', 'منها', 'منه',
            'فيه', 'فيها', 'الا', 'ألا', 'اذا', 'بين', 'دون', 'غير', 'حول',
            'بعد', 'قبل', 'فوق', 'تحت', 'داخل', 'خارج', 'أمام', 'خلف',
        ];

        $normalized = str_replace(['أ', 'إ', 'آ', 'ة', 'ى', 'ؤ', 'ئ'], ['ا', 'ا', 'ا', 'ه', 'ي', 'و', 'ي'], $text);

        $words = preg_split('/[\s,،.؟?!\-\n\r]+/u', $normalized);

        return array_values(array_unique(array_filter(array_map('trim', $words), function ($word) use ($stopWords) {
            return mb_strlen($word) > 2 && !in_array($word, $stopWords);
        })));
    }

    private function searchByFulltext(array $keywords)
    {
        $fulltextQuery = implode(' ', array_map(fn($w) => "+{$w}*", $keywords));

        return Legal::whereRaw(
            "MATCH(name, rule_description) AGAINST(? IN BOOLEAN MODE)",
            [$fulltextQuery]
        )->get();
    }

    private function searchByLike(array $keywords)
    {
        $query = Legal::query();
        foreach ($keywords as $keyword) {
            $query->orWhere('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('rule_description', 'LIKE', "%{$keyword}%");
        }
        return $query->get();
    }

    private function buildAnswer($results): string
    {
        $answer = "بناءً على القوانين المسجلة في النظام، وجدت المعلومات التالية:\n\n";
        foreach ($results as $i => $legal) {
            $answer .= ($i + 1) . ". {$legal->name} (رقم: {$legal->rule_number})\n";
            $answer .= "   {$legal->rule_description}\n\n";
        }
        return $answer;
    }
}
