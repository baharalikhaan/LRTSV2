<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Score;

class ScoreSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing scores
        Score::truncate();

        $scores = [
            // Scholarly Articles - Journals
            [
                'name' => 'journal_q1',
                'label' => 'Q1',
                'value' => 8.00,
                'description' => 'Journal articles (Web of Science — Q1)',
                'category' => 'publication',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'journal_q2',
                'label' => 'Q2',
                'value' => 6.00,
                'description' => 'Journal articles (Web of Science — Q2)',
                'category' => 'publication',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'journal_q3',
                'label' => 'Q3',
                'value' => 4.00,
                'description' => 'Journal articles (Web of Science — Q3)',
                'category' => 'publication',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'journal_q4',
                'label' => 'Q4',
                'value' => 3.00,
                'description' => 'Journal articles (Web of Science — Q4)',
                'category' => 'publication',
                'sort_order' => 4,
                'is_active' => true,
            ],

            // Scholarly Articles - Conferences
            [
                'name' => 'conference',
                'label' => 'Conf',
                'value' => 2.00,
                'description' => 'Indexed international conferences',
                'category' => 'publication',
                'sort_order' => 5,
                'is_active' => true,
            ],

            // Scholarly Articles - Books
            [
                'name' => 'book',
                'label' => 'Book',
                'value' => 8.00,
                'description' => 'Published Books',
                'category' => 'publication',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'edited_book',
                'label' => 'EdBook',
                'value' => 6.00,
                'description' => 'Edited Books (collection)',
                'category' => 'publication',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'book_chapter',
                'label' => 'Chap',
                'value' => 4.00,
                'description' => 'Book Chapters',
                'category' => 'publication',
                'sort_order' => 8,
                'is_active' => true,
            ],

            // Intellectual Property
            [
                'name' => 'ip_disclosure',
                'label' => 'IP',
                'value' => 4.00,
                'description' => 'Intellectual Property Disclosure',
                'category' => 'ip',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'provisional_patent',
                'label' => 'FP',
                'value' => 7.00,
                'description' => 'Provisional Patent Filed',
                'category' => 'ip',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'patent_granted',
                'label' => 'GP',
                'value' => 9.00,
                'description' => 'Patents Granted',
                'category' => 'ip',
                'sort_order' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'open_source_sw',
                'label' => 'SW',
                'value' => 8.00,
                'description' => 'Open Source Software',
                'category' => 'ip',
                'sort_order' => 13,
                'is_active' => true,
            ],
            [
                'name' => 'startup',
                'label' => 'SUp',
                'value' => 10.00,
                'description' => 'Start-Up Created',
                'category' => 'ip',
                'sort_order' => 14,
                'is_active' => true,
            ],

            // Students
            [
                'name' => 'masters',
                'label' => 'MSc',
                'value' => 2.00,
                'description' => 'Masters Student',
                'category' => 'student',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'ug',
                'label' => 'UG',
                'value' => 1.00,
                'description' => 'Undergraduate Student',
                'category' => 'student',
                'sort_order' => 21,
                'is_active' => true,
            ],
            [
                'name' => 'phd',
                'label' => 'PhD',
                'value' => 3.00,
                'description' => 'PhD Student',
                'category' => 'student',
                'sort_order' => 22,
                'is_active' => true,
            ],
            [
                'name' => 'researcher',
                'label' => 'Res',
                'value' => 2.00,
                'description' => 'Researcher',
                'category' => 'student',
                'sort_order' => 23,
                'is_active' => true,
            ],

            // Other Contributions
            [
                'name' => 'cross_college',
                'label' => 'CC',
                'value' => 2.00,
                'description' => 'Cross-College Participation',
                'category' => 'contribution',
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'research_awards',
                'label' => 'RA',
                'value' => 0.00,
                'description' => 'Research Awards (bonus)',
                'category' => 'contribution',
                'sort_order' => 31,
                'is_active' => true,
            ],
        ];

        foreach ($scores as $score) {
            Score::create($score);
        }

        $this->command->info('Scores seeded successfully: ' . count($scores) . ' records created.');
    }
}
