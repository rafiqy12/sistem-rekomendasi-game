# Sistem Rekomendasi Game

## Menggunakan SBERT dan Cosine Similarity

Sistem rekomendasi game berbasis AI yang menggunakan **SBERT (Sentence-BERT)** untuk mengubah deskripsi game menjadi vektor embeddings dan **Cosine Similarity** untuk menemukan game yang paling mirip secara semantik.

---

## 🚀 Fitur

-   ✨ **Rekomendasi Berbasis Teks**: Masukkan deskripsi game yang Anda inginkan dan dapatkan rekomendasi
-   🎮 **Rekomendasi Berbasis Game**: Klik pada game untuk mendapatkan game serupa
-   🧠 **AI-Powered**: Menggunakan Sentence-BERT untuk pemahaman semantik
-   📊 **Skor Similarity**: Melihat seberapa mirip setiap rekomendasi dengan query Anda
-   🎨 **UI Modern**: Antarmuka yang indah dan responsif

---

## 📋 Prerequisites

Pastikan Anda telah menginstall:

-   **Python 3.8 atau lebih tinggi**
-   **pip** (Python package manager)

---

## 🛠️ Instalasi

### 1. Clone atau Download Project

Pastikan Anda berada di direktori project:

```bash
cd d:\laragon\www\sistemrekomendasi
```

### 2. Buat Virtual Environment (Opsional tapi Disarankan)

**Windows:**

```bash
python -m venv venv
venv\Scripts\activate
```

**Linux/Mac:**

```bash
python3 -m venv venv
source venv/bin/activate
```

### 3. Install Dependencies

```bash
pip install -r requirements.txt
```

**Catatan:** Proses instalasi mungkin memakan waktu karena mengunduh model SBERT (~420MB). Pastikan koneksi internet stabil.

---

## 🎯 Cara Menjalankan

### 1. Jalankan Backend Flask

```bash
python app.py
```

Server akan berjalan di: `http://localhost:5000`

**Output yang diharapkan:**

```
Loading SBERT model...
Model loaded successfully!
Computing game embeddings...
Computed embeddings for 12 games
 * Running on http://0.0.0.0:5000
```

### 2. Buka Browser

Buka browser dan akses:

```
http://localhost:5000
```

---

## 💡 Cara Menggunakan

### Metode 1: Rekomendasi Berdasarkan Deskripsi

1. Ketik deskripsi game yang Anda inginkan di kotak teks
    - Contoh: "game RPG fantasi dengan cerita menarik"
    - Contoh: "game santai untuk relaksasi"
    - Contoh: "game petualangan dengan grafik indah"
2. Klik tombol **"🔍 Cari Rekomendasi"**
3. Sistem akan menampilkan 5 game yang paling sesuai dengan skor similarity

### Metode 2: Rekomendasi Berdasarkan Game

1. Scroll ke bagian **"Semua Game di Database"**
2. Klik pada card game yang Anda suka
3. Sistem akan menampilkan 5 game yang mirip dengan game tersebut

---

## 🔧 Struktur Project

```
sistemrekomendasi/
│
├── app.py                    # Backend Flask dengan SBERT
├── requirements.txt          # Dependencies Python
├── README.md                 # Dokumentasi ini
│
├── templates/
│   └── index.html           # Frontend HTML
│
└── static/
    ├── style.css            # Styling CSS
    └── script.js            # JavaScript logic
```

---

## 🧠 Teknologi yang Digunakan

### Backend:

-   **Flask**: Web framework Python
-   **Sentence-Transformers**: Library SBERT untuk text embeddings
-   **scikit-learn**: Untuk menghitung cosine similarity
-   **NumPy**: Operasi array dan matematika

### Frontend:

-   **HTML5**: Struktur halaman
-   **CSS3**: Styling modern dengan gradients dan animations
-   **JavaScript (Vanilla)**: Interaksi dengan API

### Machine Learning:

-   **SBERT (paraphrase-MiniLM-L6-v2)**: Model untuk mengubah teks menjadi embeddings 384-dimensional
-   **Cosine Similarity**: Mengukur kesamaan antara dua vektor embeddings

---

## 📊 Cara Kerja Sistem

1. **Pre-processing**:

    - Setiap game digabungkan menjadi satu teks: `title + genre + description`
    - Model SBERT mengkonversi teks menjadi vektor 384-dimensional

2. **Query Processing**:

    - User query atau game yang dipilih dikonversi menjadi vektor embedding

3. **Similarity Calculation**:

    - Cosine similarity dihitung antara query embedding dan semua game embeddings
    - Formula: `similarity = (A · B) / (||A|| * ||B||)`

4. **Ranking**:
    - Game diurutkan berdasarkan skor similarity (0-1)
    - Top 5 game dengan skor tertinggi ditampilkan

---

## 🎮 Game di Database

Sistem saat ini memiliki 12 game:

1. The Witcher 3: Wild Hunt
2. Dark Souls 3
3. Stardew Valley
4. Minecraft
5. Elden Ring
6. Red Dead Redemption 2
7. God of War
8. Animal Crossing: New Horizons
9. Terraria
10. Horizon Zero Dawn
11. Cyberpunk 2077
12. Portal 2

---

## 🔄 Menambah Game Baru

Edit file `app.py` pada bagian `games_data` dan tambahkan game baru:

```python
{
    "id": 13,
    "title": "Nama Game",
    "genre": "Genre1, Genre2",
    "description": "Deskripsi lengkap game...",
    "platform": "PC, PS5",
    "year": 2024
}
```

Restart server untuk menerapkan perubahan.

---

## 🐛 Troubleshooting

### Error: Model tidak bisa diunduh

-   Pastikan koneksi internet stabil
-   Coba jalankan manual: `python -c "from sentence_transformers import SentenceTransformer; SentenceTransformer('paraphrase-MiniLM-L6-v2')"`

### Error: Port 5000 sudah digunakan

-   Ganti port di `app.py`: `app.run(debug=True, host='0.0.0.0', port=5001)`
-   Update juga di `static/script.js`: `const API_URL = 'http://localhost:5001';`

### Frontend tidak bisa connect ke backend

-   Pastikan Flask server berjalan
-   Cek console browser (F12) untuk error CORS
-   Pastikan `flask-cors` terinstall

---

## 📝 API Endpoints

### GET `/api/games`

Mendapatkan semua game

```json
[
  {
    "id": 1,
    "title": "The Witcher 3",
    ...
  }
]
```

### POST `/api/recommend`

Mendapatkan rekomendasi

```json
// Request
{
  "query": "game RPG fantasi",  // atau
  "game_id": 1,
  "top_n": 5
}

// Response
{
  "query": "game RPG fantasi",
  "recommendations": [...]
}
```

### POST `/api/search`

Mencari game berdasarkan similarity

```json
// Request
{
  "query": "sandbox game"
}

// Response
{
  "query": "sandbox game",
  "results": [...]  // semua game dengan skor
}
```

---

## 📚 Referensi

-   [Sentence-BERT Paper](https://arxiv.org/abs/1908.10084)
-   [Sentence-Transformers Documentation](https://www.sbert.net/)
-   [Cosine Similarity](https://en.wikipedia.org/wiki/Cosine_similarity)

---

## 📄 Lisensi

Project ini dibuat untuk tujuan edukasi dan pembelajaran.

---

## 👨‍💻 Author

Dibuat dengan ❤️ menggunakan Python, Flask, dan SBERT

---

**Selamat Mencoba! 🎮🚀**
