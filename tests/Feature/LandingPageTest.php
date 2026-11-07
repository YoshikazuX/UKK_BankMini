<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    /**
     * Test the landing page returns 200 and renders properly.
     */
    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('LKM Mitra Siswa Abadi');
        $response->assertSee('SMK Negeri 1 Bantul');
        $response->assertSee('Pengelolaan Bank Mini Sekolah dalam Satu Ekosistem Digital');
        $response->assertSee('rel="icon"', false);
        $response->assertSee('SMK_Negeri_1_Bantul_logo.png');
    }

    /**
     * Test the landing page includes expected navigation routes.
     */
    public function test_landing_page_contains_expected_routes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('login'));
        $response->assertSee(route('download'));
    }

    /**
     * Test the landing page contains device showcase images and core sections.
     */
    public function test_landing_page_contains_device_mockups_and_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('showcase_web_teller.png');
        $response->assertSee('showcase_mobile_siswa.jpg');
        $response->assertSee('Satu Ekosistem, Dua Platform Terintegrasi');
        $response->assertSee('Web Operational Dashboard');
        $response->assertSee('HanBank Mobile');
        $response->assertSee('Keamanan dan Akuntabilitas dalam Setiap Transaksi');
        $response->assertSee('Mulai Akses Ekosistem Bank Mini Sekolah');
    }

    /**
     * Test the landing page contains PRD role features and FAQs.
     */
    public function test_landing_page_contains_role_features_and_faqs(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Nasabah / Siswa');
        $response->assertSee('Petugas Teller');
        $response->assertSee('Supervisor');
        $response->assertSee('Admin &amp; Avalis', false);
        $response->assertSee('Apa itu Sistem Bank Mini Sekolah?');
    }
}
