<?php

namespace App\Services;

use App\Models\SyncRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;

/**
 * Menyinkronkan tabel-tabel sumber (SIMABSARA2017) apa adanya (mirror),
 * tanpa normalisasi -- nama tabel & kolom lokal mengikuti nama sumber
 * (di-lowercase). Setiap method meng-query kolom yang persis sama dengan
 * definisi migration-nya, supaya aman di-upsert langsung.
 */
class SyncService
{
    protected const CHUNK_SIZE = 500;

    protected PDO $source;

    public function __construct(?PDO $source = null)
    {
        $this->source = $source ?? MdbConnection::connect();
    }

    public function syncTkodecuti(): int
    {
        $stmt = $this->source->query('SELECT KodeCuti, KeteranganCuti, KodeSapa FROM TKodeCuti');

        return $this->consume($stmt, 'tkodecuti', ['kodecuti'], function (array $row) {
            $kode = trim((string) $row['kodecuti']);

            if ($kode === '') {
                return null;
            }

            return [
                'kodecuti' => $kode,
                'keterangancuti' => $this->sanitizeUtf8($row['keterangancuti']),
                'kodesapa' => $this->sanitizeUtf8($row['kodesapa']),
            ];
        });
    }

    public function syncKodeFormasijabatan(): int
    {
        $stmt = $this->source->query('
            SELECT KD_TUGAS, KD_JABATAN, FORMASIJABATAN, usia_pensiun, kodetugasinpassing, IsDisplay, IsPLT
            FROM KODE_FORMASIJABATAN
            WHERE KD_JABATAN IS NOT NULL AND LTRIM(RTRIM(KD_JABATAN)) <> \'\'
        ');

        return $this->consume($stmt, 'kode_formasijabatan', ['kd_jabatan'], function (array $row) {
            $row['kd_jabatan'] = trim((string) $row['kd_jabatan']);

            return $this->sanitizeRow($row);
        });
    }

    public function syncSysdbInstansi(): int
    {
        $stmt = $this->source->query('
            SELECT INS_INSCOD, INS_KNDINS, INS_INSNAM, INS_LOCCOD, INS_APPCOD, INS_LASNIP, INS_LD2ANO,
                   INS_LC2ANO, INS_HIGPOS, PENGELOLA, AKTIF, Aturan
            FROM SYSDB_INSTANSI
            WHERE INS_INSCOD IS NOT NULL AND LTRIM(RTRIM(INS_INSCOD)) <> \'\'
        ');

        return $this->consume($stmt, 'sysdb_instansi', ['ins_inscod'], function (array $row) {
            $row['ins_inscod'] = trim((string) $row['ins_inscod']);

            return $this->sanitizeRow($row);
        });
    }

    public function syncSysdbWkguni(): int
    {
        $stmt = $this->source->query('
            SELECT WKU_INSCOD, WKU_WRKCOD, WKU_NAME, STATUS, WKU_UKERPJG, WKU_LAMA, WKU_ALAMAT,
                   WKU_TELEPON, SAPK_WKGUNI, LOKASI1, LOKASI2, JABATANIDCARD, Id_Unor
            FROM SYSDB_WKGUNI
            WHERE WKU_WRKCOD IS NOT NULL AND LTRIM(RTRIM(WKU_WRKCOD)) <> \'\'
        ');

        return $this->consume($stmt, 'sysdb_wkguni', ['wku_wrkcod'], function (array $row) {
            $row['wku_wrkcod'] = trim((string) $row['wku_wrkcod']);

            return $this->sanitizeRow($row);
        });
    }

    public function syncSysdbWkguniTampungan(): int
    {
        $stmt = $this->source->query('
            SELECT WKU_INSCOD, WKU_WRKCOD, WKU_NAME, STATUS, WKU_UKERPJG, WKU_LAMA, WKU_ALAMAT, WKU_TELEPON
            FROM SYSDB_WKGUNITAMPUNGAN
            WHERE WKU_WRKCOD IS NOT NULL AND LTRIM(RTRIM(WKU_WRKCOD)) <> \'\'
        ');

        return $this->consume($stmt, 'sysdb_wkgunitampungan', ['wku_wrkcod'], function (array $row) {
            $row['wku_wrkcod'] = trim((string) $row['wku_wrkcod']);

            return $this->sanitizeRow($row);
        });
    }

    public function syncTprofilunitkerja(): int
    {
        $stmt = $this->source->query('
            SELECT kodeuker, eselon, kodeinstansigaji, nipkepala, email, indeks, KodeStatusData, isvisible
            FROM TProfilUnitKerja
            WHERE kodeuker IS NOT NULL AND LTRIM(RTRIM(kodeuker)) <> \'\'
        ');

        return $this->consume($stmt, 'tprofilunitkerja', ['kodeuker'], function (array $row) {
            $row['kodeuker'] = trim((string) $row['kodeuker']);

            return $this->sanitizeRow($row);
        });
    }

    public function syncSysdbPnsdetail(): int
    {
        $stmt = $this->source->query('
            SELECT DTL_PNSNIP, DTL_PNSTLP, DTL_PNSFAX, DTL_PNSEMAIL, DTL_ADDRS, DTL_POSCOD, DTL_ADRLOC,
                   DTL_PNSNOT, DTL_JARGJTGL, [dukuh/jalan] AS dukuh_jalan, desa, kecamatan, kabupaten, provinsi,
                   Status_Data, UserUker, UpdtUker, DTL_PNSEMAILGOID, DTL_ADDRS200
            FROM SYSDB_PNSDETAIL
            WHERE DTL_PNSNIP IS NOT NULL AND LTRIM(RTRIM(DTL_PNSNIP)) <> \'\'
        ');

        return $this->consume($stmt, 'sysdb_pnsdetail', ['dtl_pnsnip'], function (array $row) {
            $row['dtl_pnsnip'] = trim((string) $row['dtl_pnsnip']);
            $row = $this->sanitizeRow($row);

            return $this->withDates($row, ['dtl_jargjtgl', 'updtuker']);
        });
    }

    /**
     * SYSDB_PNSKPG punya kolom modidatetime -- incremental by default,
     * hanya baris yang berubah sejak sync sukses terakhir yang diproses.
     * Gunakan $incremental=false untuk full re-scan (mis. setelah curiga
     * ada data drift, atau saat belum pernah sync sama sekali).
     *
     * Filter "sudah berubah sejak watermark" dilakukan di PHP (setelah
     * parse Carbon), BUKAN lewat "WHERE modidatetime > ?" di SQL Server --
     * data asli modidatetime formatnya tidak konsisten (campuran
     * "9/9/2026 8:17:03 AM" dan "Rabu, 01 Desember 2021 08.44.14"), jadi
     * perbandingan string mentah di SQL akan salah. Konsekuensinya: baris
     * yang TIDAK berubah tetap ditransfer dari sumber (network cost sama),
     * tapi tidak ikut di-upsert ke MySQL (mengurangi beban tulis lokal).
     */
    public function syncSysdbPnskpg(bool $incremental = true): int
    {
        $entity = 'sysdb_pnskpg';
        $since = $incremental ? $this->resolveWatermark($entity) : null;

        $stmt = $this->source->query('
            SELECT KPG_PNSNIP, KPG_PAYROLL, KPG_REKKOR, KPG_KARKRE, KPG_ASRNSI, KPG_JNASRN, KPG_KDEBIT,
                   KPG_TASNUM, KPG_ASKNUM, KPG_FARMSTS, KPG_NPWNUM, KPG_NIK, KPG_KK, iduser, modidatetime
            FROM SYSDB_PNSKPG
            WHERE KPG_PNSNIP IS NOT NULL AND LTRIM(RTRIM(KPG_PNSNIP)) <> \'\'
        ');

        $maxWatermark = null;

        $mapper = $this->incrementalMapper(function (array $row) {
            $row['kpg_pnsnip'] = trim((string) $row['kpg_pnsnip']);

            return $this->sanitizeRow($row);
        }, $since, $maxWatermark);

        $total = $this->consume($stmt, 'sysdb_pnskpg', ['kpg_pnsnip'], $mapper);

        $this->updateWatermark($entity, $maxWatermark, $total);

        return $total;
    }

    public function syncSysdbPns(): int
    {
        $stmt = $this->source->query('
            SELECT PNS_PNSNIP, PNS_PNSNAM, PNS_FTITLE, PNS_PNSPOB, PNS_PNSDOB, PNS_SEXCOD, PNS_RLGCOD,
                   PNS_SCICOD, PNS_STRNCO, PNS_ASEFDT, PNS_CCSEFD, PNS_KNDPNS, PNS_SRCDTA, PNS_BASREC,
                   PNS_CSEFDT, PNS_STAPNS, PNS_OATSTA, PNS_KPGSRN, PNS_MINCOD, PNS_INSCOD, PNS_WKUCOD,
                   PNS_GENPOS, PNS_ECHCOD, PNS_INADTE, PNS_ECHEFD, PNS_STRTNG, PNS_FTRCOD, PNS_CFPCOD,
                   PNS_MAINCP, PNS_SUPPCP, PNS_STLUD, PNS_KNDSAL, PNS_CRNCOD, PNS_CRNDTE, PNS_CRNSTA,
                   PNS_WPRANK, PNS_LRNCOD, PNS_POFCOD, PNS_KTUACO, PNS_WKLCOD, PNS_MARSTA, PNS_DPCWFE,
                   PNS_DPCCHL, PNS_AWRSTA, PNS_PNLCOD, PNS_STEPNS, PNS_POSPSO, PNS_PROORG, PNS_SOLORG,
                   PNS_GOLKAR, PNS_MNGLOC, PNS_REASTA, PNS_PENEFD, PNS_REPNAM, PNS_CREABY, PNS_CREADT,
                   PNS_UPDTBY, PNS_UPDTDT, PNS_BTCHID, PNS_POSTDT, PNS_TERCOD, PNS_TASNUM, PNS_ASKNUM,
                   PNS_KORCOD, PNS_FAMSTS, PNS_NPWNUM, PNS_NIKNUM, PNS_PNSADR, PNS_POSCOD, PNS_ADRLOC,
                   PNS_LAHIRLOC, PNS_UNKERLOC, PNS_TGLLSTR, PNS_JMJMSTR, PNS_TGLLFUN, PNS_JMJMFUN,
                   PNS_THLLPDK, PNS_JARGJCD, PNS_TMTPEN, PNS_PROSES, PNS_TMTGAJI, PNS_KETGAJI, PNS_JABTAM,
                   PNS_TMTTAM, PNS_KODGR2, PNS_PMKTH, PNS_PMKBL, KODE_FORMASI, NOMOR_SK_TUGAS, TGL_SK_TUGAS,
                   KD_PJB, NIP_BARU, UNIT_LAMA, PNS_THNLLS, PNS_KKRSNO1, PNS_NIKNO, PNS_JABATTAM,
                   PNS_RTITLE, PNS_KKSRNO, Status_Data, UserUker, UpdtUker, IDPNSBKN, ESELONBKN,
                   PNS_KODETPP, PNS_STEPNSTPP, PNS_CRNCODTPP, PNS_JabatanTPP, PNS_UnitKerjaTPP, PNS_IsTPP,
                   PNS_IsSAE, PNS_IsInspektorat, PNS_isHukdis, PNS_IsKinerja
            FROM SYSDB_PNS
            WHERE PNS_PNSNIP IS NOT NULL AND LTRIM(RTRIM(PNS_PNSNIP)) <> \'\'
        ');

        $total = $this->consume($stmt, 'sysdb_pns', ['pns_pnsnip'], function (array $row) {
            $row['pns_pnsnip'] = trim((string) $row['pns_pnsnip']);
            $row = $this->sanitizeRow($row);

            return $this->withDates($row, [
                'pns_ccsefd', 'pns_inadte', 'pns_echefd', 'pns_crndte', 'pns_penefd',
                'pns_creadt', 'pns_updtdt', 'pns_postdt', 'pns_tgllstr', 'pns_tgllfun',
                'pns_proses', 'pns_tmtgaji', 'pns_tmttam', 'tgl_sk_tugas', 'updtuker',
            ]);
        });

        $this->flagStatusAktif();

        return $total;
    }

    /**
     * Tandai status_aktif pada sysdb_pns dengan mencocokkan nip_baru
     * terhadap tb_pegawai_dump -- tabel referensi pegawai aktif yang
     * dikelola terpisah dari sinkronisasi SIMABSARA2017. Dilewati kalau
     * tabel referensinya tidak ada (mis. environment lain). Baris yang
     * status-nya sudah diatur manual (lewat prosedur Non Aktifkan /
     * Aktifkan Kembali) tidak disentuh, supaya keputusan admin tidak
     * tertimpa oleh sinkronisasi berikutnya.
     */
    protected function flagStatusAktif(): void
    {
        if (! Schema::hasTable('tb_pegawai_dump')) {
            return;
        }

        DB::statement('UPDATE sysdb_pns SET status_aktif = ? WHERE manual_override_status = 0', ['Tidak Aktif']);

        DB::statement('
            UPDATE sysdb_pns p
            INNER JOIN tb_pegawai_dump d ON d.nip = p.nip_baru
            SET p.status_aktif = ?
            WHERE p.manual_override_status = 0
        ', ['Aktif']);
    }

    /**
     * RiwayatCuti (123rb+ baris) punya kolom modidatetime -- incremental
     * by default, hanya baris yang berubah sejak sync sukses terakhir yang
     * diproses. Ini penting untuk performa jangka panjang, karena full
     * scan tiap sync akan makin lambat seiring data bertambah.
     *
     * Filter "sudah berubah sejak watermark" dilakukan di PHP (setelah
     * parse Carbon), BUKAN lewat "WHERE modidatetime > ?" di SQL Server --
     * data asli modidatetime formatnya tidak konsisten (campuran
     * "9/9/2026 8:17:03 AM" dan "Rabu, 01 Desember 2021 08.44.14"), jadi
     * perbandingan string mentah di SQL akan salah dan bisa melewatkan
     * baris yang sebetulnya berubah. Konsekuensinya: seluruh baris tetap
     * ditransfer dari sumber (network cost sama seperti full scan), tapi
     * yang tidak berubah tidak ikut di-upsert ke MySQL (mengurangi beban
     * tulis lokal, yang terbukti jadi porsi waktu terbesar pada sync ini).
     *
     * $detectDeletes menangani kasus pengajuan cuti yang dibatalkan/dihapus
     * di sumber (upsert saja tidak menghapus baris lokal yang sudah tidak
     * ada di sumber). Opt-in dan HANYA berlaku bersama full scan
     * ($incremental=false) -- pada incremental, "tidak terlihat di run ini"
     * tidak berarti "dihapus dari sumber" (bisa saja memang belum berubah),
     * jadi mengaktifkannya di incremental akan salah menghapus data valid.
     */
    public function syncRiwayatcuti(bool $incremental = true, bool $detectDeletes = false): int
    {
        $entity = 'riwayatcuti';

        // Prune cuma valid di atas full scan -- paksa nonaktifkan incremental
        // supaya "tidak tersentuh run ini" benar-benar berarti "hilang dari sumber".
        if ($detectDeletes) {
            $incremental = false;
        }

        $since = $incremental ? $this->resolveWatermark($entity) : null;
        $syncStartedAt = now();

        $stmt = $this->source->query('
            SELECT NIP, IDCLTN, JenisCuti, NOMORSKCLTN, TANGGALSKCLTN, TANGGALAWALCLTN, TANGGALAKHIRCLTN,
                   TANGGALAKTIFCLTN, NOBKN, TANGGALBKN, iduser, isvisible, modidatetime, lamahari,
                   KodeSatuanCuti, gelardepan, gelarbelakang, jabatan, unitkerja, masakerja, alasancuti,
                   catatancuti, n2, n2ket, n1, n1ket, n, nket, besar, sakit, melahirkan, penting, cltn,
                   alamatcuti, telpon, ansungsetuju, ansungrubah, ansungrubahalasan, ansungtangguh,
                   ansungtangguhalasan, ansungtidak, ansungtidakalasan, nipansung, pybsetuju, pybrubah,
                   pybrubahalasan, pybtangguh, pybtangguhalasan, pybtidak, pybtidakalasan, nippyb,
                   TglProses, CutiAlasanPenting, umpegtahunan, umpegbesar, umpegsakit, umpegmelahirkan,
                   umpegpenting, umpegcltn, catatanumpeg, dalamluarnegeri, umpegnip, catatanangsung,
                   ansungjabatanLain, PybAtasanLain, AnsungJabatan, AnsungUnitKerja, AnsungTambahan,
                   PybJabatan, PybUnitKerja, PybTambahan, NomorPengantar, TanggalPengantar, Golru,
                   idbatchLuarNegeri
            FROM RiwayatCuti
            WHERE NIP IS NOT NULL AND TANGGALAWALCLTN IS NOT NULL
        ');

        $maxWatermark = null;

        $mapper = $this->incrementalMapper(function (array $row) {
            $nip = trim((string) $row['nip']);
            $tanggalAwal = $this->parseDate($row['tanggalawalcltn']);

            if ($nip === '' || $tanggalAwal === null) {
                return null;
            }

            $row['nip'] = $nip;
            $row['jeniscuti'] = trim((string) ($row['jeniscuti'] ?? ''));
            $row = $this->sanitizeRow($row);
            $row = $this->withDates($row, ['tanggalskcltn', 'tanggalaktifcltn', 'tanggalbkn', 'tglproses', 'tanggalpengantar']);

            $row['tanggalawalcltn'] = $tanggalAwal;
            $row['tanggalakhircltn'] = $this->parseDate($row['tanggalakhircltn']);

            return $row;
        }, $since, $maxWatermark);

        $total = $this->consume($stmt, 'riwayatcuti', ['nip', 'jeniscuti', 'tanggalawalcltn'], $mapper);

        if ($detectDeletes) {
            // Baris lokal yang synced_at-nya lebih lama dari sebelum run ini
            // dimulai berarti tidak tersentuh full scan barusan -> sudah
            // tidak ada lagi di sumber. Margin 2 detik untuk toleransi
            // presisi timestamp, supaya baris yang baru saja di-upsert
            // tidak salah kehapus.
            $this->deleteStaleRows('riwayatcuti', $syncStartedAt->copy()->subSeconds(2));
        }

        $this->updateWatermark($entity, $maxWatermark, $total);

        return $total;
    }

    /**
     * Jalankan query sumber baris demi baris (streaming, tidak fetchAll),
     * transformasi lewat $mapper, lalu upsert per {@see self::CHUNK_SIZE}
     * (masing-masing batch dalam satu transaction, lihat {@see self::flush()}).
     * $mapper mengembalikan null untuk melewati baris yang tidak valid.
     * $onRow (opsional) dipanggil untuk tiap baris yang lolos mapping,
     * dipakai pemanggil untuk melacak watermark modidatetime dsb, tanpa
     * mengubah alur upsert-nya sendiri.
     */
    protected function consume(\PDOStatement $stmt, string $table, array $uniqueBy, callable $mapper, ?callable $onRow = null): int
    {
        $now = now();
        $buffer = [];
        $total = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row = array_change_key_case($row, CASE_LOWER);

            $mapped = $mapper($row);

            if ($mapped === null) {
                continue;
            }

            if ($onRow) {
                $onRow($mapped);
            }

            $mapped['synced_at'] = $now;
            $mapped['created_at'] = $now;
            $mapped['updated_at'] = $now;

            $buffer[] = $mapped;

            if (count($buffer) >= static::CHUNK_SIZE) {
                $total += $this->flush($buffer, $table, $uniqueBy);
            }
        }

        $total += $this->flush($buffer, $table, $uniqueBy);

        return $total;
    }

    /**
     * Terapkan sanitasi UTF-8 ke semua nilai string dalam baris. Kolom
     * bertipe datetime pada sumber ditangani eksplisit lewat
     * {@see self::withDates()} di masing-masing method sync, bukan
     * ditebak dari nama kolom -- lebih aman daripada heuristik nama.
     */
    protected function sanitizeRow(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = $this->sanitizeUtf8($value);
            }
        }

        return $row;
    }

    /**
     * Parse ulang kolom-kolom tertentu dalam $row sebagai datetime.
     */
    protected function withDates(array $row, array $columns): array
    {
        foreach ($columns as $column) {
            if (array_key_exists($column, $row)) {
                $row[$column] = $this->parseDateTime($row[$column]);
            }
        }

        return $row;
    }

    /**
     * Data lama pada sumber kadang tersimpan sebagai Windows-1252 meski
     * kolomnya nvarchar, sehingga byte seperti en-dash (0x96) lolos dan
     * membuat MySQL (utf8mb4) menolak insert.
     */
    protected function sanitizeUtf8(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        }

        return $value;
    }

    protected function parseDate(mixed $value): ?string
    {
        return $this->parseDateTime($value)?->toDateString();
    }

    /**
     * Data modidatetime & kolom tanggal lain di sumber ternyata campur
     * beberapa format tidak standar (54,7% baris modidatetime RiwayatCuti
     * gagal di-parse Carbon::parse() polos): "DD/MM/YYYY HH.MM.SS",
     * "DD-MM-YYYY:HH:MM:SS", dan nama hari Indonesia panjang seperti
     * "Rabu, 01 Desember 2021 08.44.14". Coba pola-pola eksplisit itu dulu
     * (lihat {@see self::parseKnownDateFormats()}) sebelum jatuh ke
     * Carbon::parse() generik yang menangani format umum (mis. gaya
     * Amerika "9/9/2026 8:17:03 AM" yang justru sudah berhasil di situ).
     */
    protected function parseDateTime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if ($parsed = $this->parseKnownDateFormats($value)) {
            return $parsed;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseKnownDateFormats(string $value): ?Carbon
    {
        // DD/MM/YYYY HH.MM.SS atau DD/MM/YYYY HH:MM:SS (jam boleh 1-2 digit).
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2})[.:](\d{2})[.:](\d{2})$/', $value, $m)) {
            return $this->safeCreate((int) $m[3], (int) $m[2], (int) $m[1], (int) $m[4], (int) $m[5], (int) $m[6]);
        }

        // Sama seperti di atas tapi tahun 2 atau 4 digit, dan boleh ada
        // suffix AM/PM, mis. "17/02/22 8:38:42 AM" atau "20/02/2021 8:24:31 AM".
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2}|\d{4})\s+(\d{1,2})[.:](\d{2})[.:](\d{2})\s*(AM|PM)?$/i', $value, $m)) {
            $hour = (int) $m[4];
            $meridiem = strtoupper($m[7] ?? '');

            if ($meridiem === 'PM' && $hour < 12) {
                $hour += 12;
            } elseif ($meridiem === 'AM' && $hour === 12) {
                $hour = 0;
            }

            $year = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];

            return $this->safeCreate($year, (int) $m[2], (int) $m[1], $hour, (int) $m[5], (int) $m[6]);
        }

        // DD-MM-YYYY:HH:MM:SS (dash untuk tanggal, langsung ":" tanpa spasi sebelum jam).
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4}):(\d{1,2}):(\d{2}):(\d{2})$/', $value, $m)) {
            return $this->safeCreate((int) $m[3], (int) $m[2], (int) $m[1], (int) $m[4], (int) $m[5], (int) $m[6]);
        }

        // "Nama Hari, DD NamaBulanIndonesia YYYY HH.MM.SS" (mis. "Rabu, 01 Desember 2021 08.44.14").
        if (preg_match('/^\p{L}+,\s*(\d{1,2})\s+(\p{L}+)\s+(\d{4})\s+(\d{1,2})[.:](\d{2})[.:](\d{2})$/u', $value, $m)) {
            $months = [
                'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
                'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            ];
            $month = $months[mb_strtolower($m[2])] ?? null;

            if ($month !== null) {
                return $this->safeCreate((int) $m[3], $month, (int) $m[1], (int) $m[4], (int) $m[5], (int) $m[6]);
            }
        }

        return null;
    }

    protected function safeCreate(int $year, int $month, int $day, int $hour, int $minute, int $second): ?Carbon
    {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31 || $hour > 23 || $minute > 59 || $second > 59) {
            return null;
        }

        try {
            return Carbon::create($year, $month, $day, $hour, $minute, $second);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Transaksi per-batch: kalau upsert satu batch gagal di tengah jalan,
     * batch itu di-rollback utuh (tidak ada baris setengah-update), dan
     * exception-nya naik ke pemanggil (SyncDataCommand) yang mencatat
     * status gagal + pesan error ke sync_logs untuk audit. Batch-batch
     * sebelumnya yang sudah commit tetap aman tersimpan.
     */
    protected function flush(array &$buffer, string $table, array $uniqueBy): int
    {
        if (empty($buffer)) {
            return 0;
        }

        $count = count($buffer);

        $updateColumns = array_diff(array_keys($buffer[0]), $uniqueBy, ['created_at']);

        DB::transaction(function () use ($table, $buffer, $uniqueBy, $updateColumns) {
            DB::table($table)->upsert($buffer, $uniqueBy, $updateColumns);
        });

        $buffer = [];

        return $count;
    }

    /**
     * Watermark tersimpan (last_watermark) untuk entitas ini -- basis
     * filter incremental berikutnya. Null kalau belum pernah sync sukses,
     * pemanggil lalu otomatis full scan.
     */
    protected function resolveWatermark(string $entity): ?Carbon
    {
        return SyncRun::where('entity', $entity)->value('last_watermark');
    }

    /**
     * Bungkus $innerMapper dengan logika watermark incremental: selalu
     * lacak modidatetime maksimum dari SEMUA baris mentah yang terlihat
     * (byref $maxWatermark, sebelum transformasi $innerMapper -- supaya
     * watermark maju walau barisnya sendiri di-skip), lalu skip baris
     * (return null, tidak ikut di-upsert) kalau $since diisi dan
     * modidatetime baris ini belum lebih baru dari watermark tersimpan.
     *
     * Perbandingan dilakukan di PHP setelah parse Carbon, bukan lewat
     * WHERE di SQL Server -- lihat catatan di syncRiwayatcuti().
     */
    protected function incrementalMapper(callable $innerMapper, ?Carbon $since, ?Carbon &$maxWatermark): callable
    {
        return function (array $row) use ($innerMapper, $since, &$maxWatermark) {
            $parsed = $this->parseDateTime($row['modidatetime'] ?? null);

            if ($parsed !== null && ($maxWatermark === null || $parsed->gt($maxWatermark))) {
                $maxWatermark = $parsed;
            }

            if ($since !== null && $parsed !== null && $parsed->lte($since)) {
                return null;
            }

            return $innerMapper($row);
        };
    }

    /**
     * Simpan watermark baru + waktu sync terakhir untuk entitas ini.
     * Kalau tidak ada baris ber-modidatetime valid yang diproses (mis.
     * incremental run kosong, atau tabel tidak punya kolom modidatetime),
     * last_watermark lama tetap dipertahankan -- hanya last_synced_at
     * & last_records_count yang diperbarui.
     */
    protected function updateWatermark(string $entity, ?Carbon $newWatermark, int $recordsCount): void
    {
        $values = [
            'last_synced_at' => now(),
            'last_records_count' => $recordsCount,
        ];

        if ($newWatermark !== null) {
            $values['last_watermark'] = $newWatermark;
        }

        SyncRun::updateOrCreate(['entity' => $entity], $values);
    }

    /**
     * Hapus baris lokal yang synced_at-nya lebih lama dari $before --
     * dipakai setelah full scan untuk membuang baris yang sudah tidak ada
     * di sumber (lihat dokumentasi $detectDeletes pada syncRiwayatcuti()).
     */
    protected function deleteStaleRows(string $table, Carbon $before): int
    {
        return DB::table($table)->where('synced_at', '<', $before)->delete();
    }
}
