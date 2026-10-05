<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatToolService;
use App\Models\AiSetting;

class AiController extends Controller
{
    private ChatToolService $toolService;

    public function __construct(ChatToolService $toolService)
    {
        $this->toolService = $toolService;
    }

    /**
     * Proxy chat requests to Gemini API.
     * Static: prompt only, no DB access
     * Dynamic: tool-calling with live database queries
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:10',
        ]);

        // Admin kill-switch: AI Assistant can be disabled from the settings
        // (System Settings → AI tab: "Enable AI Assistant").
        if (AiSetting::get('assistant_enabled', '1') !== '1') {
            return response()->json(['error' => 'The AI Assistant has been disabled by the administrator.'], 503);
        }

        $apiKey = AiSetting::get('api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'AI service not configured.'], 503);
        }

        $model = AiSetting::get('model', 'gemini-2.5-flash');
        $mode = AiSetting::get('mode', 'static');
        $isDynamic = $mode === 'dynamic';

        $user = $request->user();
        $activeRole = $user->activeRole();
        $allowedRoles = ['admin', 'lpi', 'reviewer'];
        $activeRole = in_array(strtolower($activeRole), $allowedRoles) ? strtolower($activeRole) : 'lpi';

        // Build contents — last 5 history messages only
        $contents = [];
        $history = array_slice($request->input('history', []), -5);
        foreach ($history as $msg) {
            $role = ($msg['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $text = mb_substr($msg['text'] ?? '', 0, 2000);
            if ($text !== '') {
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $request->input('message')]],
        ];

        $payload = [
            'system_instruction' => ['parts' => [['text' => $this->getSystemPrompt($activeRole)]]],
            'contents'           => $contents,
            'generationConfig'   => [
                'temperature'     => 0.7,
                'maxOutputTokens' => 1024,
            ],
        ];

        // Dynamic mode: add tools
        if ($isDynamic) {
            $payload['tools'] = $this->getToolDeclarations();
        }

        // Tool-call loop (only runs in dynamic mode)
        $maxIterations = $isDynamic ? 3 : 1;
        for ($i = 0; $i < $maxIterations; $i++) {
            $data = $this->callGemini($apiKey, $model, $payload);

            if (isset($data->error)) {
                \Log::warning('Gemini API error: ' . $data->error->message);
                return response()->json(['error' => $data->error->message ?? 'API error'], 502);
            }

            $candidate = $data->candidates[0] ?? null;
            if (!$candidate) {
                return response()->json(['error' => 'No response from AI.'], 502);
            }

            $content = $candidate->content ?? null;
            $parts = $content->parts ?? [];

            $functionCalls = [];
            $textParts = [];
            foreach ($parts as $part) {
                if (isset($part->functionCall)) {
                    $functionCalls[] = $part->functionCall;
                }
                if (isset($part->text)) {
                    $textParts[] = $part->text;
                }
            }

            // If no function calls, return the text response
            if (empty($functionCalls)) {
                $reply = implode("\n", $textParts);
                return response()->json(['reply' => $reply ?: 'I could not generate a response.']);
            }

            // On last iteration, execute tools but don't loop — just get results and ask Gemini to format them
            $contents[] = json_decode(json_encode($content));

            $functionResponses = [];
            foreach ($functionCalls as $fc) {
                $result = $this->executeTool($fc->name, (array)($fc->args ?? []), $user, $activeRole);
                $functionResponses[] = [
                    'functionResponse' => [
                        'name'     => $fc->name,
                        'response' => (object)$result,
                    ],
                ];
            }

            $contents[] = ['role' => 'user', 'parts' => $functionResponses];

            // If this was the last iteration, add a text prompt to force a summary response
            if ($i === $maxIterations - 1) {
                $contents[] = [
                    'role'  => 'user',
                    'parts' => [['text' => 'Based on the tool results above, provide a clear and helpful response to the user.']],
                ];
            }

            $payload['contents'] = $contents;
        }

        return response()->json(['reply' => 'I could not complete your request. Please try rephrasing.']);
    }

    /**
     * Execute a tool call against the database.
     */
    private function executeTool(string $name, array $args, $user, string $activeRole): array
    {
        try {
            $userId = $user->id;

            $result = match ($name) {
                'getMyProjects'      => $this->toolService->getMyProjects($userId, $activeRole),
                'getProjectDetails'  => $this->toolService->getProjectDetails((int)($args['project_id'] ?? 0), $userId, $activeRole),
                'getMyGrades'        => $this->toolService->getMyGrades($userId, $activeRole),
                'getResearchCalls'   => $this->toolService->getResearchCalls(),
                'getMySubmissions'   => $this->toolService->getMySubmissions($userId, $activeRole),
                'getSystemStats'     => $this->toolService->getSystemStats($userId, $activeRole),
                default              => ['error' => 'Unknown tool'],
            };

            return ['result' => $result];
        } catch (\Exception $e) {
            \Log::warning('ChatToolService error: ' . $e->getMessage());
            return ['error' => 'Query failed.'];
        }
    }

    /**
     * Gemini tool/function declarations.
     */
    private function getToolDeclarations(): array
    {
        return [[
            'functionDeclarations' => [
                [
                    'name'        => 'getMyProjects',
                    'description' => 'List projects visible to the current user.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => (object) [],
                    ],
                ],
                [
                    'name'        => 'getProjectDetails',
                    'description' => 'Get detailed info for a single project.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'project_id' => [
                                'type'        => 'integer',
                                'description' => 'The project ID',
                            ],
                        ],
                        'required' => ['project_id'],
                    ],
                ],
                [
                    'name'        => 'getMyGrades',
                    'description' => 'Get grading results for the user\'s projects.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => (object) [],
                    ],
                ],
                [
                    'name'        => 'getResearchCalls',
                    'description' => 'List active research calls with deadlines.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => (object) [],
                    ],
                ],
                [
                    'name'        => 'getMySubmissions',
                    'description' => 'Get file submission history.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => (object) [],
                    ],
                ],
                [
                    'name'        => 'getSystemStats',
                    'description' => 'Get aggregated statistics for the user\'s role.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => (object) [],
                    ],
                ],
            ],
        ]];
    }

    /**
     * Call the Gemini API.
     */
    private function callGemini(string $apiKey, string $model, array $payload): object
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return (object)['error' => (object)['message' => 'Connection error']];
        }

        $decoded = json_decode($body);
        if ($httpCode !== 200) {
            return (object)['error' => (object)['message' => $decoded->error->message ?? "API error ({$httpCode})"]];
        }

        return $decoded ?? (object)[];
    }

    /**
     * Get system prompt from DB, with role context prepended.
     */
    private function getSystemPrompt(string $activeRole): string
    {
        $roleLine = match ($activeRole) {
            'admin'   => 'You are helping an Admin (full access).',
            'lpi'     => 'You are helping an LPI (can only see own projects/submissions).',
            'reviewer'=> 'You are helping a Reviewer (can only see assigned projects).',
            default   => '',
        };

        $basePrompt = AiSetting::get('prompt', $this->getDefaultPrompt());

        return "{$roleLine}\n\n{$basePrompt}";
    }

    /**
     * Fallback default prompt if no active prompt in DB.
     */
    private function getDefaultPrompt(): string
    {
        return <<<PROMPT
You are an AI assistant for the Research Tracking System (RTS) at Qatar University — Office of Research & Graduate Studies.

## About RTS
RTS manages research projects: research calls → registration → progress reports → grading. Three roles: Admin, LPI, Reviewer.

## Rules
1. Be helpful, concise, and friendly.
2. If you don't know something, say so rather than making things up.

## Output Formatting
Use GitHub Flavored Markdown (GFM) for all responses:
- **Tables** for grading criteria, status lists, and comparisons.
- **Bold** `**text**` for field names, status values, menu items.
- **Code** `backticks` for technical identifiers, routes, constants.
- **Lists** `- ` for features, `1. ` for workflows.
- **Headings** `### ` for sections within responses.
- Keep responses structured and scannable.

## Common Tasks
- Submit progress report → Projects → your project → Add Progress Report
- Grade a project → Projects → click Grade on assigned project
- View grades → Dashboard → My Reviews → View Grades
- Assign reviewers → Projects → Assign Reviewers
- Create research call → Research Calls → New Research Call
PROMPT;
    }
}
