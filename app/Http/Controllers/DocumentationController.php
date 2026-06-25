<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:documentation.view');
    }

    public function index(Request $request)
    {
        $docs = $this->discoverDocs();
        $selectedDoc = $request->string('doc')->toString();

        if ($selectedDoc === '' || ! $docs->contains(fn (array $doc) => $doc['path'] === $selectedDoc)) {
            $selectedDoc = $docs->first()['path'] ?? null;
        }

        $activeDoc = $selectedDoc ? $docs->firstWhere('path', $selectedDoc) : null;
        $content = $activeDoc ? Str::markdown(File::get($activeDoc['full_path'])) : null;

        return view('documentation.index', [
            'docs' => $docs->groupBy('group'),
            'activeDoc' => $activeDoc,
            'content' => $content,
        ]);
    }

    private function discoverDocs(): Collection
    {
        $docsRoot = base_path('docs');

        if (! File::isDirectory($docsRoot)) {
            return collect();
        }

        return collect(File::allFiles($docsRoot))
            ->filter(fn ($file) => $file->getExtension() === 'md')
            ->map(function ($file) use ($docsRoot) {
                $relativePath = str_replace('\\', '/', ltrim(str_replace($docsRoot, '', $file->getPathname()), '\\/'));
                $segments = explode('/', $relativePath);
                $group = count($segments) > 1 ? ucfirst(str_replace('-', ' ', $segments[0])) : 'Umum';
                $rawContent = File::get($file->getPathname());
                $title = $this->extractTitle($rawContent) ?? Str::headline(pathinfo($file->getFilename(), PATHINFO_FILENAME));
                $excerpt = $this->extractExcerpt($rawContent);

                return [
                    'path' => $relativePath,
                    'group' => $group,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'full_path' => $file->getPathname(),
                ];
            })
            ->sortBy(['group', 'title'])
            ->values();
    }

    private function extractTitle(string $content): ?string
    {
        foreach (preg_split("/\r\n|\n|\r/", $content) as $line) {
            $trimmed = trim($line);
            if (Str::startsWith($trimmed, '# ')) {
                return trim(Str::after($trimmed, '# '));
            }
        }

        return null;
    }

    private function extractExcerpt(string $content): ?string
    {
        $lines = collect(preg_split("/\r\n|\n|\r/", $content))
            ->map(fn ($line) => trim($line))
            ->reject(fn ($line) => $line === '' || Str::startsWith($line, '#') || Str::startsWith($line, '- ') || Str::startsWith($line, '```'))
            ->values();

        return $lines->first() ?: null;
    }
}
