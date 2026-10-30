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
            // Jika mengakses endpoint pengelolaan absensi/avalis atau aksi mutasi, tolak dengan 403 Forbidden
            if ($request->is('absensi-avalis/*') || $request->is('avalis/*') || $request->isMethod('POST') || $request->isMethod('DELETE') || $request->expectsJson()) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses endpoint pengelolaan ini.');
            }

            // Arahkan ke dashboard yang sesuai peran tanpa logout paksa
            if (in_array('teller', $userRoles, true)) {
                return redirect('/teller/dashboard')->with('error', 'Akses ditolak: Menu ini di luar wewenang tugas Teller.');
            }

            if (in_array('accounting', $userRoles, true) || in_array('supervisor', $userRoles, true)) {
                return redirect('/supervisor/dashboard')->with('error', 'Akses ditolak: Menu ini di luar wewenang Accounting / Supervisor.');
            }

            if (in_array('customer_service', $userRoles, true) || in_array('cs', $userRoles, true)) {
                return redirect('/admin/data-nasabah')->with('error', 'Akses terbatas untuk Customer Service.');
            }

            return redirect('/login')->with('error', 'Akses Ilegal! Anda tidak memiliki wewenang untuk membuka halaman tersebut.');
        }

        return $next($request);
    }
}
