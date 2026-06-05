<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChat;
use App\Models\AiPersonalization;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\WorkoutHistory;
use App\Models\ProgressEntry;
use App\Models\Reminder;
use App\Models\Schedule;
use App\Models\Favorite;
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

        // Ambil data user context
        $workoutDates = WorkoutHistory::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->select(DB::raw('DATE(completed_at) as date')) 
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('date');

        $streak = 0;
        if ($workoutDates->isNotEmpty()) {
            $today = now()->startOfDay();
            $hasWorkoutToday = $workoutDates->contains($today->toDateString());
            $dateToCheck = $hasWorkoutToday ? $today : now()->subDay()->startOfDay();
            foreach ($workoutDates as $dateString) {
                $parsedDate = Carbon::parse($dateString)->startOfDay();
                if ($parsedDate->equalTo($dateToCheck)) {
                    $streak++;
                    $dateToCheck->subDay(); 
                } else {
                    break; 
                }
            }
        }

        $progresses = ProgressEntry::where('user_id', $user->id)->latest('measured_at')->take(3)->get();
        $progStrings = [];
        foreach($progresses as $prog) {
            $measuredAt = $prog->measured_at ? Carbon::parse($prog->measured_at)->format('Y-m-d') : 'unknown';
            $progStrings[] = $prog->weight_kg . "kg (" . $measuredAt . ")";
        }
        $progressText = empty($progStrings) ? 'Belum ada' : implode(', ', $progStrings);

        $reminders = Reminder::where('user_id', $user->id)->where('is_sent', false)->where('remind_at', '>=', now())->take(3)->get();
        $remStrings = [];
        foreach($reminders as $rem) {
            $remStrings[] = $rem->title . " (" . Carbon::parse($rem->remind_at)->format('Y-m-d H:i') . ")";
        }
        $reminderText = empty($remStrings) ? 'Tidak ada' : implode(', ', $remStrings);

        $schedules = Schedule::with('workout')->where('user_id', $user->id)->where('schedule_time', '>=', now())->orderBy('schedule_time', 'asc')->take(3)->get();
        $schStrings = [];
        foreach($schedules as $sch) {
            $wName = $sch->workout ? $sch->workout->title : $sch->title;
            $schStrings[] = $wName . " (" . Carbon::parse($sch->schedule_time)->format('Y-m-d H:i') . ")";
        }
        $scheduleText = empty($schStrings) ? 'Tidak ada' : implode(', ', $schStrings);

        $favorites = Favorite::with('workout')->where('user_id', $user->id)->get();
        $favStrings = [];
        foreach($favorites as $fav) {
            if ($fav->workout) {
                $favStrings[] = $fav->workout->title;
            }
        }
        $favoriteText = empty($favStrings) ? 'Tidak ada' : implode(', ', $favStrings);

        $userDataContext = "
Data Pengguna Saat Ini:
- Nama: " . ($user->name ?? 'Belum diset') . "
- Umur: " . ($user->age ?? 'Belum diset') . " tahun
- Berat Badan: " . ($user->weight ?? 'Belum diset') . " kg
- Tinggi Badan: " . ($user->height ?? 'Belum diset') . " cm
- Gender: " . ($user->gender ?? 'Belum diset') . "
- Goal: " . ($user->goal ?? 'Belum diset') . "
- Activity Level: " . ($user->activity_level ?? 'Belum diset') . "
- Target Kalori Harian: " . ($user->daily_calories_target ?? 'Belum diset') . " kcal
- Poin: " . ($user->points ?? 0) . "
- Streak Workout: " . $streak . " hari beruntun
- Riwayat Progress (Berat Badan Terbaru): " . $progressText . "
- Reminder Aktif: " . $reminderText . "
- Jadwal Workout Mendatang: " . $scheduleText . "
- Workout Favorit: " . $favoriteText . "

Perhatikan data pengguna di atas untuk menjawab jika user bertanya tentang progress, streak, jadwal, reminder, favorit, target, poin atau profil mereka secara spesifik. Jika tidak ditanya, tidak perlu disebutkan.
";

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
13. Jika user minta rekomendasi tempat terdekat tapi lokasi belum jelas, minta user tulis lokasi sekarang (kota/area).

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

        $historyChats = AiChat::where('user_id', $user->id)
            ->latest()
            ->take(4)
            ->get()
            ->reverse();

        $historyText = $historyChats
            ->map(fn ($chat) => strtoupper($chat->role) . ': ' . $chat->message)
            ->implode("\n");

        if ($historyText === '') {
            $historyText = 'Tidak ada.';
        }

        $chat = AiChat::create([
            'user_id' => $user->id,
            'role'    => $validated['role'] ?? 'user',
            'message' => $validated['message'],
            'meta'    => $validated['meta'] ?? null,
        ]);

        $youtubeResults = [];
        $mapsResults = [];

        try {
            $prompt = trim($mainPrompt)
                . "\n\n" . trim($userDataContext)
                . "\n\nRiwayat singkat:\n" . $historyText
                . "\n\nUser message:\n" . $validated['message']
                . "\n\nInstruksi output:"
                . "\nBalas dalam JSON murni tanpa markdown."
                . "\nFormat: {\"reply\":\"...\",\"youtube_search\":true|false,\"youtube_query\":\"...\",\"maps_search\":true|false,\"maps_query\":\"...\"}"
                . "\nPilih youtube_search = true hanya jika user minta rekomendasi video atau tutorial."
                . "\nJika youtube_search = false, isi youtube_query dengan string kosong."
                . "\nJika youtube_search = true, buat youtube_query singkat dan relevan (maks 8 kata)."
                . "\nPilih maps_search = true hanya jika user minta rekomendasi tempat atau gym terdekat DAN lokasi user sudah disebutkan di chat."
                . "\nJika maps_search = false, isi maps_query dengan string kosong."
                . "\nJika maps_search = true, buat maps_query singkat dan relevan (maks 8 kata) dan sertakan lokasi."
                . "\nJika lokasi belum disebutkan, maps_search = false dan minta user kirim lokasi (tanyakan sedang ada dikota mana)."
                . "\nJika user hanya membalas nama kota/area (contoh: \"Jakarta\"), anggap itu jawaban lokasi dan set maps_search = true.";

            $result = Gemini::generativeModel(
                model: 'gemini-3.1-flash-lite'
            )->generateContent($prompt);

            $rawText = trim($result->text());
            $aiReplyText = $rawText;

            $decoded = json_decode($rawText, true);
            if (!is_array($decoded)) {
                $jsonStart = strpos($rawText, '{');
                $jsonEnd = strrpos($rawText, '}');

                if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
                    $maybeJson = substr($rawText, $jsonStart, $jsonEnd - $jsonStart + 1);
                    $decoded = json_decode($maybeJson, true);
                }
            }

            if (is_array($decoded)) {
                $aiReplyText = $decoded['reply'] ?? $rawText;
                $shouldSearch = (bool) ($decoded['youtube_search'] ?? false);
                $youtubeQuery = $decoded['youtube_query'] ?? '';
                $shouldMapsSearch = (bool) ($decoded['maps_search'] ?? false);
                $mapsQuery = $decoded['maps_query'] ?? '';

                if ($shouldSearch && is_string($youtubeQuery) && trim($youtubeQuery) !== '') {
                    $youtubeResults = $this->searchYoutube($youtubeQuery);
                }

                if ($shouldMapsSearch && is_string($mapsQuery) && trim($mapsQuery) !== '') {
                    $mapsResults = $this->searchOpenStreetMap($mapsQuery);
                }
            }

            if ($mapsResults === []) {
                $fallbackQuery = $this->inferMapsQueryFromMessage($validated['message'], $historyChats);

                if ($fallbackQuery !== null) {
                    $mapsResults = $this->searchOpenStreetMap($fallbackQuery);
                }
            }

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
                    'youtube_results' => $youtubeResults,
                    'maps_result' => $mapsResults,
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
                    'youtube_results' => $youtubeResults,
                    'maps_result' => $mapsResults,
                ],
            ], 500);
        }
    }

    private function searchYoutube(string $query): array
    {
        $apiKey = config('services.youtube.key');
        $trimmedQuery = trim($query);

        if (!$apiKey || $trimmedQuery === '') {
            return [];
        }

        try {
            $response = Http::timeout(8)->get('https://www.googleapis.com/youtube/v3/search', [
                'part'       => 'snippet',
                'q'          => $trimmedQuery,
                'type'       => 'video',
                'maxResults' => 5,
                'safeSearch' => 'moderate',
                'key'        => $apiKey,
            ]);
        } catch (Throwable $e) {
            return [];
        }

        if (!$response->successful()) {
            return [];
        }

        $items = $response->json('items', []);
        $results = [];

        foreach ($items as $item) {
            $videoId = $item['id']['videoId'] ?? null;

            if (!$videoId) {
                continue;
            }

            $snippet = $item['snippet'] ?? [];
            $thumbnail = $snippet['thumbnails']['medium']['url']
                ?? $snippet['thumbnails']['default']['url']
                ?? null;

            $results[] = [
                'video_id'     => $videoId,
                'title'        => $snippet['title'] ?? null,
                'channel'      => $snippet['channelTitle'] ?? null,
                'thumbnail'    => $thumbnail,
                'published_at' => $snippet['publishedAt'] ?? null,
                'url'          => 'https://www.youtube.com/watch?v=' . $videoId,
            ];
        }

        return $results;
    }

    private function searchOpenStreetMap(string $query): array
    {
        $trimmedQuery = trim($query);

        if ($trimmedQuery === '') {
            return [];
        }

        $locationQuery = $this->extractLocationQuery($trimmedQuery);
        $geo = $this->geocodeLocation($locationQuery !== '' ? $locationQuery : $trimmedQuery);

        if (!$geo) {
            return [];
        }

        $radiusMeters = 3000;
        $gyms = $this->searchOverpassGyms($geo['lat'], $geo['lon'], $radiusMeters);

        if ($gyms === []) {
            return [];
        }

        $locationHint = $locationQuery !== ''
            ? $locationQuery
            : ($geo['label'] ?? '');

        $results = [];

        foreach ($gyms as $gym) {
            $distance = $this->haversineDistance($geo['lat'], $geo['lon'], $gym['lat'], $gym['lon']);
            $name = $gym['name'];
            $queryHint = trim($name . ' ' . $locationHint);

            $results[] = [
                'name' => $name,
                'address' => $this->buildAddress($gym['tags']),
                'distance_m' => $distance !== null ? (int) round($distance) : null,
                'location' => [
                    'lat' => $gym['lat'],
                    'lng' => $gym['lon'],
                ],
                'maps_url' => 'https://www.openstreetmap.org/?mlat=' . $gym['lat'] . '&mlon=' . $gym['lon'] . '#map=16/' . $gym['lat'] . '/' . $gym['lon'],
                'google_search_url' => 'https://www.google.com/search?q=' . urlencode($queryHint),
            ];
        }

        usort($results, fn (array $a, array $b) => ($a['distance_m'] ?? PHP_INT_MAX) <=> ($b['distance_m'] ?? PHP_INT_MAX));

        return array_slice($results, 0, 8);
    }

    private function inferMapsQueryFromMessage(string $message, $historyChats): ?string
    {
        $trimmed = trim($message);

        if ($trimmed === '' || strlen($trimmed) > 60) {
            return null;
        }

        $lastAssistant = $this->getLastAssistantMessage($historyChats);
        $askedLocation = $lastAssistant && preg_match('/\b(lokasi|daerah|kota|di mana|dimana)\b/i', $lastAssistant);

        if (!$askedLocation) {
            return null;
        }

        if (preg_match('/\b(gym|fitness|fitnes)\b/i', $trimmed)) {
            return $trimmed;
        }

        return 'gym ' . $trimmed;
    }

    private function getLastAssistantMessage($historyChats): ?string
    {
        if (!is_object($historyChats) || !method_exists($historyChats, 'filter')) {
            return null;
        }

        $lastAssistant = $historyChats
            ->filter(fn ($chat) => $chat->role === 'assistant')
            ->last();

        if (!$lastAssistant) {
            return null;
        }

        return is_string($lastAssistant->message) ? $lastAssistant->message : null;
    }

    private function extractLocationQuery(string $query): string
    {
        $cleaned = preg_replace('/\b(gym|gym terdekat|fitness|fitnes|dekat|terdekat)\b/i', '', $query);

        if (!is_string($cleaned)) {
            return '';
        }

        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        return $cleaned;
    }

    private function geocodeLocation(string $query): ?array
    {
        $items = $this->queryNominatim($query, 'id');

        if ($items === []) {
            $items = $this->queryNominatim($query, null);
        }

        if ($items === []) {
            return null;
        }

        $item = $this->pickNominatimResult($items, 'id') ?? $items[0];
        $lat = $item['lat'] ?? null;
        $lon = $item['lon'] ?? null;

        if (!is_numeric($lat) || !is_numeric($lon)) {
            return null;
        }

        return [
            'lat' => (float) $lat,
            'lon' => (float) $lon,
            'label' => $item['display_name'] ?? null,
        ];
    }

    private function queryNominatim(string $query, ?string $countryCode): array
    {
        try {
            $params = [
                'q' => $query,
                'format' => 'json',
                'limit' => 5,
                'addressdetails' => 1,
            ];

            if ($countryCode) {
                $params['countrycodes'] = $countryCode;
            }

            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'FitTrack/1.0 (FitTrack Laravel)',
                    'Accept-Language' => 'id',
                ])
                ->get('https://nominatim.openstreetmap.org/search', $params);
        } catch (Throwable $e) {
            return [];
        }

        if (!$response->successful()) {
            return [];
        }

        $items = $response->json();

        return is_array($items) ? $items : [];
    }

    private function pickNominatimResult(array $items, string $countryCode): ?array
    {
        $target = strtolower($countryCode);

        foreach ($items as $item) {
            $address = $item['address'] ?? null;
            $code = is_array($address) ? strtolower((string) ($address['country_code'] ?? '')) : '';

            if ($code === $target) {
                return $item;
            }
        }

        return null;
    }

    private function searchOverpassGyms(float $lat, float $lon, int $radiusMeters): array
    {
        $query = '[out:json][timeout:10];'
            . '(' 
            . 'node["amenity"="gym"](around:' . $radiusMeters . ',' . $lat . ',' . $lon . ');'
            . 'way["amenity"="gym"](around:' . $radiusMeters . ',' . $lat . ',' . $lon . ');'
            . 'relation["amenity"="gym"](around:' . $radiusMeters . ',' . $lat . ',' . $lon . ');'
            . 'node["leisure"="fitness_centre"](around:' . $radiusMeters . ',' . $lat . ',' . $lon . ');'
            . 'way["leisure"="fitness_centre"](around:' . $radiusMeters . ',' . $lat . ',' . $lon . ');'
            . 'relation["leisure"="fitness_centre"](around:' . $radiusMeters . ',' . $lat . ',' . $lon . ');'
            . ');out center tags;';

        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => 'FitTrack/1.0 (FitTrack Laravel)',
                ])
                ->asForm()
                ->post('https://overpass-api.de/api/interpreter', [
                    'data' => $query,
                ]);
        } catch (Throwable $e) {
            return [];
        }

        if (!$response->successful()) {
            return [];
        }

        $payload = $response->json();
        $elements = is_array($payload) ? ($payload['elements'] ?? []) : [];
        $results = [];

        foreach ($elements as $element) {
            $tags = $element['tags'] ?? [];
            $name = $tags['name'] ?? null;
            $itemLat = $element['lat'] ?? ($element['center']['lat'] ?? null);
            $itemLon = $element['lon'] ?? ($element['center']['lon'] ?? null);

            if (!$name || !is_numeric($itemLat) || !is_numeric($itemLon)) {
                continue;
            }

            $results[] = [
                'name' => $name,
                'lat' => (float) $itemLat,
                'lon' => (float) $itemLon,
                'tags' => is_array($tags) ? $tags : [],
            ];
        }

        return $results;
    }

    private function buildAddress(array $tags): ?string
    {
        $full = $tags['addr:full'] ?? null;

        if (is_string($full) && trim($full) !== '') {
            return $full;
        }

        $street = trim((string) ($tags['addr:street'] ?? ''));
        $house = trim((string) ($tags['addr:housenumber'] ?? ''));
        $city = trim((string) ($tags['addr:city'] ?? ''));
        $state = trim((string) ($tags['addr:state'] ?? ''));
        $postcode = trim((string) ($tags['addr:postcode'] ?? ''));

        $line = trim($street . ' ' . $house);
        $parts = array_filter([$line, $city, $state, $postcode]);

        if ($parts === []) {
            return $tags['address'] ?? null;
        }

        return implode(', ', $parts);
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): ?float
    {
        $earthRadius = 6371000;
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
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
