<x-docs.heading id="diagram-erd-visual" level="2">Diagram ERD HANBANK</x-docs.heading>
<p>
    Diagram di bawah memetakan entitas utama beserta relasi foreign key yang benar-benar terdefinisi dalam file migrasi dan kelas model Eloquent pada repositori <code>belajar_laravel_handa</code>.
</p>

<div class="my-6 p-4 sm:p-5 rounded-2xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
    <svg viewBox="0 0 920 610" class="w-full min-w-[740px] h-auto" role="img" aria-label="Entity Relationship Diagram Database HANBANK">
        <defs>
            <marker id="erd-many" viewBox="0 0 12 12" refX="10" refY="6" markerWidth="8" markerHeight="8" orient="auto">
                <path d="M 0 1 L 10 6 L 0 11 M 10 0 L 10 12" stroke="#0F766E" stroke-width="1.5" fill="none" />
            </marker>
        </defs>

        {{-- USERS Entity --}}
        <rect x="20" y="25" width="215" height="145" rx="10" fill="#F8FAFC" stroke="#0F172A" stroke-width="1.6" />
        <rect x="20" y="25" width="215" height="28" rx="8" fill="#0F172A" />
        <text x="32" y="43" fill="#10B981" font-size="11.5" font-weight="800" font-family="JetBrains Mono, monospace">users</text>
        <text x="32" y="70" fill="#0F172A" font-size="10.5" font-family="JetBrains Mono, monospace">PK id</text>
        <text x="32" y="87" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">UQ username (50)</text>
        <text x="32" y="104" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   nama_petugas, email</text>
        <text x="32" y="121" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   role (admin/teller/spv)</text>
        <text x="32" y="138" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   status (aktif/nonaktif)</text>
        <text x="32" y="155" fill="#64748B" font-size="10" font-family="JetBrains Mono, monospace">+ HasApiTokens, Notifiable</text>

        {{-- NASABAH Entity --}}
        <rect x="680" y="25" width="220" height="160" rx="10" fill="#F8FAFC" stroke="#0F766E" stroke-width="1.6" />
        <rect x="680" y="25" width="220" height="28" rx="8" fill="#0F766E" />
        <text x="692" y="43" fill="#FFFFFF" font-size="11.5" font-weight="800" font-family="JetBrains Mono, monospace">nasabah</text>
        <text x="692" y="70" fill="#0F172A" font-size="10.5" font-family="JetBrains Mono, monospace">PK id</text>
        <text x="692" y="87" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">UQ no_rekening, nis</text>
        <text x="692" y="104" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">UQ username</text>
        <text x="692" y="121" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   nama_siswa, kelas, jurusan</text>
        <text x="692" y="138" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   saldo (bigInt), status</text>
        <text x="692" y="155" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   pin_keamanan, deleted_at</text>
        <text x="692" y="172" fill="#64748B" font-size="10" font-family="JetBrains Mono, monospace">+ SoftDeletes, HasApiTokens</text>

        {{-- TRANSAKSI Entity (Center Top) --}}
        <rect x="340" y="30" width="245" height="165" rx="10" fill="#FFFFFF" stroke="#0F172A" stroke-width="1.8" />
        <rect x="340" y="30" width="245" height="28" rx="8" fill="#1E293B" />
        <text x="352" y="48" fill="#FFFFFF" font-size="11.5" font-weight="800" font-family="JetBrains Mono, monospace">transaksi</text>
        <text x="352" y="75" fill="#0F172A" font-size="10.5" font-family="JetBrains Mono, monospace">PK id</text>
        <text x="352" y="92" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK nasabah_id -&gt; nasabah.id</text>
        <text x="352" y="109" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK petugas_id -&gt; users.id</text>
        <text x="352" y="126" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK dibatalkan_oleh, ref_id</text>
        <text x="352" y="143" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   jenis_transaksi, kode_sandi</text>
        <text x="352" y="160" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   nominal, status</text>
        <text x="352" y="177" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">UQ idempotency_key</text>

        {{-- JURNAL Entity --}}
        <rect x="350" y="235" width="225" height="105" rx="10" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <rect x="350" y="235" width="225" height="26" rx="8" fill="#334155" />
        <text x="362" y="252" fill="#FFFFFF" font-size="11" font-weight="700" font-family="JetBrains Mono, monospace">jurnal</text>
        <text x="362" y="278" fill="#0F172A" font-size="10.5" font-family="JetBrains Mono, monospace">PK id</text>
        <text x="362" y="294" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK transaksi_id (nullable)</text>
        <text x="362" y="310" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   debit, kredit, tanggal</text>
        <text x="362" y="326" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   keterangan</text>

        {{-- BRANKAS Entity --}}
        <rect x="350" y="365" width="225" height="92" rx="10" fill="#ECFDF5" stroke="#10B981" stroke-width="1.4" />
        <rect x="350" y="365" width="225" height="26" rx="8" fill="#065F46" />
        <text x="362" y="382" fill="#FFFFFF" font-size="11" font-weight="700" font-family="JetBrains Mono, monospace">brankas (Kas Harian)</text>
        <text x="362" y="408" fill="#065F46" font-size="10.5" font-family="JetBrains Mono, monospace">PK id · UQ tanggal</text>
        <text x="362" y="424" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   saldo_awal, masuk, keluar</text>
        <text x="362" y="440" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   total (posisi kas fisik)</text>

        {{-- PENGAJUAN KAS --}}
        <rect x="20" y="215" width="230" height="115" rx="10" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <rect x="20" y="215" width="230" height="26" rx="8" fill="#334155" />
        <text x="32" y="232" fill="#FFFFFF" font-size="11" font-weight="700" font-family="JetBrains Mono, monospace">pengajuan_kas</text>
        <text x="32" y="257" fill="#0F172A" font-size="10.5" font-family="JetBrains Mono, monospace">PK id · UQ nomor_pengajuan</text>
        <text x="32" y="274" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK user_id -&gt; users.id</text>
        <text x="32" y="291" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK disetujui_oleh -&gt; users.id</text>
        <text x="32" y="308" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   nominal, status (pending/hold)</text>

        {{-- DAILY REPORTS --}}
        <rect x="20" y="355" width="230" height="115" rx="10" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <rect x="20" y="355" width="230" height="26" rx="8" fill="#334155" />
        <text x="32" y="372" fill="#FFFFFF" font-size="11" font-weight="700" font-family="JetBrains Mono, monospace">daily_reports</text>
        <text x="32" y="397" fill="#0F172A" font-size="10.5" font-family="JetBrains Mono, monospace">PK id · report_date</text>
        <text x="32" y="414" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK user_id, approved_by -&gt; users</text>
        <text x="32" y="431" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   system_balance, physical_balance</text>
        <text x="32" y="448" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   difference, status</text>

        {{-- SAVING GOALS --}}
        <rect x="680" y="220" width="220" height="105" rx="10" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <rect x="680" y="220" width="220" height="26" rx="8" fill="#0F766E" />
        <text x="692" y="237" fill="#FFFFFF" font-size="11" font-weight="700" font-family="JetBrains Mono, monospace">saving_goals</text>
        <text x="692" y="262" fill="#0F172A" font-size="10.5" font-family="JetBrains Mono, monospace">PK id</text>
        <text x="692" y="278" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK nasabah_id -&gt; nasabah.id</text>
        <text x="692" y="294" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   nama, target_amount, current</text>
        <text x="692" y="310" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   deadline, is_completed</text>

        {{-- PIN RESET REQUESTS --}}
        <rect x="680" y="350" width="220" height="110" rx="10" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <rect x="680" y="350" width="220" height="26" rx="8" fill="#334155" />
        <text x="692" y="367" fill="#FFFFFF" font-size="11" font-weight="700" font-family="JetBrains Mono, monospace">pin_reset_requests</text>
        <text x="692" y="392" fill="#0F172A" font-size="10.5" font-family="JetBrains Mono, monospace">PK id · UQ reset_token</text>
        <text x="692" y="408" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK nasabah_id -&gt; nasabah.id</text>
        <text x="692" y="424" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">FK approved_by -&gt; users.id</text>
        <text x="692" y="440" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">   status (pending/approved/...)</text>

        {{-- Bottom Bar: Governance & Operational Tables --}}
        <rect x="20" y="495" width="880" height="95" rx="10" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="1.4" />
        <text x="35" y="518" fill="#0F172A" font-size="11" font-weight="800" font-family="Plus Jakarta Sans, sans-serif">Entitas Tata Kelola, Avalis &amp; Sistem Pendukung:</text>
        <text x="35" y="540" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">• approval_histories (teller_id, supervisor_id -&gt; users) · avalis (user_id -&gt; users) · absensi_avalis</text>
        <text x="35" y="558" fill="#334155" font-size="10.5" font-family="JetBrains Mono, monospace">• verifikasi_kunjungan_siswa (UQ nasabah_id+tanggal) · audit_logs · password_reset_requests · pengaturan_sistem</text>
        <text x="35" y="576" fill="#0F766E" font-size="10.5" font-family="JetBrains Mono, monospace">• personal_access_tokens (morph tokenable: User | Nasabah) · notifications (morph notifiable: User | Nasabah)</text>

        {{-- Connectors --}}
        <line x1="235" y1="80" x2="338" y2="95" stroke="#0F766E" stroke-width="1.5" marker-end="url(#erd-many)" />
        <line x1="680" y1="90" x2="587" y2="90" stroke="#0F766E" stroke-width="1.5" marker-end="url(#erd-many)" />
        <line x1="462" y1="195" x2="462" y2="233" stroke="#0F766E" stroke-width="1.5" marker-end="url(#erd-many)" />
        <line x1="120" y1="170" x2="120" y2="213" stroke="#475569" stroke-width="1.5" marker-end="url(#erd-many)" />
        <line x1="78" y1="170" x2="78" y2="353" stroke="#475569" stroke-width="1.5" stroke-dasharray="4 3" marker-end="url(#erd-many)" />
        <line x1="790" y1="185" x2="790" y2="218" stroke="#0F766E" stroke-width="1.5" marker-end="url(#erd-many)" />
        <line x1="840" y1="185" x2="840" y2="348" stroke="#475569" stroke-width="1.5" stroke-dasharray="4 3" marker-end="url(#erd-many)" />
    </svg>
</div>

<x-docs.heading id="matriks-relasi-tabel" level="2">Matriks Relasi Antar Tabel</x-docs.heading>
<x-docs.table :headers="['Entitas Induk', 'Entitas Anak', 'Kardinalitas', 'Aturan On Delete / Catatan']">
    <tr>
        <td><code>nasabah</code></td>
        <td><code>transaksi</code></td>
        <td>1 : N</td>
        <td><code>cascade</code> — seluruh transaksi terhubung ke <code>nasabah_id</code> (menggunakan <code>SoftDeletes</code> agar arsip alumni tetap mempertahankan riwayat).</td>
    </tr>
    <tr>
        <td><code>nasabah</code></td>
        <td><code>saving_goals</code>, <code>pin_reset_requests</code></td>
        <td>1 : N</td>
        <td><code>cascade</code> — terhapus secara fisik jika baris <code>nasabah</code> benar-benar dihapus permanen.</td>
    </tr>
    <tr>
        <td><code>users</code></td>
        <td><code>transaksi</code> (<code>petugas_id</code> &amp; <code>dibatalkan_oleh</code>)</td>
        <td>1 : N</td>
        <td><code>set null</code> — riwayat buku besar tidak hilang walaupun akun petugas dihapus.</td>
    </tr>
    <tr>
        <td><code>users</code></td>
        <td><code>pengajuan_kas</code> &amp; <code>daily_reports</code></td>
        <td>1 : N</td>
        <td><code>cascade</code> untuk <code>user_id</code> (pemilik laporan/pengajuan), <code>set null</code> untuk <code>approved_by</code> / <code>disetujui_oleh</code>.</td>
    </tr>
    <tr>
        <td><code>users</code></td>
        <td><code>approval_histories</code> &amp; <code>avalis</code></td>
        <td>1 : N / 1 : 1</td>
        <td><code>set null</code> — riwayat keputusan supervisor dan data profil avalis tetap tersimpan.</td>
    </tr>
    <tr>
        <td><code>transaksi</code></td>
        <td><code>jurnal</code></td>
        <td>1 : N (nullable)</td>
        <td><code>cascade</code> pada <code>transaksi_id</code>; boleh bernilai <code>NULL</code> untuk jurnal droping kas non-nasabah.</td>
    </tr>
    <tr>
        <td><code>transaksi</code></td>
        <td><code>transaksi</code> (<code>ref_transaksi_id</code>)</td>
        <td>Self-referencing</td>
        <td><code>set null</code> — menghubungkan transaksi koreksi/pembalik (<code>koreksi_setor</code> / <code>koreksi_tarik</code>) ke transaksi asal yang dibatalkan.</td>
    </tr>
    <tr>
        <td><code>User</code> / <code>Nasabah</code></td>
        <td><code>personal_access_tokens</code> &amp; <code>notifications</code></td>
        <td>Polymorphic 1 : N</td>
        <td>Relasi morfologis Laravel (<code>tokenable_type/id</code> dan <code>notifiable_type/id</code>) yang melayani akun petugas dan nasabah sekaligus.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="kebijakan-integritas-data" level="2">Kebijakan Integritas &amp; Soft Deletes</x-docs.heading>
<p>
    Untuk melindungi integritas pencatatan akuntansi sekolah, <code>NasabahController::destroy()</code> memblokir penghapusan nasabah yang masih berstatus <code>aktif</code> dan hanya mengizinkan pengarsipan (<code>SoftDeletes</code>) pada akun nonaktif atau alumni sehingga relasi <code>transaksi.nasabah_id</code> tetap utuh di database. Selain itu, relasi <code>Transaksi::dailyReport()</code> didefinisikan pada level model melalui kolom <code>daily_report_id</code>.
</p>

