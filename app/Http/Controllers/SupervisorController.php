<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\AvalisSyncHelper;
use App\Models\Brankas;
use App\Models\Nasabah;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\ProfileImageService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupervisorController extends Controller
{
    public function index(): View
    {
        $totalNasabah = Nasabah::count();
        $totalSaldo = Nasabah::sum('saldo');

        $transaksi = Transaksi::with('nasabah')
            ->orderBy('created_at', 'desc')
            ->get();

        $today = Carbon::today()->toDateString();
        $brankasHariIni = Brankas::where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;
        $pendingPengajuanKasCount = PengajuanKas::where('status', 'pending')->count();
        $absensiSummary = AvalisSyncHelper::getDashboardSummary($today);

        return view('supervisor.dashboard', compact(
            'totalNasabah',
            'totalSaldo',
            'transaksi',
            'kasTersedia',
            'kasAwal',
            'pendingPengajuanKasCount',
            'absensiSummary'
        ));
    }

    public function laporan(): View
    {
        $transaksi = Transaksi::with('nasabah')
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('supervisor.laporan', compact('transaksi'));
    }

    public function approve(int|string $id): RedirectResponse
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->update(['status' => 'sukses']);

        return back()->with('success', 'Transaksi berhasil disetujui oleh Supervisor!');
    }

    public function cetakHarian(): View
    {
        $hariIni = Carbon::today();

        $brankas = Brankas::where('tanggal', $hariIni->toDateString())->first();
        if (! $brankas) {
            $brankas = (object) [
                'saldo_awal' => 0,
                'masuk' => 0,
                'keluar' => 0,
                'total' => 0,
            ];
        }

        $totalAdmin = Transaksi::whereDate('created_at', $hariIni)
            ->where('jenis_transaksi', 'admin')
            ->sum('nominal');

        $transaksiHariIni = Transaksi::with(['nasabah', 'petugas'])
            ->whereDate('created_at', $hariIni)
            ->orderBy('created_at', 'asc')
            ->get();

        $petugasTeller = $transaksiHariIni->pluck('petugas.nama_petugas')->filter()->unique();

        return view('supervisor.cetak-harian', compact('brankas', 'totalAdmin', 'transaksiHariIni', 'petugasTeller'));
    }

    public function profil(): View
    {
        $user = Auth::user();

        return view('supervisor.profil', compact('user'));
    }

    public function updateProfil(Request $request, ProfileImageService $imageService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nama_petugas' => 'required|string|max:150',
            'no_hp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\s\-()]+$/'],
            'email' => 'nullable|email|max:150|unique:users,email,'.$user->id,
            'foto' => 'nullable|file|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:2048',
        ], [
            'nama_petugas.required' => 'Nama lengkap wajib diisi.',
            'nama_petugas.max' => 'Nama lengkap maksimal 150 karakter.',
            'no_hp.regex' => 'Format nomor telepon tidak valid. Gunakan angka, contoh: 081234567890.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'foto.file' => 'File foto yang dipilih tidak valid.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.mimetypes' => 'Format file ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.max' => 'Ukuran file foto maksimal adalah 2 MB.',
        ]);

        $oldValues = [
            'nama_petugas' => $user->nama_petugas,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
        ];

        $user->nama_petugas = $validated['nama_petugas'];
        $user->no_hp = $validated['no_hp'] ?? null;
        $user->email = $validated['email'] ?? null;

        if ($request->hasFile('foto')) {
            $user->foto = $imageService->validateAndStore($request->file('foto'), $user->foto);
        }

        $user->save();
        AvalisSyncHelper::autoSync(true);

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_PROFIL',
            modul: 'Profil',
            objekId: $user->username,
            oldValues: $oldValues,
            newValues: [
                'nama_petugas' => $user->nama_petugas,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
            ]
        );

        return back()->with('success', 'Profil dan foto profil Anda berhasil diperbarui!');
    }
}
