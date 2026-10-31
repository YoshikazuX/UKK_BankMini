<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleGuard
{
    /**
     * Handle an incoming request with Multi-Role and Segregation of Duties (SoD) support.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $userRoleString = strtolower($user->role ?? '');
        $userRoles = array_map('trim', explode(',', $userRoleString));

        // 1. Batasan Keamanan Mutlak (Segregation of Duties / SoD Perbankan):
        // Administrator dan Supervisor TIDAK DIIZINKAN melakukan input transaksi setoran / penarikan kas loket!
        if ($request->is('teller/transaksi*')) {
            if (! in_array('teller', $userRoles, true)) {
                $redirectUrl = in_array('admin', $userRoles, true) ? '/admin/dashboard' : '/supervisor/dashboard';

                return redirect($redirectUrl)->with('error', 'Batasan Keamanan SoD: Administrator & Supervisor tidak diizinkan melakukan transaksi setoran/penarikan kas loket!');
            }
        }

        // 2. Administrator memiliki akses ke seluruh modul back-office, manajemen akun, audit & konfigurasi
        if (in_array('admin', $userRoles, true)) {
            return $next($request);
        }

        // 3. Petugas Teller yang memegang uang fisik DILARANG merangkap Accounting / Supervisor
        if (in_array('teller', $userRoles, true) && (in_array('accounting', $userRoles, true) || in_array('supervisor', $userRoles, true))) {
            return redirect('/teller/dashboard')->with('error', 'Pelanggaran Aturan SoD Perbankan: Akun Teller dilarang merangkap wewenang Accounting / Otorisasi Kas!');
        }

        // 4. Normalisasi Role Target
        $normalizedTargets = [];
        foreach ($roles as $r) {
            foreach (explode(',', strtolower($r)) as $subRole) {
                $subRole = trim($subRole);
                $normalizedTargets[] = $subRole;

                // Alias kesetaraan role
                if ($subRole === 'supervisor') {
                    $normalizedTargets[] = 'accounting';
                }
                if ($subRole === 'accounting') {
                    $normalizedTargets[] = 'supervisor';
                }
                if ($subRole === 'customer_service') {
                    $normalizedTargets[] = 'cs';
                }
                if ($subRole === 'cs') {
                    $normalizedTargets[] = 'customer_service';
                }
            }
        }

        // Cek kesesuaian role
        $hasAccess = false;
        foreach ($userRoles as $uRole) {
            $checkList = [$uRole];
            if ($uRole === 'accounting') {
                $checkList[] = 'supervisor';
            }
            if ($uRole === 'supervisor') {
                $checkList[] = 'accounting';
            }
            if ($uRole === 'cs') {
                $checkList[] = 'customer_service';
            }
            if ($uRole === 'customer_service') {
                $checkList[] = 'cs';
            }

            if (array_intersect($checkList, $normalizedTargets)) {
                $hasAccess = true;
                break;
            }
        }

        if (! $hasAccess) {
            // Jika request via API / JSON atau mencoba mutasi data secara langsung, tolak 403 Forbidden
            if ($request->expectsJson() || $request->is('api/*') || $request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses endpoint ini.');
            }

            // Jika role terdaftar mencoba mengakses modul admin/supervisor/teller yang bukan haknya
            if ($request->is('admin/*') && ! in_array('admin', $userRoles, true) && ! (in_array('customer_service', $userRoles, true) && $request->is('admin/data-nasabah*', 'admin/tambah-nasabah*', 'admin/edit-nasabah*', 'admin/import-nasabah*', 'admin/template-nasabah*'))) {
                abort(403, 'Akses Ditolak: Hak akses Administrator diperlukan.');
            }

            if ($request->is('supervisor/*') && ! in_array('supervisor', $userRoles, true) && ! in_array('accounting', $userRoles, true)) {
                abort(403, 'Akses Ditolak: Hak akses Supervisor / Accounting diperlukan.');
            }

            if ($request->is('teller/*') && ! in_array('teller', $userRoles, true)) {
                abort(403, 'Akses Ditolak: Hak akses Teller Loket diperlukan.');
            }

            if ($request->is('laporan/*') && ! array_intersect($userRoles, ['admin', 'supervisor', 'accounting'])) {
                abort(403, 'Akses Ditolak: Hak akses Laporan Keuangan diperlukan.');
            }

            // Arahkan ke dashboard yang sesuai peran
            if (in_array('teller', $userRoles, true)) {
                return redirect('/teller/dashboard')->with('error', 'Akses ditolak: Menu ini di luar wewenang tugas Teller.');
            }

            if (in_array('accounting', $userRoles, true) || in_array('supervisor', $userRoles, true)) {
                return redirect('/supervisor/dashboard')->with('error', 'Akses ditolak: Menu ini di luar wewenang Accounting / Supervisor.');
            }

            if (in_array('customer_service', $userRoles, true) || in_array('cs', $userRoles, true)) {
                return redirect('/admin/data-nasabah')->with('error', 'Akses terbatas untuk Customer Service.');
            }

            if (in_array('avalis', $userRoles, true)) {
                return redirect('/absensi-avalis')->with('error', 'Akses terbatas untuk Petugas Avalis.');
            }

            if (in_array('nasabah', $userRoles, true)) {
                return redirect('/nasabah/dashboard')->with('error', 'Akses terbatas untuk Nasabah.');
            }

            abort(403, 'Akses Ilegal! Anda tidak memiliki wewenang untuk membuka halaman tersebut.');
        }

        return $next($request);
    }
}
