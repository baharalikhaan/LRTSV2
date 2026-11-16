<?php

namespace Tests\Feature;

use App\Services\CycleProgressReportService;
use Tests\TestCase;

/**
 * The report upload cards on the progress page (Progress 1, Progress 2,
 * Final and Readiness) link the official Qatar University post-award forms
 * page instead of a local copy of the template.
 */
class ReportTemplateLinksTest extends TestCase
{
    public function test_progress_page_template_links_point_to_the_qu_forms_page()
    {
        $blade = file_get_contents(resource_path('views/projects/add-progress.blade.php'));

        $this->assertStringNotContainsString(
            "route('download.template'",
            $blade,
            'The progress page must no longer link the local template files.'
        );

        $this->assertSame(
            4,
            substr_count($blade, 'CycleProgressReportService::TEMPLATE_URL'),
            'All four report cards (Progress 1, Progress 2, Final, Readiness) must link the QU forms page.'
        );

        $this->assertStringContainsString(
            'Download Template',
            $blade,
            'The link labels should stay recognisable.'
        );

        // The URL itself is the official post-award forms page.
        $this->assertSame(
            'https://www.qu.edu.qa/en-us/Research/offices/research-support/post-awards-forms',
            CycleProgressReportService::TEMPLATE_URL
        );

        // The email reminders use the same source, so both stay in sync.
        $context = (new CycleProgressReportService)->getColumnEmailContext('progress_report');
        $this->assertSame(
            [CycleProgressReportService::TEMPLATE_URL],
            array_values($context['templates']),
            'The report reminder emails must link the same QU forms page.'
        );
    }

    /** No other view should still serve the local template route. */
    public function test_no_view_links_the_local_template_route()
    {
        $offenders = [];

        foreach (glob(resource_path('views/**/*.blade.php')) as $file) {
            if (str_contains(file_get_contents($file), "route('download.template'")) {
                $offenders[] = basename($file);
            }
        }

        $this->assertSame([], $offenders, 'These views still use the local template route: ' . implode(', ', $offenders));
    }
}
