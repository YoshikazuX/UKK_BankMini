<x-docs.heading id="alur-transaksi-loket" level="2">Alur Pelayanan &amp; Transaksi Loket Teller</x-docs.heading>
<p>
    Berikut alur end-to-end pemrosesan transaksi tabungan di loket Bank Mini Sekolah dari kedatangan siswa hingga penerbitan slip cetak dan pembukuan jurnal:
</p>

<div class="my-6 p-4 rounded-2xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
    <svg viewBox="0 0 840 165" class="w-full min-w-[660px] h-auto" role="img" aria-label="Diagram Alur Transaksi Loket Teller">
        <defs>
            <marker id="wf-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto">
                <path d="M 0 1 L 10 5 L 0 9 z" fill="#0F766E" />
            </marker>
        </defs>
        <rect x="10" y="35" width="140" height="90" rx="10" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <text x="80" y="63" text-anchor="middle" fill="#0F172A" font-size="11" font-weight="800">1. Identifikasi Siswa</text>
        <text x="80" y="83" text-anchor="middle" fill="#475569" font-size="9.5">Scan QR / Cari NIS</text>
        <text x="80" y="99" text-anchor="middle" fill="#475569" font-size="9.5">atau No Rekening</text>

        <rect x="180" y="35" width="145" height="90" rx="10" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <text x="252" y="63" text-anchor="middle" fill="#0F172A" font-size="11" font-weight="800">2. Validasi Kunjungan</text>
        <text x="252" y="83" text-anchor="middle" fill="#475569" font-size="9.5">Cek status = 'aktif'</text>
        <text x="252" y="99" text-anchor="middle" fill="#475569" font-size="9.5">&amp; Verifikasi Kunjungan</text>

        <rect x="355" y="35" width="150" height="90" rx="10" fill="#ECFDF5" stroke="#10B981" stroke-width="1.6" />
        <text x="430" y="63" text-anchor="middle" fill="#065F46" font-size="11" font-weight="800">3. Otorisasi &amp; Key</text>
        <text x="430" y="83" text-anchor="middle" fill="#065F46" font-size="9.5">Cek Idempotency Key,</text>
        <text x="430" y="99" text-anchor="middle" fill="#065F46" font-size="9.5">PIN (Tarik), Saldo Min</text>
        <text x="430" y="114" text-anchor="middle" fill="#065F46" font-size="9.5">&amp; Kas Fisik Brankas</text>

        <rect x="535" y="35" width="145" height="90" rx="10" fill="#0F172A" />
        <text x="607" y="63" text-anchor="middle" fill="#10B981" font-size="11" font-weight="800">4. DB Transaction</text>
        <text x="607" y="83" text-anchor="middle" fill="#E2E8F0" font-size="9.5">Lock Nasabah &amp; Brankas</text>
        <text x="607" y="99" text-anchor="middle" fill="#E2E8F0" font-size="9.5">Catat Transaksi + Jurnal</text>
        <text x="607" y="114" text-anchor="middle" fill="#E2E8F0" font-size="9.5">AuditLog + Notifikasi</text>

        <rect x="710" y="35" width="120" height="90" rx="10" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <text x="770" y="63" text-anchor="middle" fill="#0F172A" font-size="11" font-weight="800">5. Slip &amp; Buku</text>
        <text x="770" y="83" text-anchor="middle" fill="#475569" font-size="9.5">Cetak Slip 101/201</text>
        <text x="770" y="99" text-anchor="middle" fill="#475569" font-size="9.5">Update Mobile App</text>

        <line x1="150" y1="80" x2="178" y2="80" stroke="#0F766E" stroke-width="2" marker-end="url(#wf-arrow)" />
        <line x1="325" y1="80" x2="353" y2="80" stroke="#0F766E" stroke-width="2" marker-end="url(#wf-arrow)" />
        <line x1="505" y1="80" x2="533" y2="80" stroke="#0F766E" stroke-width="2" marker-end="url(#wf-arrow)" />
        <line x1="680" y1="80" x2="708" y2="80" stroke="#0F766E" stroke-width="2" marker-end="url(#wf-arrow)" />
    </svg>
</div>

<x-docs.heading id="alur-siklus-kas-harian" level="2">Siklus Droping Kas, Tutup Kas &amp; Rekonsiliasi</x-docs.heading>
<x-docs.steps>
    <x-docs.step number="1" title="Pengajuan Droping Kas Awal oleh Teller">
        <p>Apabila uang tunai di loket kurang untuk melayani penarikan siswa, Teller mengajukan tiket kas melalui <code>PengajuanKasController::store</code>. Model membangkitkan nomor referensi unik <code>PK-YYYYMMDD-XXX</code> berstatus <code>pending</code>, lalu <code>NotificationHelper</code> mengirim notifikasi database ke seluruh Supervisor/Accounting.</p>
    </x-docs.step>
    <x-docs.step number="2" title="Otorisasi Ber-Locking oleh Supervisor">
        <p>Supervisor meninjau tiket di Pusat Persetujuan. Aksi dilakukan dalam <code>DB::transaction</code> menggunakan <code>lockForUpdate()</code>. Jika <strong>Disetujui</strong>, <code>brankas.masuk</code> dan <code>brankas.total</code> bertambah, entri <code>jurnal</code> debit kas terbentuk, serta catatan direkam ke <code>approval_histories</code>. Supervisor juga dapat memilih <strong>Hold</strong> (menunggu klarifikasi, catatan wajib diisi) atau <strong>Tolak</strong> (alasan wajib diisi).</p>
    </x-docs.step>
    <x-docs.step number="3" title="Pelaporan Tutup Kas Harian (Daily Report)">
        <p>Di akhir layanan, Teller mengirimkan laporan tutup kas lewat <code>DailyReportController::store</code>. Sistem menghitung kas sistem = <code>opening_balance + total_deposit + total_cash_drop - total_withdrawal</code> (hanya menjumlahkan transaksi berstatus <code>sukses</code> dan kode sandi sah <code>101/201/102/202</code>), lalu dibandingkan terhadap uang fisik hasil hitung Teller.</p>
    </x-docs.step>
    <x-docs.step number="4" title="Verifikasi Supervisor atau Otomasi Pukul 17:00">
        <p>Supervisor menyetujui atau meminta klarifikasi tutup kas. Jika sampai pukul 17:00 masih ada laporan yang belum diproses namun seimbang (<code>difference === 0</code>) dan tidak ada tiket kas menggantung, scheduler <code>kas:auto-close</code> melakukan persetujuan otomatis.</p>
    </x-docs.step>
</x-docs.steps>

<x-docs.heading id="alur-reset-pin-dan-otp" level="2">Alur Reset PIN Nasabah &amp; OTP Petugas</x-docs.heading>
<ul>
    <li><strong>Reset PIN Keamanan Nasabah (<code>PinResetController</code> &amp; <code>AuthNasabahController</code>):</strong>
        Siswa/nasabah mengajukan lupa PIN &rarr; entri <code>pin_reset_requests</code> berstatus <code>pending</code> &rarr; Admin menyetujui di menu Permohonan Reset &amp; menerbitkan <code>reset_token</code> 64 karakter &rarr; Siswa memasukkan PIN 6 digit baru secara mandiri menggunakan <code>reset_token</code> tersebut tanpa pernah membuat Admin mengetahui PIN akhir siswa.</li>
    <li><strong>Lupa Password Petugas berbasis OTP (<code>AuthController</code>):</strong>
        Petugas mengajukan reset melalui <code>/forgot-password/request-otp</code> (metode WhatsApp, Email, atau Tiket Admin) &rarr; server menghasilkan kode OTP acak, menyimpannya ke kolom <code>otp_code</code> (<code>varchar(255)</code>) dalam bentuk hash, dan memverifikasi kedaluwarsa di <code>otp_expires_at</code> saat <code>verifyOtp</code> dipanggil.</li>
</ul>

