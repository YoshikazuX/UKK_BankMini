<?php

namespace Tests\Feature;

use Tests\TestCase;

class DownloadPageTest extends TestCase
{
    /**
     * Test the download page renders with HTTP 200 and expected branding.
     */
    public function test_download_page_renders_successfully(): void
    {
        $response = $this->get('/download');

        $response->assertStatus(200);
        $response->assertSee('LKM Mitra Siswa Abadi');
        $response->assertSee('SMK Negeri 1 Bantul');
        $response->assertSee('Perbankan Sekolah, Kini dalam Genggaman.');
        $response->assertSee('HanBank Mobile');
    }

    /**
     * Test the download page contains navigation and routes.
     */
    public function test_download_page_contains_expected_routes(): void
    {
        $response = $this->get('/download');

        $response->assertStatus(200);
        $response->assertSee(route('landing'));
        $response->assertSee(route('login'));
        $response->assertSee(route('download'));
    }

    /**
     * Test the download page displays real device mockup, install guide and specs.
     */
    public function test_download_page_contains_sections_and_mockup(): void
    {
        $response = $this->get('/download');

        $response->assertStatus(200);
        $response->assertSee('showcase_mobile_siswa.jpg');
        $response->assertSee('Cara Memasang Aplikasi di Android');
        $response->assertSee('Persyaratan Sistem');
        $response->assertSee('Android 7.0 (Nougat) atau versi lebih baru');
    }
}
