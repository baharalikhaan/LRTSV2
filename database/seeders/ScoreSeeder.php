<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScoreSeeder extends Seeder
{
    public function run(): void
    {
        $scores = [
            ['id' => 1,  'name' => 'journal_q1',       'label' => 'Q1',    'category' => 'publication', 'value' => 8.00, 'description' => 'Journal articles (Web of Science — Q1)',          'sort_order' => 1,  'is_active' => 1],
            ['id' => 2,  'name' => 'journal_q2',       'label' => 'Q2',    'category' => 'publication', 'value' => 6.00, 'description' => 'Journal articles (Web of Science — Q2)',          'sort_order' => 2,  'is_active' => 1],
            ['id' => 3,  'name' => 'journal_q3',       'label' => 'Q3',    'category' => 'publication', 'value' => 4.00, 'description' => 'Journal articles (Web of Science — Q3)',          'sort_order' => 3,  'is_active' => 1],
            ['id' => 4,  'name' => 'journal_q4',       'label' => 'Q4',    'category' => 'publication', 'value' => 3.00, 'description' => 'Journal articles (Web of Science — Q4)',          'sort_order' => 4,  'is_active' => 1],
            ['id' => 5,  'name' => 'conference',       'label' => 'Conf',  'category' => 'publication', 'value' => 2.00, 'description' => 'Indexed international conferences',              'sort_order' => 5,  'is_active' => 1],
            ['id' => 6,  'name' => 'book',             'label' => 'Book',  'category' => 'publication', 'value' => 8.00, 'description' => 'Published Books',                                'sort_order' => 6,  'is_active' => 1],
            ['id' => 7,  'name' => 'edited_book',      'label' => 'EdBook','category' => 'publication', 'value' => 6.00, 'description' => 'Edited Books (collection)',                      'sort_order' => 7,  'is_active' => 1],
            ['id' => 8,  'name' => 'book_chapter',     'label' => 'Chap',  'category' => 'publication', 'value' => 4.00, 'description' => 'Book Chapters',                                 'sort_order' => 8,  'is_active' => 1],
            ['id' => 9,  'name' => 'ip_disclosure',    'label' => 'IP',    'category' => 'ip',          'value' => 4.00, 'description' => 'Intellectual Property Disclosure',               'sort_order' => 10, 'is_active' => 1],
            ['id' => 10, 'name' => 'provisional_patent','label' => 'FP',   'category' => 'ip',          'value' => 7.00, 'description' => 'Provisional Patent Filed',                      'sort_order' => 11, 'is_active' => 1],
            ['id' => 11, 'name' => 'patent_granted',   'label' => 'GP',    'category' => 'ip',          'value' => 9.00, 'description' => 'Patents Granted',                               'sort_order' => 12, 'is_active' => 1],
            ['id' => 12, 'name' => 'open_source_sw',   'label' => 'SW',    'category' => 'ip',          'value' => 8.00, 'description' => 'Open Source Software',                          'sort_order' => 13, 'is_active' => 1],
            ['id' => 13, 'name' => 'startup',          'label' => 'SUp',   'category' => 'ip',          'value' => 10.00,'description' => 'Start-Up Created',                              'sort_order' => 14, 'is_active' => 1],
            ['id' => 14, 'name' => 'masters',          'label' => 'MSc',   'category' => 'student',     'value' => 2.00, 'description' => 'Masters Student',                               'sort_order' => 20, 'is_active' => 1],
            ['id' => 15, 'name' => 'ug',               'label' => 'UG',    'category' => 'student',     'value' => 1.00, 'description' => 'Undergraduate Student',                         'sort_order' => 21, 'is_active' => 1],
            ['id' => 16, 'name' => 'phd',              'label' => 'PhD',   'category' => 'student',     'value' => 3.00, 'description' => 'PhD Student',                                   'sort_order' => 22, 'is_active' => 1],
            ['id' => 17, 'name' => 'researcher',       'label' => 'Res',   'category' => 'student',     'value' => 2.00, 'description' => 'Researcher',                                    'sort_order' => 23, 'is_active' => 1],
            ['id' => 18, 'name' => 'cross_college',    'label' => 'CC',    'category' => 'contribution','value' => 2.00, 'description' => 'Cross-College Participation',                   'sort_order' => 30, 'is_active' => 1],
            ['id' => 19, 'name' => 'research_awards',  'label' => 'RA',    'category' => 'contribution','value' => 0.00, 'description' => 'Research Awards (bonus)',                       'sort_order' => 31, 'is_active' => 1],
        ];

        foreach ($scores as $score) {
            DB::table('scores')->updateOrInsert(
                ['id' => $score['id']],
                array_merge($score, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
