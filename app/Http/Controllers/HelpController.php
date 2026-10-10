<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Pusat Bantuan in-app: me-render file markdown di docs/bantuan/*.md
 * menjadi halaman HTML dengan layout aplikasi. Tidak perlu toolchain
 * Node/Docusaurus — cukup PHP + league/commonmark.
 */
class HelpController extends Controller
{
    /**
     * Daftar topik: slug => [judul, deskripsi, ikon lucide].
     *
     * @return array<string, array{0:string,1:string,2:string}>
     */
    public static function topics(): array
    {
        return [
            'masuk' => ['Masuk ke Aplikasi', 'Login dengan kode OTP WhatsApp', 'log-in'],
            'pindah-sekolah' => ['Berpindah Sekolah', 'Ganti sekolah aktif', 'repeat'],
            'keamanan-akun' => ['Keamanan Akun', 'Verifikasi 2 langkah (2FA)', 'shield-check'],
            'dashboard' => ['Dashboard', 'Ringkasan halaman utama', 'layout-dashboard'],
            'siswa' => ['Data Siswa', 'Kelola data siswa', 'users'],
            'guru' => ['Data Guru & Pegawai', 'Kelola guru dan tendik', 'graduation-cap'],
            'kelas' => ['Data Kelas', 'Rombel, wali kelas, penempatan', 'school'],
            'jadwal' => ['Jadwal Pelajaran', 'Susun jadwal mingguan', 'calendar-days'],
            'absensi' => ['Absensi Siswa', 'Catat kehadiran H/I/S/A', 'clipboard-check'],
            'nilai' => ['Data Nilai', 'Input nilai, leger, rapor', 'award'],
            'ppdb' => ['PPDB Online', 'Periode, seleksi, daftar ulang', 'user-plus'],
            'keuangan' => ['Keuangan SPP', 'Tagihan dan pembayaran', 'wallet'],
            'pengumuman' => ['Pengumuman', 'Buat dan tayangkan pengumuman', 'megaphone'],
            'laporan' => ['Laporan', 'Laporan bawaan dan ekspor', 'bar-chart-3'],
            'erapor' => ['E-Rapor', 'Pratinjau dan cetak rapor', 'file-text'],
            'tatib' => ['Tata Tertib', 'Pelanggaran dan poin', 'shield-alert'],
            'bk' => ['BK & Konseling', 'Sesi konseling siswa', 'heart-handshake'],
            'perpus' => ['Perpustakaan', 'Katalog dan peminjaman buku', 'library'],
            'ekskul' => ['Ekstrakurikuler & Prestasi', 'Ekstrakurikuler dan prestasi', 'medal'],
            'lms' => ['LMS Mini / Tugas Online', 'Tugas, pengumpulan, penilaian', 'pen-line'],
            'broadcast' => ['Broadcast', 'Broadcast WA & notifikasi terjadwal', 'send'],
            'aset' => ['Inventaris & Aset', 'Kelola aset dan mutasi', 'package'],
            'uks' => ['UKS & Kesehatan', 'Kunjungan UKS dan obat', 'heart-pulse'],
            'transportasi' => ['Antar-Jemput', 'Rute dan penumpang', 'bus'],
            'kantin' => ['Kantin Digital', 'Produk, kasir, dan transaksi', 'store'],
            'survei' => ['Survei & Evaluasi', 'Survei dan hasil agregat', 'clipboard-list'],
            'alumni' => ['Alumni & Karier', 'Data alumni dan tracer study', 'briefcase'],
            'organisasi' => ['Organisasi', 'Pengurus dan kegiatan organisasi', 'users-round'],
            'pengaturan' => ['Pengaturan', 'Profil sekolah, tahun ajaran, modul', 'settings'],
            'peran' => ['Peran & Hak Akses', 'Daftar peran pengguna', 'key-round'],
            'faq' => ['Pertanyaan Umum', 'Jawaban pertanyaan umum', 'circle-help'],
        ];
    }

    public function index(): View
    {
        $slot = new HtmlString(view('bantuan.index', ['topics' => self::topics()])->render());

        return view('layouts.app', ['title' => 'Pusat Bantuan', 'slot' => $slot]);
    }

    public function show(Request $request, string $topik): View
    {
        if (! array_key_exists($topik, self::topics())) {
            throw new NotFoundHttpException('Topik bantuan tidak ditemukan.');
        }

        $path = base_path("docs/bantuan/{$topik}.md");
        $markdown = File::exists($path)
            ? File::get($path)
            : "# {$topik}\n\nDokumentasi topik ini sedang disusun.";

        $environment = new Environment;
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $html = (new MarkdownConverter($environment))->convert($markdown)->getContent();

        $slot = new HtmlString(view('bantuan.show', [
            'topics' => self::topics(),
            'active' => $topik,
            'title' => self::topics()[$topik][0],
            'html' => new HtmlString($html),
        ])->render());

        return view('layouts.app', ['title' => 'Bantuan: '.self::topics()[$topik][0], 'slot' => $slot]);
    }
}
