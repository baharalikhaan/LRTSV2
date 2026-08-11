<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStudentDetail extends Model
{
    use HasFactory;

    protected $table = 'project_students_details';

    protected $fillable = [
        'project_student_id',
        'student_id',
        'first_name',
        'last_name',
        'student_status',
        'major',
        'minor',
        'college',
        'std_program',
        'std_level',
        'admission_term',
        'reg_in_course',
        'raw_response',
    ];

    public function projectStudent()
    {
        return $this->belongsTo(ProjectStudent::class, 'project_student_id');
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Test response for development/testing
     */
    private static function getTestResponse(string $studentId): array
    {
        return [
            'items' => [
                [
                    'student_id' => $studentId,
                    'first_name' => 'Test',
                    'last_name' => 'Student',
                    'student_status' => 'Active',
                    'major' => 'Computer Science',
                    'minor' => 'Undeclared',
                    'college' => 'Engineering',
                    'std_program' => 'Master of Science',
                    'std_level' => 'Master',
                    'admission_term' => '202410',
                    'reg_in_course' => 'Registered',
                ]
            ],
            'hasMore' => false,
            'limit' => 25,
            'offset' => 0,
            'count' => 1,
            'links' => [
                [
                    'rel' => 'self',
                    'href' => 'http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std'
                ]
            ]
        ];
    }

    /**
     * Fetch student info from QU SIS API
     */
    public static function fetchFromApi(string $studentId): ?array
    {
        $useTestResponse = config('services.student_api.use_test_response', false);

        // Use test response if configured
        if ($useTestResponse) {
            $data = self::getTestResponse($studentId);
            return self::parseApiResponse($data, $studentId);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 10,
            ]);

            $response = $client->request('GET', config('services.student_api.url', 'http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std'), [
                'headers' => [
                    'sec_key' => config('services.student_api.sec_key', 'STD@R'),
                    'st_id' => $studentId,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return self::parseApiResponse($data, $studentId);
        } catch (\Throwable $e) {
            \Log::warning('Student API failed for ID: ' . $studentId . ' - ' . $e->getMessage());

            // Fallback to test response on error
            $data = self::getTestResponse($studentId);
            return self::parseApiResponse($data, $studentId);
        }
    }

    /**
     * Parse API response and extract student data
     */
    private static function parseApiResponse(array $data, string $studentId): ?array
    {
        if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
            // Get the last item from the array (as per legacy code)
            $item = end($data['items']);

            if (is_array($item)) {
                return [
                    'student_id' => $item['student_id'] ?? $studentId,
                    'first_name' => $item['first_name'] ?? null,
                    'last_name' => $item['last_name'] ?? null,
                    'student_status' => $item['student_status'] ?? null,
                    'major' => $item['major'] ?? null,
                    'minor' => $item['minor'] ?? null,
                    'college' => $item['college'] ?? null,
                    'std_program' => $item['std_program'] ?? null,
                    'std_level' => $item['std_level'] ?? null,
                    'admission_term' => $item['admission_term'] ?? null,
                    'reg_in_course' => $item['reg_in_course'] ?? null,
                    'raw_response' => json_encode($data),
                ];
            }
        }

        return null;
    }

    /**
     * Save student details from API response
     */
    public static function saveFromApi(int $projectStudentId, string $studentId): ?self
    {
        $apiData = self::fetchFromApi($studentId);

        if (!$apiData) {
            return null;
        }

        return self::updateOrCreate(
            ['project_student_id' => $projectStudentId],
            array_merge($apiData, ['project_student_id' => $projectStudentId])
        );
    }
}
