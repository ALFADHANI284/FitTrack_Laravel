# Endpoint API - Workout App

Dokumentasi endpoint API untuk aplikasi workout/gym. Endpoint dibagi berdasarkan fitur utama seperti autentikasi, pengguna, workout class, schedule, history, reminder, progress, favorite, achievement, dan AI.

## Base URL

```txt
/api
```

## Keterangan Auth

| Simbol | Keterangan |
|---|---|
| ❌ | Tidak membutuhkan autentikasi |
| ✅ | Membutuhkan autentikasi user |
| ✅ Admin | Membutuhkan autentikasi dan role admin |

Autentikasi menggunakan token JWT

---

## 1. Autentikasi

| Method | Endpoint | Keterangan | Auth |
|---|---|---|---|
| POST | `/api/auth/register` | Register user baru | ❌ |
| POST | `/api/auth/login` | Login user | ❌ |
| POST | `/api/auth/logout` | Logout user | ✅ |
| POST | `/api/auth/refresh-token` | Refresh JWT token | ✅ |
| GET | `/api/auth/me` | Get current user profile | ✅ |
| PUT | `/api/auth/profile` | Update profile user | ✅ |
| PUT | `/api/auth/password` | Change password | ✅ |
| POST | `/api/auth/forgot-password` | Send reset password email | ❌ |
| POST | `/api/auth/reset-password` | Reset password | ❌ |
| POST | `/api/auth/verify-email` | Verify email | ❌ |

---

## 2. Pengguna

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/users` | Get all users | ✅ Admin |
| GET | `/api/users/:id` | Get detail user | ✅ |
| PUT | `/api/users/:id` | Update user | ✅ |
| DELETE | `/api/users/:id` | Delete user | ✅ |
| POST | `/api/users/upload-avatar` | Upload profile picture | ✅ |

---

## 3. Workout Class

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/workout` | Get all workout classes | ✅ |
| GET | `/api/workout/:id` | Get detail class | ✅ |
| POST | `/api/workout/:id` | Join workout class | ✅ |
| POST | `/api/workout` | Create workout class | ✅ Admin |
| PUT | `/api/workout-classes/:id` | Update workout class | ✅ Admin |
| DELETE | `/api/workout-classes/:id` | Delete workout class | ✅ Admin |

> Catatan: Di dokumen asli ada endpoint `GET /api/workout /:id` dengan spasi. Di file ini dirapikan menjadi `GET /api/workout/:id`.

---

## 4. Workout Schedule

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/workout-schedules` | Get schedules | ✅ |
| GET | `/api/workout-schedules/:id` | Get detail schedule | ✅ |
| POST | `/api/workout-schedules` | Create schedule | ✅ Admin |
| PUT | `/api/workout-schedules/:id` | Update schedule | ✅ Admin |
| DELETE | `/api/workout-schedules/:id` | Delete schedule | ✅ Admin |

---

## 5. Workout History

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/workout-history` | Get history workout user | ✅ |
| GET | `/api/workout-history/:id` | Get detail history | ✅ |
| POST | `/api/workout-history/:id` | Create workout history | ✅ |
| POST | `/api/workout-history` | Save completed workout | ✅ |
| DELETE | `/api/workout-history/:id` | Delete history | ✅ |

---

## 6. Reminder

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/reminders` | Get all reminders | ✅ |
| GET | `/api/reminders/:id` | Get detail reminder | ✅ |
| POST | `/api/reminders` | Create reminder | ✅ |
| PUT | `/api/reminders/:id` | Update reminder | ✅ |
| DELETE | `/api/reminders/:id` | Delete reminder | ✅ |

---

## 7. Progress

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/progress` | Get body progress | ✅ |
| POST | `/api/progress` | Add progress data | ✅ |
| PUT | `/api/progress/:id` | Update progress | ✅ |
| DELETE | `/api/progress/:id` | Delete progress | ✅ |

---

## 8. Favorite

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/favorites` | Get favorite workouts | ✅ |
| POST | `/api/favorites/:workoutId` | Add favorite workout | ✅ |
| DELETE | `/api/favorites/:workoutId` | Remove favorite | ✅ |

---

## 9. Achievement

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/achievements` | Get achievements | ✅ |
| POST | `/api/achievements/claim/:id` | Claim badge | ✅ |

---

## 10. AI

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/ai/chat` | Get AI chat history | ✅ |
| POST | `/api/ai/chat` | Send AI chat | ✅ |
| GET | `/api/ai/personalization` | View AI personalization | ✅ |
| POST | `/api/ai/personalization` | Create AI personalization | ✅ |
| DELETE | `/api/ai/personalization` | Delete AI personalization | ✅ |

---

# Contoh JSON Request & Response

## GET `/workouts`

Fungsi: Ambil semua workout.

### Response

```json
{
  "status": true,
  "message": "List Workout",
  "data": [
    {
      "id": 1,
      "nama_latihan": "Push Up",
      "durasi": 15,
      "kalori": 100,
      "status": "belum"
    },
    {
      "id": 2,
      "nama_latihan": "Jogging",
      "durasi": 30,
      "kalori": 250,
      "status": "selesai"
    }
  ]
}
```

---

## POST `/workouts`

Fungsi: Tambah workout.

### Request

```json
{
  "nama_latihan": "Sit Up",
  "durasi": 10,
  "kalori": 80,
  "status": "belum"
}
```

### Response

```json
{
  "status": true,
  "message": "Workout berhasil ditambahkan",
  "data": {
    "id": 3,
    "nama_latihan": "Sit Up",
    "durasi": 10,
    "kalori": 80,
    "status": "belum"
  }
}
```

---

## PUT `/workouts/1`

Fungsi: Update workout berdasarkan ID.

### Request

```json
{
  "nama_latihan": "Push Up Intens",
  "status": "selesai"
}
```

### Response

```json
{
  "status": true,
  "message": "Workout berhasil diupdate",
  "data": {
    "id": 1,
    "nama_latihan": "Push Up Intens",
    "status": "selesai"
  }
}
```

---

## DELETE `/workouts/{id}`

Fungsi: Hapus workout berdasarkan ID.

### Response

```json
{
  "status": true,
  "message": "Workout berhasil dihapus"
}
```

---

# Data Utama Workout

| Field | Tipe Data | Keterangan |
|---|---|---|
| `id` | integer | ID workout |
| `nama_latihan` | string | Nama latihan |
| `durasi` | integer | Durasi latihan dalam menit |
| `kalori` | integer | Jumlah kalori yang terbakar |
| `status` | string | Status latihan, contoh: `selesai` atau `belum` |
| `tanggal` | date | Tanggal latihan |
