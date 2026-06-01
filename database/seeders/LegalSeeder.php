<?php

namespace Database\Seeders;

use App\Models\Legal;
use Illuminate\Database\Seeder;

class LegalSeeder extends Seeder
{
    public function run(): void
    {
        Legal::create([
            'name' => 'القانون المدني',
            'rule_number' => 'RULE-001',
            'rule_description' => 'القواعد المنظمة للمعاملات المدنية بين الأفراد',
        ]);

        Legal::create([
            'name' => 'القانون التجاري',
            'rule_number' => 'RULE-002',
            'rule_description' => 'القواعد المنظمة للأعمال التجارية والتجار',
        ]);

        Legal::create([
            'name' => 'قانون العقوبات',
            'rule_number' => 'RULE-003',
            'rule_description' => 'القواعد المنظمة للجرائم والعقوبات',
        ]);

        Legal::create([
            'name' => 'قانون الأسرة',
            'rule_number' => 'RULE-004',
            'rule_description' => 'القواعد المنظمة للأحوال الشخصية والأسرة',
        ]);

        Legal::create([
            'name' => 'قانون الإجراءات الجنائية',
            'rule_number' => 'RULE-005',
            'rule_description' => 'القواعد المنظمة للإجراءات أمام المحاكم الجنائية',
        ]);
    }
}
