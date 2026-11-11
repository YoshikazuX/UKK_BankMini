<x-docs.heading id="integrasi-flutter-dan-api" level="2">Uji Integrasi Klien Flutter (Mock HTTP)</x-docs.heading>
<p>
    Pada repositori mobile <code>ukk_bankmini</code>, integrasi antara lapisan jaringan (<code>ApiClient</code> + <code>AuthInterceptor</code> + <code>SessionStorage</code>), service API, parsing JSON model, dan widget antarmuka diuji menggunakan <code>TestApiMockInterceptor</code> yang mensimulasikan payload respons aktual backend Laravel:
</p>
<x-docs.table :headers="['File Test Flutter', 'Lapisan yang Terintegrasi', 'Status Eksekusi Aktual']">
    <tr>
        <td><code>network_test.dart</code></td>
        <td><code>ApiConstants</code> &harr; <code>ApiResponse</code> &harr; <code>SessionStorage</code> &harr; <code>ApiClient</code> interceptor stack</td>
        <td><x-docs.status-badge status="Tested and Verified" /> (6/6 kasus lulus)</td>
    </tr>
    <tr>
        <td><code>nasabah_module_test.dart</code></td>
        <td><code>TransaksiModel</code>, <code>NasabahModel</code>, proteksi transfer nonaktif, verifikasi PIN ke mock Dio</td>
        <td><x-docs.status-badge status="Tested and Verified" /> (5/5 kasus lulus)</td>
    </tr>
    <tr>
        <td><code>fitur_lengkap_nasabah_test.dart</code></td>
        <td><code>SavingGoalModel</code>, <code>BadgeModel</code>, <code>SessionModel</code>, <code>NotifikasiModel</code>, status blokir/nonaktif</td>
        <td><x-docs.status-badge status="Tested and Verified" /> (5/5 kasus lulus)</td>
    </tr>
    <tr>
        <td><code>teller_module_test.dart</code></td>
        <td><code>TellerApiService</code>: cari/detail nasabah, setor <code>101</code>, tarik <code>201</code>, status kas, droping kas, daily report</td>
        <td><x-docs.status-badge status="Tested and Verified" /> (7/7 kasus lulus)</td>
    </tr>
    <tr>
        <td><code>supervisor_admin_test.dart</code></td>
        <td><code>SupervisorApiService</code> + rendering widget <code>SupervisorMainScreen</code> &amp; <code>AdminMainScreen</code></td>
        <td><x-docs.status-badge status="Tested and Verified" /> (10/10 kasus lulus)</td>
    </tr>
    <tr>
        <td><code>widget_test.dart</code></td>
        <td>Model domain fixtures + alur widget <code>SplashScreen</code> &rarr; <code>OnboardingScreen</code> &rarr; <code>LoginScreen</code></td>
        <td><x-docs.status-badge status="Tested and Verified" /> (7/7 kasus lulus)</td>
    </tr>
</x-docs.table>

<x-docs.heading id="integrasi-lintas-modul-backend" level="2">Uji Integrasi Lintas Modul di Laravel</x-docs.heading>
<p>
    Meskipun suite mobile telah menguji integrasi di level kontrak JSON tiruan (mock), pengujian integrasi nyata (*live E2E*) antara aplikasi Android/emulator melawan server Laravel berjalan serta uji integrasi lintas tabel PHPUnit lokal belum dieksekusi pada sesi ini. Secara khusus:
</p>
<ul>
    <li><strong>Database + Journal + Brankas + Notification:</strong> Kode skenario integrasi sudah tertulis di <code>TellerPenyempurnaanTest</code> dan <code>SupervisorPenyempurnaanTest</code> (termasuk pengiriman <code>SupervisorTaskNotification</code> ke tabel <code>notifications</code> dan eksekusi <code>kas:auto-close</code>), berstatus menunggu eksekusi PHPUnit dengan dev dependencies terpasang.</li>
    <li><strong>Live End-to-End Mobile &harr; Backend:</strong> Belum terdapat test suite otomatis tanpa mock antara aplikasi Flutter dan API Laravel lokal.</li>
</ul>

