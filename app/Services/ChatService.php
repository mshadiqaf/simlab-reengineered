<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\JenisPengujian;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL   = 'llama-3.1-8b-instant';

    public function chat(string $userMessage, array $history, User $user): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt($user)],
        ];

        foreach (array_slice($history, -10) as $msg) {
            $messages[] = [
                'role'    => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $response = Http::timeout(30)
                ->withToken(config('services.groq.key'))
                ->post(self::API_URL, [
                    'model'       => self::MODEL,
                    'messages'    => $messages,
                    'temperature' => 0.3,
                    'max_tokens'  => 1024,
                ]);

            if (!$response->successful()) {
                Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);

                $reply = match ($response->status()) {
                    401     => 'Konfigurasi AI tidak valid. Hubungi administrator.',
                    429     => 'Layanan AI sedang sibuk. Silakan coba lagi dalam beberapa detik.',
                    503     => 'Layanan AI sedang tidak tersedia. Silakan coba lagi nanti.',
                    default => 'Maaf, saya sedang tidak bisa merespons. Silakan coba lagi.',
                };

                return ['reply' => $reply, 'formData' => null, 'suggestions' => null];
            }

            $text = $response->json('choices.0.message.content') ?? '';

            return $this->parseResponse($text);
        } catch (\Exception $e) {
            Log::error('ChatService exception', ['message' => $e->getMessage()]);
            return ['reply' => 'Terjadi kesalahan koneksi. Silakan coba lagi.', 'formData' => null, 'suggestions' => null];
        }
    }

    private function buildSystemPrompt(User $user): string
    {
        $ruangans = Ruangan::orderBy('nama_ruangan')->get()
            ->map(fn($r) => "  - ID {$r->id}: {$r->nama_ruangan} (kapasitas: {$r->kapasitas} orang)")
            ->join("\n");

        $alats = Alat::where('available_stock', '>', 0)->orderBy('nama_alat')->get()
            ->map(fn($a) => "  - ID {$a->id}: {$a->nama_alat} ({$a->available_stock} {$a->satuan} tersedia)")
            ->join("\n");

        $ujis = JenisPengujian::orderBy('nama_pengujian')->get()
            ->map(fn($u) => "  - ID {$u->id}: {$u->nama_pengujian}")
            ->join("\n");

        $now = now()->locale('id')->isoFormat('dddd, D MMMM YYYY, HH:mm');

        return <<<PROMPT
Kamu adalah asisten AI untuk SIMLAB (Sistem Informasi Manajemen Laboratorium ITK).
Tugasmu membantu mahasiswa mengajukan peminjaman/penggunaan fasilitas laboratorium.

ATURAN PENTING:
1. Hanya bantu hal-hal berkaitan dengan SIMLAB (peminjaman ruangan, alat, layanan pengujian).
2. Jangan menyetujui atau menolak pengajuan — itu tugas Kepala Lab dan Laboran.
3. Gunakan Bahasa Indonesia yang ramah dan profesional.
4. Tolak sopan jika permintaan di luar lingkup SIMLAB.
5. Waktu sekarang: {$now}

DATA PENGGUNA:
- Nama: {$user->name}
- NIM: {$user->nim}
- Program Studi: {$user->program_studi}

FASILITAS TERSEDIA:

Ruangan Laboratorium:
{$ruangans}

Alat Laboratorium:
{$alats}

Jenis Layanan Pengujian:
{$ujis}

JENIS PENGAJUAN & FIELD WAJIB:
1. Peminjaman Ruangan (ruangan): ruangan_id, tanggal_mulai, tanggal_selesai, waktu_mulai (HH:MM), waktu_selesai (HH:MM), jumlah_pengguna, tujuan_penggunaan, judul_proyek
2. Peminjaman Alat (alat): alat_id, jumlah_dipinjam, tanggal_mulai_alat, tanggal_selesai_alat, keperluan_spesifik, tujuan_penggunaan
3. Layanan Pengujian (pengujian): jenis_pengujian_id, nama_sampel, jumlah_sampel, tujuan_penggunaan

INSTRUKSI INTERAKTIF — WAJIB DIIKUTI:
Saat menanyakan pilihan dari database, taruh satu marker ini di baris PALING AKHIR pesanmu (tidak ada teks setelahnya):
- Menanyakan pilihan ruangan  → %%SUGGEST:ruangan%%
- Menanyakan pilihan alat     → %%SUGGEST:alat%%
- Menanyakan jenis pengujian  → %%SUGGEST:pengujian%%
- Meminta konfirmasi ya/tidak → %%SUGGEST:confirm%%

INSTRUKSI SKIP KE FORMULIR:
Jika pengguna mengatakan "lanjut ke formulir", "skip", "langsung saja", atau sejenisnya:
Keluarkan blok %%FORM_DATA%% dengan data yang SUDAH dikumpulkan dari percakapan ini.
Field yang belum diketahui tetap null. Partial data diperbolehkan asalkan tipe_pengajuan sudah diketahui.

INSTRUKSI EKSTRAKSI FORM:
Jika semua informasi sudah lengkap, ATAU pengguna ingin lanjut, keluarkan blok ini di AKHIR respons:

%%FORM_DATA%%
{
  "tipe_pengajuan": null,
  "nomor_hp": null,
  "judul_proyek": null,
  "tujuan_penggunaan": null,
  "dosen_pembimbing": null,
  "email_dosen": null,
  "ruangan_id": null,
  "tanggal_mulai": null,
  "tanggal_selesai": null,
  "waktu_mulai": null,
  "waktu_selesai": null,
  "jumlah_pengguna": null,
  "nama_pengguna_lainnya": null,
  "catatan_alat_bahan": null,
  "alat_id": null,
  "jumlah_dipinjam": null,
  "tanggal_mulai_alat": null,
  "tanggal_selesai_alat": null,
  "keperluan_spesifik": null,
  "durasi_jam": null,
  "jenis_pengujian_id": null,
  "nama_sampel": null,
  "jumlah_sampel": null,
  "keterangan_tambahan": null
}
%%END_FORM%%

- Isi hanya field yang relevan dan diketahui, sisanya null.
- Tanggal: YYYY-MM-DD. Waktu: HH:MM.
- Gunakan ID integer dari daftar fasilitas (bukan nama teks).
PROMPT;
    }

    private function parseResponse(string $rawText): array
    {
        $formData    = null;
        $suggestions = null;

        // 1. Extract FORM_DATA block
        if (str_contains($rawText, '%%FORM_DATA%%') && str_contains($rawText, '%%END_FORM%%')) {
            preg_match('/%%FORM_DATA%%\s*([\s\S]*?)\s*%%END_FORM%%/', $rawText, $matches);

            if (!empty($matches[1])) {
                $decoded = json_decode(trim($matches[1]), true);
                if (json_last_error() === JSON_ERROR_NONE && $this->hasMinimumData($decoded)) {
                    $formData = $decoded;
                }
            }

            $rawText = trim(preg_replace('/%%FORM_DATA%%[\s\S]*?%%END_FORM%%/', '', $rawText));
        }

        // 2. Extract SUGGEST marker (only show suggestions when there's no formData)
        if (!$formData && preg_match('/%%SUGGEST:(ruangan|alat|pengujian|confirm)%%/i', $rawText, $matches)) {
            $suggestions = $this->buildSuggestions(strtolower($matches[1]));
            $rawText = trim(preg_replace('/%%SUGGEST:\w+%%/i', '', $rawText));
        }

        return [
            'reply'       => trim($rawText) ?: 'Saya siap membantu. Silakan deskripsikan kebutuhan Anda.',
            'formData'    => $formData,
            'suggestions' => $suggestions,
        ];
    }

    private function hasMinimumData(array $data): bool
    {
        return in_array($data['tipe_pengajuan'] ?? null, ['ruangan', 'alat', 'pengujian'], true);
    }

    private function buildSuggestions(string $type): ?array
    {
        return match ($type) {
            'ruangan' => [
                'type'  => 'options',
                'items' => Ruangan::orderBy('nama_ruangan')->get()
                    ->map(fn($r) => [
                        'value' => $r->nama_ruangan,
                        'label' => "{$r->nama_ruangan} — kap. {$r->kapasitas} orang",
                    ])->values()->toArray(),
            ],
            'alat' => [
                'type'  => 'options',
                'items' => Alat::where('available_stock', '>', 0)->orderBy('nama_alat')->get()
                    ->map(fn($a) => [
                        'value' => $a->nama_alat,
                        'label' => "{$a->nama_alat} ({$a->available_stock} {$a->satuan} tersedia)",
                    ])->values()->toArray(),
            ],
            'pengujian' => [
                'type'  => 'options',
                'items' => JenisPengujian::orderBy('nama_pengujian')->get()
                    ->map(fn($u) => [
                        'value' => $u->nama_pengujian,
                        'label' => $u->nama_pengujian,
                    ])->values()->toArray(),
            ],
            'confirm' => [
                'type'  => 'confirm',
                'items' => [
                    ['value' => 'Ya, lanjutkan', 'label' => 'Ya, lanjutkan'],
                    ['value' => 'Tidak, ubah',   'label' => 'Tidak, ubah'],
                ],
            ],
            default => null,
        };
    }
}
