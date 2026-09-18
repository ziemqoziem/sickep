<?php

namespace Database\Seeders;

use App\Models\JenisCutiAturan;
use Illuminate\Database\Seeder;

class JenisCutiAturanSeeder extends Seeder
{
    /**
     * Data acuan diambil dari ketentuan-cuti.md (ringkasan Perban BKN No. 7/2021).
     */
    public function run(): void
    {
        $rows = [
            [
                'kode' => 'TAHUNAN',
                'nama' => 'Cuti Tahunan',
                'syarat_masa_kerja_bulan' => 12,
                'jatah_hari' => 12,
                'carry_over_hari' => 6,
                'maks_hari' => null,
                'perlu_dokumen' => false,
                'butuh_persetujuan_admin' => false,
                'keterangan' => 'Hak 12 hari kerja/tahun. Sisa tahun lalu bisa dipakai maksimal 6 hari. Jika ditangguhkan PPK, hak tahun berikutnya jadi 24 hari (termasuk hak tahun berjalan).',
            ],
            [
                'kode' => 'BESAR',
                'nama' => 'Cuti Besar',
                'syarat_masa_kerja_bulan' => 60,
                'jatah_hari' => null,
                'carry_over_hari' => null,
                'maks_hari' => 90,
                'perlu_dokumen' => false,
                'butuh_persetujuan_admin' => true,
                'keterangan' => 'Maksimal 3 bulan. Tidak berhak Cuti Tahunan di tahun yang sama. Sisa cuti besar yang tidak terpakai hangus. Dipakai juga untuk ibadah haji dan kelahiran anak ke-4+.',
            ],
            [
                'kode' => 'SAKIT',
                'nama' => 'Cuti Sakit',
                'syarat_masa_kerja_bulan' => null,
                'jatah_hari' => null,
                'carry_over_hari' => null,
                'maks_hari' => 365,
                'perlu_dokumen' => true,
                'butuh_persetujuan_admin' => false,
                'keterangan' => 'Maksimal 1 tahun, dapat diperpanjang 6 bulan lagi dengan surat keterangan tim penguji kesehatan. Wajib lampiran surat keterangan dokter. Keguguran maksimal 1,5 bulan; kecelakaan kerja tidak dibatasi 1 tahun.',
            ],
            [
                'kode' => 'MELAHIRKAN',
                'nama' => 'Cuti Melahirkan',
                'syarat_masa_kerja_bulan' => null,
                'jatah_hari' => null,
                'carry_over_hari' => null,
                'maks_hari' => 90,
                'perlu_dokumen' => false,
                'butuh_persetujuan_admin' => false,
                'keterangan' => 'Maksimal 3 bulan, untuk kelahiran anak pertama sampai ketiga. Anak keempat dan seterusnya memakai skema Cuti Besar.',
            ],
            [
                'kode' => 'ALASAN_PENTING',
                'nama' => 'Cuti Karena Alasan Penting',
                'syarat_masa_kerja_bulan' => null,
                'jatah_hari' => null,
                'carry_over_hari' => null,
                'maks_hari' => 30,
                'perlu_dokumen' => false,
                'butuh_persetujuan_admin' => false,
                'keterangan' => 'Maksimal 1 bulan, ditentukan PPK/pejabat delegasi. Meliputi keluarga sakit keras/meninggal, pernikahan, istri melahirkan/operasi caesar (PNS laki-laki), musibah kebakaran/bencana alam.',
            ],
            [
                'kode' => 'BERSAMA',
                'nama' => 'Cuti Bersama',
                'syarat_masa_kerja_bulan' => null,
                'jatah_hari' => null,
                'carry_over_hari' => null,
                'maks_hari' => null,
                'perlu_dokumen' => false,
                'butuh_persetujuan_admin' => false,
                'keterangan' => 'Mengikuti kalender cuti bersama nasional. Tidak mengurangi hak Cuti Tahunan; bagi jabatan yang tidak diberikan cuti bersama, hak Cuti Tahunan ditambah sejumlah hari cuti bersama yang tidak diberikan.',
            ],
            [
                'kode' => 'CLTN',
                'nama' => 'Cuti di Luar Tanggungan Negara',
                'syarat_masa_kerja_bulan' => 60,
                'jatah_hari' => null,
                'carry_over_hari' => null,
                'maks_hari' => 1095,
                'perlu_dokumen' => true,
                'butuh_persetujuan_admin' => true,
                'keterangan' => 'Maksimal 3 tahun, dapat diperpanjang 1 tahun. PNS diberhentikan dari jabatannya, masa kerja tidak dihitung, dan tidak menerima penghasilan selama CLTN. Wajib lapor tertulis maks. 1 bulan setelah selesai.',
            ],
        ];

        foreach ($rows as $row) {
            JenisCutiAturan::updateOrCreate(['kode' => $row['kode']], $row);
        }
    }
}
