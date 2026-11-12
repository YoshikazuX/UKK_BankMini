<?php

namespace Tests\Feature;

use App\Http\Controllers\NasabahController;
use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class NasabahImportExcelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'nama_petugas' => 'Admin Import',
            'username' => 'admin_import',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_import_nasabah_from_xlsx_file(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Format absensi: KELAS di baris pertama, lalu baris data siswa
        $sheet->setCellValue('A1', 'KELAS : X RPL 1');
        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 19001);
        $sheet->setCellValue('C2', 'Siswa Excel Satu');

        $sheet->setCellValue('A3', 2);
        $sheet->setCellValue('B3', 19002);
        $sheet->setCellValue('C3', 'Siswa Excel Dua');

        // Buat temporary XLSX
        $tempPath = tempnam(sys_get_temp_dir(), 'test_xlsx_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'data_siswa.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post('/admin/import-nasabah', [
            'file_csv' => $uploadedFile,
            'status_default' => 'auto',
        ]);

        $response->assertSessionHas('success');

        $siswa1 = Nasabah::where('nis', '19001')->first();
        $siswa2 = Nasabah::where('nis', '19002')->first();

        $this->assertNotNull($siswa1);
        $this->assertEquals('Siswa Excel Satu', $siswa1->nama_siswa);
        $this->assertEquals('X RPL 1', $siswa1->kelas);
        $this->assertEquals('Rekayasa Perangkat Lunak', $siswa1->jurusan);
        $this->assertEquals('nonaktif', $siswa1->status); // Kelas 10 auto nonaktif

        $this->assertNotNull($siswa2);
        $this->assertEquals('Siswa Excel Dua', $siswa2->nama_siswa);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function test_admin_can_import_nasabah_from_native_minimal_xlsx(): void
    {
        // Gunakan reflection untuk menguji generateMinimalXlsx
        $tempPath = tempnam(sys_get_temp_dir(), 'test_min_xlsx_').'.xlsx';
        $rows = [
            ['NIS', 'Nama Lengkap', 'Kelas', 'Jurusan', 'Status'],
            ['19099', 'Siswa Native Satu', 'XI RPL 1', 'Rekayasa Perangkat Lunak', 'aktif'],
        ];

        $ref = new \ReflectionClass(NasabahController::class);
        $method = $ref->getMethod('generateMinimalXlsx');
        $method->setAccessible(true);
        $method->invoke(null, $rows, $tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'data_siswa_native.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post('/admin/import-nasabah', [
            'file_csv' => $uploadedFile,
            'status_default' => 'auto',
        ]);

        $response->assertSessionHas('success');

        $siswa = Nasabah::where('nis', '19099')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals('Siswa Native Satu', $siswa->nama_siswa);
        $this->assertEquals('aktif', $siswa->status);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function test_admin_can_download_xlsx_template(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/template-nasabah?format=xlsx');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_download_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/template-nasabah?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
