<?php

namespace App\Services;

use App\Models\Score;
use App\Models\Outcome;
use App\Models\ProjectContribution;
use App\Models\ProjectStudent;
use Illuminate\Support\Facades\DB;

class GradingCalculator
{
    /**
     * Calculate expected score based on commitments
     */
    public static function calculateExpectedScore(int $projectId): float
    {
        $commitment = DB::table('commitments')->where('project_id', $projectId)->first();
        
        if (!$commitment) {
            return 0;
        }

        $scoreMap = Score::getMap();

        $expected = 0;
        $expected += ($commitment->q1article ?? 0) * ($scoreMap['journal_q1'] ?? 8);
        $expected += ($commitment->q2article ?? 0) * ($scoreMap['journal_q2'] ?? 6);
        $expected += ($commitment->q3article ?? 0) * ($scoreMap['journal_q3'] ?? 4);
        $expected += ($commitment->q4article ?? 0) * ($scoreMap['journal_q4'] ?? 3);
        $expected += ($commitment->confArticle ?? 0) * ($scoreMap['conference'] ?? 2);
        $expected += ($commitment->books ?? 0) * ($scoreMap['book'] ?? 8);
        $expected += ($commitment->editBooks ?? 0) * ($scoreMap['edited_book'] ?? 6);
        $expected += ($commitment->chapters ?? 0) * ($scoreMap['book_chapter'] ?? 4);
        $expected += ($commitment->ip ?? 0) * ($scoreMap['ip_disclosure'] ?? 4);
        $expected += ($commitment->filedPatent ?? 0) * ($scoreMap['provisional_patent'] ?? 7);
        $expected += ($commitment->openSourceSW ?? 0) * ($scoreMap['open_source_sw'] ?? 8);
        $expected += ($commitment->startUp ?? 0) * ($scoreMap['startup'] ?? 10);
        $expected += ($commitment->master ?? 0) * ($scoreMap['masters'] ?? 2);
        $expected += ($commitment->UG ?? 0) * ($scoreMap['ug'] ?? 1);
        $expected += ($commitment->Phd ?? 0) * ($scoreMap['phd'] ?? 3);
        $expected += ($commitment->crossCollege ?? 0) * ($scoreMap['cross_college'] ?? 2);

        return $expected;
    }

    /**
     * Calculate actual verified score for a project
     */
    public static function calculateVerifiedScore(int $projectId): float
    {
        $scoreMap = Score::getMap();
        
        $outcomes = Outcome::where('project_id', $projectId)
            ->where('verifcation_by_reviewer', 'verified')
            ->get();

        $actual = 0;
        foreach ($outcomes as $outcome) {
            $actual += $scoreMap[$outcome->type] ?? $outcome->score ?? 0;
        }

        // Add verified students
        $students = ProjectStudent::where('project_id', $projectId)->get();
        foreach ($students as $student) {
            $actual += $scoreMap[$student->type] ?? 0;
        }

        return $actual;
    }

    /**
     * Calculate Grade A (outcomes verification grade) - Scale 0-5
     */
    public static function calculateGradeA(int $projectId): float
    {
        $expected = self::calculateExpectedScore($projectId);
        $actual = self::calculateVerifiedScore($projectId);

        if ($expected <= 0) {
            return 0;
        }

        $gradeA = ($actual / $expected) * 5;
        return round($gradeA, 2);
    }

    /**
     * Get outcome types and their scores
     */
    public static function getOutcomeTypes(): array
    {
        return Score::getByCategory('publication')->map(function ($score) {
            return [
                'name' => $score->name,
                'label' => $score->label,
                'score' => $score->value,
            ];
        })->toArray();
    }

    /**
     * Get IP types and their scores
     */
    public static function getIpTypes(): array
    {
        return Score::getByCategory('ip')->map(function ($score) {
            return [
                'name' => $score->name,
                'label' => $score->label,
                'score' => $score->value,
            ];
        })->toArray();
    }

    /**
     * Get student types and their scores
     */
    public static function getStudentTypes(): array
    {
        return Score::getByCategory('student')->map(function ($score) {
            return [
                'name' => $score->name,
                'label' => $score->label,
                'score' => $score->value,
            ];
        })->toArray();
    }
}
