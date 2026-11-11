<x-docs.heading id="matriks-uji-validasi" level="2">Matriks Aturan Validasi yang Diuji</x-docs.heading>
<p>
    Skenario validasi bisnis dan integritas masukan yang didefinisikan pada berkas uji Laravel dan Flutter meliputi aturan-aturan kunci berikut:
</p>

<x-docs.table :headers="['Aturan Validasi', 'Implementasi Target', 'Berkas Test yang Memverifikasi']">
    <tr>
        <td>Batas minimal setoran web &amp; saldo mengendap</td>
        <td>Setor web minimal <code>Rp 5.000</code>; tarik diblokir bila saldo akhir &lt; <code>Rp 10.000</code></td>
        <td><code>RevisiLengkapBankMiniTest</code>, <code>TellerPenyempurnaanTest</code>, <code>teller_module_test.dart</code></td>
    </tr>
    <tr>
        <td>Validasi kas fisik loket (<code>brankas</code>)</td>
        <td>Penarikan ditolak apabila nominal melebihi <code>brankas.total</code> hari berjalan</td>
        <td><code>TellerPenyempurnaanTest::test_skenario_3...</code></td>
    </tr>
    <tr>
        <td>Pemblokiran rekening <code>nonaktif</code></td>
        <td>Form transaksi loket dan API menolak mutasi rekening nonaktif / beku darurat</td>
        <td><code>TellerPenyempurnaanTest::test_skenario_1...</code>, <code>fitur_lengkap_nasabah_test.dart</code></td>
    </tr>
    <tr>
        <td>Validasi kode OTP &amp; panjang kolom</td>
        <td>OTP gagal jika salah kode dan penyimpanan hash OTP tidak terpotong di DB (kolom <code>varchar(255)</code>)</td>
        <td><code>ForgotPasswordOtpTest</code> (3 method)</td>
    </tr>
    <tr>
        <td>Wajib catatan selisih &amp; alasan penolakan</td>
        <td>Selisih daily report wajib keterangan; tolak/hold di supervisor wajib alasan/catatan</td>
        <td><code>TellerPenyempurnaanTest::test_skenario_8...</code>, <code>SupervisorPenyempurnaanTest::test_skenario_4...</code></td>
    </tr>
</x-docs.table>

<x-docs.heading id="uji-keamanan-dan-concurrency" level="2">Uji Konkurensi (Locking) &amp; Redaksi Audit</x-docs.heading>
<ul>
    <li><strong>Idempotency Key Transaksi:</strong> <code>TellerPenyempurnaanTest</code> menguji pengiriman dua kali dengan <code>idempotency_key</code> yang sama serta pengecekan status via endpoint JSON saat koneksi terputus agar saldo nasabah tidak terpotong atau bertambah dua kali.</li>
    <li><strong>Pencegahan Double Approval:</strong> <code>SupervisorPenyempurnaanTest::test_skenario_9_pencegahan_double_approval_dengan_locking</code> menguji penguncian baris saat satu tiket pengajuan kas diproses ulang setelah berstatus disetujui/ditolak.</li>
    <li><strong>Penyamaran Kredensial pada Audit &amp; Widget:</strong> <code>BankMiniFiturSempurnaTest</code> memverifikasi bahwa field <code>password</code> tidak tercatat terbuka pada <code>audit_logs</code>, dan <code>DashboardWidgetsTest</code> memastikan widget reset password di dashboard admin tidak mengekspos token/OTP ke HTML.</li>
</ul>

