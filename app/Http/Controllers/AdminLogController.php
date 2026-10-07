<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Admin-only system log viewer.
 *
 * Reads the tail of storage/logs/*.log, parses Laravel log entries and lets an
 * administrator clear the logs. Access is restricted to the Admin role.
 */
class AdminLogController extends Controller
{
    /** Maximum bytes read from the end of a log file (keeps the page fast). */
    private const TAIL_BYTES = 1048576; // 1 MB

    private const LEVELS = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $levelFilter = strtoupper((string) $request->input('level', ''));
        if (!in_array($levelFilter, self::LEVELS, true)) {
            $levelFilter = '';
        }

        $logPath = storage_path('logs/laravel.log');
        $exists  = File::exists($logPath);
        $size    = $exists ? File::size($logPath) : 0;

        $entries = $exists ? $this->parseLog($logPath, $levelFilter) : [];

        // Newest first.
        $entries = array_reverse($entries);

        return view('admin.logs', [
            'entries'     => $entries,
            'size'        => $size,
            'exists'      => $exists,
            'levelFilter' => $levelFilter,
            'levels'      => self::LEVELS,
        ]);
    }

    public function clear(Request $request)
    {
        $cleared = 0;

        foreach (File::glob(storage_path('logs/*.log')) as $file) {
            File::put($file, '');
            $cleared++;
        }

        return redirect()->route('admin.logs')
            ->with('success', $cleared > 0 ? "Cleared {$cleared} log file(s)." : 'No log files found.');
    }

    /**
     * Read the tail of a log file and parse it into entries.
     *
     * @return array<int, array{time:string,env:string,level:string,message:string,context:string}>
     */
    private function parseLog(string $path, string $levelFilter): array
    {
        $data  = $this->readTail($path, self::TAIL_BYTES);
        $lines = preg_split("/\r\n|\n|\r/", $data) ?: [];

        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[+-]\d{2}:?\d{2})?)\]\s+([\w.-]+)\.([A-Z]+):\s?(.*)$/', $line, $m)) {
                if ($current !== null) {
                    $entries[] = $current;
                }
                $current = [
                    'time'    => $m[1],
                    'env'     => $m[2],
                    'level'   => strtoupper($m[3]),
                    'message' => $m[4],
                    'context' => '',
                ];
            } elseif ($current !== null) {
                // Continuation of the current entry (stack trace / context).
                $current['context'] .= ($current['context'] === '' ? '' : "\n") . $line;
            } else {
                // Orphan line before the first recognised header.
                $current = [
                    'time'    => '',
                    'env'     => '',
                    'level'   => 'INFO',
                    'message' => $line,
                    'context' => '',
                ];
            }
        }

        if ($current !== null) {
            $entries[] = $current;
        }

        if ($levelFilter !== '') {
            $entries = array_values(array_filter($entries, fn ($e) => $e['level'] === $levelFilter));
        }

        return $entries;
    }

    /** Read at most $maxBytes from the end of a file. */
    private function readTail(string $path, int $maxBytes): string
    {
        $size = filesize($path);
        $fh = fopen($path, 'r');
        if ($fh === false) {
            return '';
        }

        if ($size > $maxBytes) {
            fseek($fh, $size - $maxBytes);
            fgets($fh); // discard the partial first line
        }

        $data = stream_get_contents($fh);
        fclose($fh);

        return $data === false ? '' : $data;
    }
}
