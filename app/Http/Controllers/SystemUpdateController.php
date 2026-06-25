<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SystemUpdateController extends Controller
{
    private const COMMITS_CACHE_KEY = 'system_updates.github_commits';
    private const COMMITS_CACHE_TTL_MINUTES = 360;

    public function __construct()
    {
        $this->middleware('permission:system_updates.view');
    }

    public function index()
    {
        $entries = $this->parseChangelog(base_path('CHANGELOG.md'));
        $recentCommits = $this->recentCommits();
        $lastCommitSyncAt = Cache::get($this->commitCacheTimestampKey());

        return view('system_updates.index', compact('entries', 'recentCommits', 'lastCommitSyncAt'));
    }

    public function refresh()
    {
        $this->recentCommits(forceRefresh: true);

        return redirect()
            ->route('system-updates.index')
            ->with('success', 'Commit terbaru berhasil di-refresh dari GitHub.');
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
    private function recentCommits(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget(self::COMMITS_CACHE_KEY);
            Cache::forget($this->commitCacheTimestampKey());
        }

        return Cache::remember(self::COMMITS_CACHE_KEY, now()->addMinutes(self::COMMITS_CACHE_TTL_MINUTES), function () {
            $repo = (string) data_get(config('services.github'), 'repo', 'jharrvis/crm-bmp');
            $branch = (string) data_get(config('services.github'), 'branch', 'master');
            $token = (string) data_get(config('services.github'), 'token', '');

            if ($repo === '') {
                return [];
            }

            try {
                $request = Http::acceptJson()
                    ->withHeaders([
                        'User-Agent' => config('app.name', 'BMPnet CRM'),
                        'X-GitHub-Api-Version' => '2022-11-28',
                    ]);

                if ($token !== '') {
                    $request = $request->withToken($token);
                }

                $response = $request
                    ->timeout(15)
                    ->get("https://api.github.com/repos/{$repo}/commits", [
                        'sha' => $branch,
                        'per_page' => 10,
                    ])
                    ->throw();

                $commits = collect($response->json())
                    ->map(function (array $commit) {
                        return [
                            'hash' => (string) ($commit['sha'] ?? ''),
                            'short_hash' => substr((string) ($commit['sha'] ?? ''), 0, 7),
                            'date' => (string) data_get($commit, 'commit.author.date', ''),
                            'subject' => (string) strtok((string) data_get($commit, 'commit.message', ''), "\n"),
                            'url' => (string) ($commit['html_url'] ?? ''),
                            'author' => (string) data_get($commit, 'commit.author.name', data_get($commit, 'author.login', 'GitHub')),
                        ];
                    })
                    ->values()
                    ->all();

                Cache::put($this->commitCacheTimestampKey(), now(), now()->addMinutes(self::COMMITS_CACHE_TTL_MINUTES));

                return $commits;
            } catch (RequestException|\Throwable) {
                return [];
            }
        });
    }

    private function commitCacheTimestampKey(): string
    {
        return self::COMMITS_CACHE_KEY . '.synced_at';
    }
}
