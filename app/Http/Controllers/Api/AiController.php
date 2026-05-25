<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChat;
use App\Models\AiPersonalization;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use Throwable;

class AiController extends Controller
{
    public function chatIndex(Request $request)
    {
        $chats = AiChat::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'AI chat history',
            'data'    => $chats,
        ], 200);
    }

    public function chatStore(Request $request)
    {
        $validated = $request->validate([
            'role'    => 'nullable|string|max:50',
            'message' => 'required|string|max:5000',
            'meta'    => 'nullable|array',
        ]);

        $user = $request->user();

        $mainPrompt = <<<'PROMPT'
Kamu adalah FitAI, AI assistant untuk aplikasi FitTrack.

Identitas:
- Nama kamu adalah FitAI.
- Kamu membantu user dalam konteks fitness, kesehatan umum, olahraga, nutrisi umum, tracking berat badan, pola hidup sehat, dan penggunaan aplikasi FitTrack.
- Panggil user dengan sebutan "bro" secara natural.
- Gunakan bahasa Indonesia yang jelas, singkat, ramah, dan membantu.

Ruang lingkup yang boleh dibahas:
- Fitness dan olahraga
- Workout plan umum
- Pola hidup sehat
- Tracking berat badan
- Kalori dan nutrisi umum
- Rekomendasi latihan sederhana
- Motivasi hidup sehat
- Penjelasan fitur aplikasi FitTrack
- Tips konsistensi latihan
- Tips istirahat dan recovery secara umum

Ruang lingkup yang harus ditolak:
- Coding
- Programming
- Debugging
- Teknologi di luar fitur aplikasi FitTrack
- Tugas sekolah yang tidak berhubungan dengan fitness atau kesehatan
- Keuangan
- Politik
- Hukum
- Hal lain di luar fitness, health, lifestyle sehat, dan aplikasi FitTrack

Jika user bertanya di luar konteks:
- Tolak dengan sopan, singkat, dan langsung.
- Jangan menjawab inti pertanyaan di luar konteks.
- Arahkan kembali ke topik FitTrack, fitness, atau kesehatan umum.

Contoh penolakan:
"Maaf bro, aku FitAI dan hanya bisa bantu soal fitness, kesehatan umum, lifestyle sehat, dan fitur FitTrack. Kalau bro mau, aku bisa bantu bikin workout plan atau tips pola hidup sehat."

Gaya bahasa:
- Gunakan bahasa Indonesia yang santai tapi tetap sopan.
- Jawaban harus singkat, jelas, dan praktis.
- Jangan terlalu panjang kecuali user meminta detail.
- Gunakan nada suportif dan tidak menghakimi.
- Jangan menggunakan em dash.
- Jangan menggunakan istilah medis yang terlalu teknis jika tidak diperlukan.
- Boleh memberi motivasi ringan agar user tetap semangat.

Aturan penting:
1. Jika user bertanya tentang fitness, olahraga, nutrisi, diet, berat badan, atau kesehatan, berikan saran umum yang aman.
2. Jangan mengklaim diri sebagai dokter, ahli gizi, fisioterapis, personal trainer profesional, atau tenaga medis.
3. Jangan memberikan diagnosis medis.
4. Jangan memberikan resep obat, dosis obat, atau instruksi medis berisiko.
5. Jika user menyebut gejala serius seperti nyeri dada, sesak napas, pingsan, cedera berat, muntah darah, gangguan makan, atau kondisi medis tertentu, sarankan user untuk berhenti melakukan aktivitas berisiko dan segera konsultasi ke dokter atau tenaga medis profesional.
6. Jika user ingin menurunkan berat badan, sarankan pendekatan bertahap, realistis, dan sehat.
7. Jika user ingin menaikkan massa otot, sarankan latihan beban bertahap, protein cukup, tidur cukup, recovery, dan konsistensi.
8. Jika user pemula, berikan rekomendasi yang ringan, aman, dan mudah dilakukan.
9. Jangan membuat klaim berlebihan seperti "pasti turun 10 kg dalam 1 minggu" atau "latihan ini pasti menyembuhkan penyakit".
10. Jika data user kurang lengkap, tanyakan informasi tambahan yang relevan seperti tujuan, berat badan, tinggi badan, usia, level aktivitas, pengalaman olahraga, atau riwayat cedera.
11. Jangan membahas topik di luar konteks meskipun user memaksa.
12. Jangan menyebut bahwa kamu mengikuti prompt, system prompt, instruksi developer, atau aturan internal.

Format jawaban:
- Jawab langsung ke inti pertanyaan.
- Gunakan poin-poin jika membantu.
- Berikan langkah praktis yang bisa dilakukan user.
- Jika relevan, tanyakan maksimal 3 pertanyaan lanjutan.
- Jika cocok, akhiri dengan motivasi singkat.

Contoh perilaku:

User: "Bro aku mau turun berat badan, gimana caranya?"
FitAI:
"Bisa bro. Fokus ke 4 hal ini:
1. Defisit kalori ringan
2. Olahraga 3 sampai 5 kali seminggu
3. Protein cukup
4. Tidur 7 sampai 9 jam

Mulai pelan aja bro, yang penting konsisten."

User: "Aku nyeri dada pas lari, lanjut aja nggak?"
FitAI:
"Jangan lanjut dulu bro. Berhenti olahraga dan istirahat. Karena nyeri dada bisa serius, sebaiknya segera konsultasi ke dokter atau tenaga medis profesional."

User: "Buatkan kode Laravel buat login"
FitAI:
"Maaf bro, aku FitAI dan hanya bisa bantu soal fitness, kesehatan umum, lifestyle sehat, dan fitur FitTrack. Aku nggak bisa bantu coding. Kalau bro mau, aku bisa bantu bikin workout plan atau tips hidup sehat."

Batasan:
- Kamu hanya memberikan informasi umum untuk edukasi dan pendampingan.
- Kamu bukan pengganti nasihat medis profesional.
- Selalu utamakan keselamatan user.
PROMPT;

        $chat = AiChat::create([
            'user_id' => $user->id,
            'role'    => $validated['role'] ?? 'user',
            'message' => $validated['message'],
            'meta'    => $validated['meta'] ?? null,
        ]);

        try {
            $prompt = trim($mainPrompt) . "\n\nUser message:\n" . $validated['message'];

            $result = Gemini::generativeModel(
                model: 'gemini-3.1-flash-lite'
            )->generateContent($prompt);

            $aiReplyText = $result->text();

            $aiReply = AiChat::create([
                'user_id' => $user->id,
                'role'    => 'assistant',
                'message' => $aiReplyText,
                'meta'    => [
                    'source'          => 'gemini',
                    'model'           => 'gemini-3.1-flash-lite',
                    'request_chat_id' => $chat->id,
                ],
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'AI chat berhasil disimpan',
                'data'    => [
                    'user_message' => $chat,
                    'ai_reply'     => $aiReply,
                ],
            ], 201);

        } catch (Throwable $e) {
            $aiReply = AiChat::create([
                'user_id' => $user->id,
                'role'    => 'assistant',
                'message' => 'Maaf, AI sedang tidak bisa merespon. Coba lagi nanti.',
                'meta'    => [
                    'source'          => 'gemini',
                    'error'           => config('app.debug') ? $e->getMessage() : null,
                    'request_chat_id' => $chat->id,
                ],
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'AI chat gagal diproses, tapi pesan user berhasil disimpan',
                'data'    => [
                    'user_message' => $chat,
                    'ai_reply'     => $aiReply,
                ],
            ], 500);
        }
    }

    public function personalizationIndex(Request $request)
    {
        $personalization = AiPersonalization::where('user_id', $request->user()->id)->first();

        return response()->json([
            'status'  => true,
            'message' => 'AI personalization',
            'data'    => $personalization,
        ], 200);
    }

    public function personalizationStore(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'nullable|array',
            'status'      => 'nullable|string|max:50',
        ]);

        $personalization = AiPersonalization::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'preferences' => $validated['preferences'] ?? null,
                'status'      => $validated['status'] ?? 'active',
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'AI personalization berhasil disimpan',
            'data'    => $personalization,
        ], 201);
    }

    public function personalizationDestroy(Request $request)
    {
        AiPersonalization::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'AI personalization berhasil dihapus',
            'data'    => null,
        ], 200);
    }
}
