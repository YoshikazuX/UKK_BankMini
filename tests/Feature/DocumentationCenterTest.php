<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationCenterTest extends TestCase
{
    public function test_docs_index_renders_introduction_successfully(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertSee('HANBANK');
        $response->assertSee('LKM Mitra Siswa Abadi');
        $response->assertSee('Pengenalan HANBANK');
        $response->assertSee('On This Page');
    }

    public function test_all_configured_documentation_pages_return_200_and_have_valid_toc_anchors(): void
    {
        $sections = config('hanbank_docs.sections', []);
        $this->assertNotEmpty($sections);

        foreach ($sections as $sectionSlug => $section) {
            foreach ($section['pages'] as $pageSlug => $page) {
                $url = ($sectionSlug === 'getting-started' && $pageSlug === 'introduction')
                    ? '/docs'
                    : "/docs/{$sectionSlug}/{$pageSlug}";

                $response = $this->get($url);
                $response->assertStatus(200);
                $response->assertSee($page['title']);

                foreach ($page['headings'] ?? [] as $heading) {
                    $response->assertSee('id="'.$heading['id'].'"', false);
                }
            }
        }
    }

    public function test_invalid_documentation_page_returns_404(): void
    {
        $response = $this->get('/docs/getting-started/halaman-fiktif');

        $response->assertStatus(404);
    }

    public function test_docs_search_endpoint_returns_ranked_json_matches(): void
    {
        $response = $this->getJson('/docs/search?q=RoleGuard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'query' => 'roleguard',
            ])
            ->assertJsonStructure([
                'success',
                'query',
                'count',
                'results' => [
                    '*' => ['title', 'sectionTitle', 'summary', 'url'],
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('count'));
    }

    public function test_previous_and_next_navigation_links_render_accurately(): void
    {
        $response = $this->get('/docs/getting-started/system-overview');

        $response->assertStatus(200);
        $response->assertSee('Previous');
        $response->assertSee('Introduction');
        $response->assertSee('Next');
        $response->assertSee('System Architecture');
    }

    public function test_public_pages_include_link_to_documentation_center(): void
    {
        $landingResponse = $this->get('/');
        $landingResponse->assertStatus(200);
        $landingResponse->assertSee(route('docs.index'));

        $downloadResponse = $this->get('/download');
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertSee(route('docs.index'));
    }
}

