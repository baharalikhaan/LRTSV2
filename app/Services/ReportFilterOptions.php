<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Collection;

/**
 * Cascading filter options shared by the wide project reports
 * (Project Details, Grading Details).
 *
 * The chain is category → cycle → research call → grant: every level only
 * offers the values that actually hold projects of the selected category, so a
 * cycle with no student projects never appears in the student view. A child
 * selection that falls outside its parent's scope is dropped and returned in
 * "filters", letting the caller normalise the request (and the URL).
 */
class ReportFilterOptions
{
    /**
     * @param  array{category?:string, cycle_id?:mixed, program_id?:mixed, grant_id?:mixed}  $filters
     * @return array{cycles: Collection, programs: Collection, grants: Collection, filters: array{cycle_id:mixed, program_id:mixed, grant_id:mixed}}
     */
    public static function forProjects(array $filters = []): array
    {
        $category = $filters['category'] ?? 'all';

        $programs = Project::with(['program.grant', 'program.cycle'])
            ->orderBy('id')
            ->get()
            ->filter(function (Project $project) use ($category) {
                if ($category === 'regular') {
                    return !$project->isStudentProject();
                }

                if ($category === 'student') {
                    return $project->isStudentProject();
                }

                return true;
            })
            ->map(fn (Project $project) => $project->program)
            ->filter();

        $cycles = $programs
            ->map(fn ($program) => $program->cycle)
            ->filter()
            ->unique('id')
            ->sortByDesc('year')
            ->values();

        $cycleId = $filters['cycle_id'] ?? null;
        if ($cycleId && !$cycles->contains('id', (int) $cycleId)) {
            $cycleId = null;
        }

        $cyclePrograms = $programs
            ->when($cycleId, fn ($collection) => $collection->filter(
                fn ($program) => (int) $program->cycle_id === (int) $cycleId
            ))
            ->unique('id')
            ->sortByDesc('id')
            ->values();

        $programId = $filters['program_id'] ?? null;
        if ($programId && !$cyclePrograms->contains('id', (int) $programId)) {
            $programId = null;
        }

        $grants = $cyclePrograms
            ->when($programId, fn ($collection) => $collection->filter(
                fn ($program) => (int) $program->id === (int) $programId
            ))
            ->map(fn ($program) => $program->grant)
            ->filter()
            ->unique('id')
            ->sortBy('grant_code')
            ->values();

        $grantId = $filters['grant_id'] ?? null;
        if ($grantId && !$grants->contains('id', (int) $grantId)) {
            $grantId = null;
        }

        return [
            'cycles'   => $cycles,
            'programs' => $cyclePrograms,
            'grants'   => $grants,
            'filters'  => [
                'cycle_id'   => $cycleId,
                'program_id' => $programId,
                'grant_id'   => $grantId,
            ],
        ];
    }
}
