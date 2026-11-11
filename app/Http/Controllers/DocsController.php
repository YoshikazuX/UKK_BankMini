<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocsController extends Controller
{
    /**
     * Tampilkan halaman awal Documentation Center (Introduction).
     */
    public function index(): View
    {
        return $this->renderDocument('getting-started', 'introduction');
    }

    /**
     * Tampilkan halaman dokumentasi berdasarkan slug section dan page.
     */
    public function show(string $section, string $page): View
    {
        return $this->renderDocument($section, $page);
    }

    /**
     * Pencarian dokumentasi berbasis JSON untuk mendukung pencarian server-side maupun UI modal.
     */
    public function search(Request $request): JsonResponse
    {
        $query = mb_strtolower(trim((string) $request->query('q', '')));
        $index = $this->buildSearchIndex();

        if ($query === '') {
            return response()->json([
                'success' => true,
                'query' => '',
                'count' => count($index),
                'results' => array_slice($index, 0, 12),
            ]);
        }

        $terms = array_values(array_filter(preg_split('/\s+/', $query) ?: []));
        $scored = [];

        foreach ($index as $entry) {
            $titleHaystack = mb_strtolower($entry['title']);
            $sectionHaystack = mb_strtolower($entry['sectionTitle']);
            $summaryHaystack = mb_strtolower($entry['summary']);
            $keywordHaystack = mb_strtolower(implode(' ', $entry['keywords']));
            $headingHaystack = mb_strtolower(implode(' ', array_column($entry['headings'], 'title')));

            $score = 0;
            $matchedAllTerms = true;

            foreach ($terms as $term) {
                $termScore = 0;

                if (str_contains($titleHaystack, $term)) {
                    $termScore += 12;
                }
                if (str_contains($keywordHaystack, $term)) {
                    $termScore += 8;
                }
                if (str_contains($headingHaystack, $term)) {
                    $termScore += 6;
                }
                if (str_contains($sectionHaystack, $term)) {
                    $termScore += 4;
                }
                if (str_contains($summaryHaystack, $term)) {
                    $termScore += 3;
                }

                if ($termScore === 0) {
                    $matchedAllTerms = false;
                    break;
                }

                $score += $termScore;
            }

            if ($matchedAllTerms && $score > 0) {
                $matchedHeadings = [];
                foreach ($entry['headings'] as $heading) {
                    $headingLower = mb_strtolower($heading['title']);
                    foreach ($terms as $term) {
                        if (str_contains($headingLower, $term)) {
                            $matchedHeadings[] = [
                                'title' => $heading['title'],
                                'url' => $entry['url'].'#'.$heading['id'],
                            ];
                            break;
                        }
                    }
                }

                $scored[] = [
                    'score' => $score,
                    'title' => $entry['title'],
                    'sectionTitle' => $entry['sectionTitle'],
                    'sectionSlug' => $entry['sectionSlug'],
                    'pageSlug' => $entry['pageSlug'],
                    'summary' => $entry['summary'],
                    'badge' => $entry['badge'],
                    'url' => $entry['url'],
                    'matchedHeadings' => array_slice($matchedHeadings, 0, 3),
                ];
            }
        }

        usort($scored, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        $results = array_map(static function (array $item): array {
            unset($item['score']);

            return $item;
        }, array_slice($scored, 0, 15));

        return response()->json([
            'success' => true,
            'query' => $query,
            'count' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * Bangun konteks lengkap halaman dokumentasi dan render layout.
     */
    private function renderDocument(string $sectionSlug, string $pageSlug): View
    {
        $config = config('hanbank_docs');
        $sections = $config['sections'] ?? [];

        if (! isset($sections[$sectionSlug]['pages'][$pageSlug])) {
            abort(404, 'Halaman dokumentasi tidak ditemukan.');
        }

        $sectionConfig = $sections[$sectionSlug];
        $pageConfig = $sectionConfig['pages'][$pageSlug];

        $flatPages = $this->buildFlatNavigation($sections);
        $currentIndex = null;

        foreach ($flatPages as $idx => $item) {
            if ($item['sectionSlug'] === $sectionSlug && $item['pageSlug'] === $pageSlug) {
                $currentIndex = $idx;
                break;
            }
        }

        $prevPage = ($currentIndex !== null && $currentIndex > 0) ? $flatPages[$currentIndex - 1] : null;
        $nextPage = ($currentIndex !== null && isset($flatPages[$currentIndex + 1])) ? $flatPages[$currentIndex + 1] : null;

        $firstPageSlugInSection = (string) array_key_first($sectionConfig['pages']);
        $breadcrumbs = [
            ['label' => 'Documentation', 'url' => route('docs.index')],
            ['label' => $sectionConfig['title'], 'url' => route('docs.show', ['section' => $sectionSlug, 'page' => $firstPageSlugInSection])],
            ['label' => $pageConfig['title'], 'url' => null],
        ];

        return view('layouts.docs', [
            'meta' => $config['meta'] ?? [],
            'navigationSections' => $this->formatNavigationSections($sections),
            'currentSectionSlug' => $sectionSlug,
            'currentPageSlug' => $pageSlug,
            'currentSection' => $sectionConfig,
            'currentPage' => $pageConfig,
            'breadcrumbs' => $breadcrumbs,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
            'tableOfContents' => $pageConfig['headings'] ?? [],
            'searchIndex' => $this->buildSearchIndex(),
            'totalPages' => count($flatPages),
            'currentNumber' => $currentIndex !== null ? $currentIndex + 1 : 1,
        ]);
    }

    /**
     * Format daftar kategori dan halaman untuk sidebar navigasi.
     *
     * @param  array<string, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    private function formatNavigationSections(array $sections): array
    {
        $formatted = [];

        foreach ($sections as $sectionSlug => $section) {
            $pages = [];
            foreach ($section['pages'] as $pageSlug => $page) {
                $pages[] = [
                    'slug' => $pageSlug,
                    'title' => $page['title'],
                    'badge' => $page['badge'] ?? null,
                    'url' => ($sectionSlug === 'getting-started' && $pageSlug === 'introduction')
                        ? route('docs.index')
                        : route('docs.show', ['section' => $sectionSlug, 'page' => $pageSlug]),
                ];
            }

            $formatted[] = [
                'slug' => $sectionSlug,
                'title' => $section['title'],
                'description' => $section['description'] ?? '',
                'pages' => $pages,
            ];
        }

        return $formatted;
    }

    /**
     * Ratakan struktur navigasi menjadi urutan linear untuk tombol Previous & Next.
     *
     * @param  array<string, array<string, mixed>>  $sections
     * @return array<int, array<string, string>>
     */
    private function buildFlatNavigation(array $sections): array
    {
        $flat = [];

        foreach ($sections as $sectionSlug => $section) {
            foreach ($section['pages'] as $pageSlug => $page) {
                $flat[] = [
                    'sectionSlug' => $sectionSlug,
                    'sectionTitle' => $section['title'],
                    'pageSlug' => $pageSlug,
                    'title' => $page['title'],
                    'summary' => $page['summary'],
                    'url' => ($sectionSlug === 'getting-started' && $pageSlug === 'introduction')
                        ? route('docs.index')
                        : route('docs.show', ['section' => $sectionSlug, 'page' => $pageSlug]),
                ];
            }
        }

        return $flat;
    }

    /**
     * Bangun indeks pencarian seluruh halaman dan subjudul dokumentasi.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildSearchIndex(): array
    {
        $sections = config('hanbank_docs.sections', []);
        $index = [];

        foreach ($sections as $sectionSlug => $section) {
            foreach ($section['pages'] as $pageSlug => $page) {
                $index[] = [
                    'sectionSlug' => $sectionSlug,
                    'sectionTitle' => $section['title'],
                    'pageSlug' => $pageSlug,
                    'title' => $page['title'],
                    'summary' => $page['summary'],
                    'badge' => $page['badge'] ?? null,
                    'keywords' => $page['keywords'] ?? [],
                    'headings' => $page['headings'] ?? [],
                    'url' => ($sectionSlug === 'getting-started' && $pageSlug === 'introduction')
                        ? route('docs.index')
                        : route('docs.show', ['section' => $sectionSlug, 'page' => $pageSlug]),
                ];
            }
        }

        return $index;
    }
}

