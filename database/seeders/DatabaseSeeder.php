<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'admin',
                'nama_petugas' => 'Administrator Utama',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'kelas' => 'Staff Administrasi',
                'no_hp' => '081122334455',
            ],
            [
                'username' => 'teller1',
                'nama_petugas' => 'Petugas Loket Teller 1',
                'password' => Hash::make('teller123'),
                'role' => 'teller',
                'kelas' => 'XII AKL 1',
                'no_hp' => '081234567890',
            ],
            [
                'username' => 'teller2',
                'nama_petugas' => 'Petugas Loket Teller 2',
                'password' => Hash::make('teller123'),
                'role' => 'teller',
                'kelas' => 'XII AKL 2',
                'no_hp' => '085712345678',
            ],
            [
                'username' => 'spv1',
                'nama_petugas' => 'Kepala Bank Mini',
                'password' => Hash::make('spv123'),
                'role' => 'supervisor',
                'kelas' => 'Guru Pembimbing',
                'no_hp' => '081398765432',
            ],
        ];

        foreach ($users as $userData) {
            $user = DB::table('users')->where('username', $userData['username'])->first();
            if ($user) {
                DB::table('users')->where('id', $user->id)->update([
                    'nama_petugas' => $userData['nama_petugas'],
                    'password' => $userData['password'],
                    'role' => $userData['role'],
                    'kelas' => $userData['kelas'],
                    'no_hp' => $userData['no_hp'],
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('users')->insert([
                    'username' => $userData['username'],
                    'nama_petugas' => $userData['nama_petugas'],
                    'password' => $userData['password'],
                    'role' => $userData['role'],
                    'kelas' => $userData['kelas'],
                    'no_hp' => $userData['no_hp'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Sinkronisasi otomatis ke tabel avalis untuk teller
        $tellers = DB::table('users')->where('role', 'teller')->get();
        foreach ($tellers as $t) {
            $avalis = DB::table('avalis')->where('user_id', $t->id)->first();
            if ($avalis) {
                DB::table('avalis')->where('id', $avalis->id)->update([
                    'nama' => $t->nama_petugas,
                    'kelas' => $t->kelas ?: 'XII AKL 1',
                    'no_wa' => $t->no_hp ?: '081234567890',
                    'status' => 'aktif',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('avalis')->insert([
                    'nama' => $t->nama_petugas,
                    'kelas' => $t->kelas ?: 'XII AKL 1',
                    'no_wa' => $t->no_hp ?: '081234567890',
                    'user_id' => $t->id,
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $nasabahs = [
            [
                'no_rekening' => '202601001',
                'nis' => '17763',
                'nama_siswa' => 'Dewi Handayani Nur Halimah',
                'kelas' => 'XI RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'username' => 'Handa',
                'password' => Hash::make('password123'),
                'pin_keamanan' => Hash::make('240808'),
                'saldo' => 50000,
                'status' => 'aktif',
                'kategori' => 'siswa',
            ],
        ];

        foreach ($nasabahs as $nasabahData) {
            $nasabah = DB::table('nasabah')->where('nis', $nasabahData['nis'])->first();
            if ($nasabah) {
                DB::table('nasabah')->where('id', $nasabah->id)->update([
                    'nama_siswa' => $nasabahData['nama_siswa'],
                    'kelas' => $nasabahData['kelas'],
                    'jurusan' => $nasabahData['jurusan'],
                    'status' => $nasabahData['status'],
                    'kategori' => $nasabahData['kategori'],
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('nasabah')->insert(array_merge($nasabahData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
