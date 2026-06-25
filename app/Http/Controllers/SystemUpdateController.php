<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class SystemUpdateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:system_updates.view');
    }

    public function index()
    {
        $entries = $this->parseChangelog(base_path('CHANGELOG.md'));
        $recentCommits = $this->recentCommits();

        return view('system_updates.index', compact('entries', 'recentCommits'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseChangelog(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', File::get($path)) ?: [];
        $entries = [];
        $currentEntry = null;
        $currentSection = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^##\s+(.+)$/', $trimmed, $matches)) {
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }

                $currentEntry = [
                    'title' => $matches[1],
                    'sections' => [],
                ];
                $currentSection = null;
                continue;
            }

            if (! $currentEntry) {
                continue;
            }

            if (preg_match('/^###\s+(.+)$/', $trimmed, $matches)) {
                $currentSection = $matches[1];
                $currentEntry['sections'][$currentSection] = [];
                continue;
            }

            if ($currentSection && str_starts_with($trimmed, '- ')) {
                $currentEntry['sections'][$currentSection][] = substr($trimmed, 2);
            }
        }

        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        return $entries;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function recentCommits(): array
    {
        if (! File::exists(base_path('.git'))) {
            return [];
        }

        try {
            $process = new Process([
                'git',
                'log',
                '--pretty=format:%H|%h|%ad|%s',
                '--date=short',
                '-n',
                '10',
            ], base_path());

            $process->run();

            if (! $process->isSuccessful()) {
                return [];
            }

            return collect(preg_split('/\r\n|\r|\n/', trim($process->getOutput())) ?: [])
                ->filter()
                ->map(function (string $row) {
                    [$hash, $shortHash, $date, $subject] = array_pad(explode('|', $row, 4), 4, '');

                    return [
                        'hash' => $hash,
                        'short_hash' => $shortHash,
                        'date' => $date,
                        'subject' => $subject,
                    ];
                })
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
