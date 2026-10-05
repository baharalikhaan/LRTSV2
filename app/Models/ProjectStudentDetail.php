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

        $url = config('services.student_api.url', 'http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std');
        $secKey = config('services.student_api.sec_key', 'STD@R');

        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 10,
            ]);

            $response = $client->request('GET', $url, [
                'headers' => [
                    'sec_key' => $secKey,
                    'st_id' => $studentId,
                ],
            ]);

            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            // The API replied but the payload was not usable JSON.
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                \Log::warning('Student API returned an invalid (non-JSON) response', [
                    'student_id'  => $studentId,
                    'url'         => $url,
                    'http_status' => $status,
                    'json_error'  => json_last_error_msg(),
                    'body'        => \Illuminate\Support\Str::limit($body, 1000),
                ]);
                return null;
            }

            $parsed = self::parseApiResponse($data, $studentId);

            // The API replied 200 but had no student record for this id.
            if (!$parsed) {
                \Log::warning('Student API returned no student record', [
                    'student_id'  => $studentId,
                    'url'         => $url,
                    'http_status' => $status,
                    'body'        => \Illuminate\Support\Str::limit($body, 1000),
                ]);
            }

            return $parsed;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // HTTP-level failure (4xx/5xx) — the response object is available.
            $resp = $e->getResponse();
            \Log::warning('Student API request failed (HTTP error)', [
                'student_id'  => $studentId,
                'url'         => $url,
                'http_status' => $resp ? $resp->getStatusCode() : null,
                'reason'      => $resp ? $resp->getReasonPhrase() : null,
                'body'        => $resp ? \Illuminate\Support\Str::limit((string) $resp->getBody(), 1000) : null,
                'error'       => $e->getMessage(),
            ]);
            return null;
        } catch (\Throwable $e) {
            // Connection / DNS / timeout / TLS or any other failure.
            \Log::warning('Student API request failed', [
                'student_id' => $studentId,
                'url'        => $url,
                'exception'  => get_class($e),
                'error'      => $e->getMessage(),
            ]);
            return null;
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

        // Backfill the canonical student_name on the parent row from the
        // API data (the add-student flow only knows level/QU-id/days — name
        // arrives from SIS).
        $name = trim(($apiData['first_name'] ?? '') . ' ' . ($apiData['last_name'] ?? ''));
        if ($name !== '') {
            \App\Models\ProjectStudent::where('id', $projectStudentId)->whereNull('student_name')
                ->update(['student_name' => $name]);
        }

        return self::updateOrCreate(
            ['project_student_id' => $projectStudentId],
            array_merge($apiData, ['project_student_id' => $projectStudentId])
        );
    }
}
