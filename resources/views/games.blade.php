<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Rekomendasi Game</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: rgba(255, 255, 255, 0.84);
            --panel-strong: #ffffff;
            --line: rgba(148, 163, 184, 0.18);
            --text: #0f172a;
            --muted: #64748b;
            --accent: #0f172a;
            --accent-soft: #dbeafe;
            --green: #dcfce7;
            --green-text: #166534;
            --amber: #fef3c7;
            --amber-text: #92400e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.12), transparent 35%),
                radial-gradient(circle at right 20%, rgba(14, 165, 233, 0.10), transparent 28%),
                linear-gradient(180deg, #eef4ff 0%, var(--bg) 36%, #eef2ff 100%);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        img {
            display: block;
            max-width: 100%;
        }

        .shell {
            max-width: 1240px;
            margin: 0 auto;
            padding: 28px 18px 48px;
        }

        .hero {
            border-radius: 30px;
            padding: 28px;
            color: white;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92) 45%, rgba(51, 65, 85, 0.9)),
                url('https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1600&q=80') center/cover;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.22);
            position: relative;
            overflow: hidden;
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.05), transparent 35%, rgba(255, 255, 255, 0.02));
            pointer-events: none;
        }

        .hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 24px;
            grid-template-columns: 1.2fr 0.8fr;
            align-items: end;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.9);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 14px 0 12px;
            font-size: clamp(2.2rem, 5vw, 4.4rem);
            line-height: 0.95;
            letter-spacing: -0.05em;
        }

        .hero p {
            margin: 0;
            max-width: 60ch;
            color: rgba(226, 232, 240, 0.96);
            font-size: 1.02rem;
            line-height: 1.7;
        }

        .hero-note {
            display: grid;
            gap: 12px;
            justify-items: stretch;
        }

        .hero-note .mini {
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.14);
            padding: 16px;
        }

        .hero-note .mini strong {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 4px;
        }

        .hero-note .mini span {
            color: rgba(226, 232, 240, 0.8);
            font-size: 0.92rem;
        }

        .stats {
            margin-top: 18px;
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .stat-card,
        .panel,
        .input-card,
        .dataset-card {
            background: var(--panel);
            backdrop-filter: blur(16px);
            border: 1px solid var(--line);
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
            border-radius: 24px;
        }

        .stat-card {
            padding: 18px 20px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .stat-value {
            margin-top: 6px;
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .input-card {
            margin-top: 18px;
            padding: 22px;
        }

        .grid-form {
            display: grid;
            gap: 16px;
            grid-template-columns: 1.6fr 0.5fr 0.5fr 1fr;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field label {
            font-size: 0.92rem;
            font-weight: 700;
            color: #1e293b;
        }

        .field input {
            width: 100%;
            border-radius: 18px;
            border: 1px solid #cbd5e1;
            background: rgba(255, 255, 255, 0.95);
            padding: 14px 16px;
            font-size: 0.98rem;
            color: var(--text);
            outline: none;
        }

        .field input:focus {
            border-color: #111827;
            box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08);
        }

        .file-line {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 18px;
            background: var(--accent);
            color: white;
            padding: 14px 20px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.18);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.22);
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: 1.5rem;
            letter-spacing: -0.03em;
        }

        .section-subtitle {
            margin: 0 0 18px;
            color: var(--muted);
        }

        .panel {
            padding: 22px;
        }

        .hero-card {
            display: grid;
            gap: 18px;
            grid-template-columns: 220px 1fr;
            align-items: stretch;
        }

        .cover {
            width: 100%;
            aspect-ratio: 3 / 4;
            border-radius: 22px;
            object-fit: cover;
            background: linear-gradient(135deg, #111827, #334155);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .cover-fallback {
            width: 100%;
            aspect-ratio: 3 / 4;
            border-radius: 22px;
            display: flex;
            align-items: end;
            padding: 18px;
            color: white;
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: -0.05em;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.15), rgba(15, 23, 42, 0.9)),
                radial-gradient(circle at 30% 20%, rgba(59, 130, 246, 0.55), transparent 42%),
                linear-gradient(135deg, #111827, #1f2937);
        }

        .pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0 0;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 800;
            background: var(--accent-soft);
            color: #1d4ed8;
        }

        .pill-green {
            background: var(--green);
            color: var(--green-text);
        }

        .pill-amber {
            background: var(--amber);
            color: var(--amber-text);
        }

        .featured-title {
            margin: 0;
            font-size: 2rem;
            line-height: 1.1;
            letter-spacing: -0.04em;
        }

        .featured-meta {
            margin: 8px 0 0;
            color: var(--muted);
        }

        .featured-summary {
            margin: 14px 0 0;
            line-height: 1.75;
            color: #334155;
        }

        .pair-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .recommend-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .game-card {
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 22px;
            border: 1px solid var(--line);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
        }

        .game-card figure {
            margin: 0;
            position: relative;
            aspect-ratio: 616 / 353;
            overflow: hidden;
            background: linear-gradient(135deg, #111827, #334155);
        }

        .game-card figure img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            background: #0f172a;
        }

        .game-card .content {
            padding: 16px;
        }

        .game-card h3 {
            margin: 0;
            font-size: 1.18rem;
            letter-spacing: -0.03em;
        }

        .game-card p {
            margin: 8px 0 0;
            color: #475569;
            line-height: 1.6;
        }

        .rating-row {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .score {
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .dataset-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .dataset-card th,
        .dataset-card td {
            text-align: left;
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .dataset-card th {
            color: #334155;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .muted {
            color: var(--muted);
        }

        .status {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .status.ok {
            background: var(--green);
            color: var(--green-text);
        }

        .status.no {
            background: var(--amber);
            color: var(--amber-text);
        }

        @media (max-width: 980px) {

            .hero-grid,
            .grid-form,
            .hero-card,
            .pair-grid,
            .recommend-grid,
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <section class="hero">
            <div class="hero-grid">
                <div>
                    <div class="eyebrow">Laravel Recommender</div>
                    <h1>Sistem Rekomendasi Game</h1>
                    <p>
                        Dataset dibaca otomatis dari folder <strong>dataset</strong>. Jika CSV tidak memiliki kolom
                        <strong>image_url</strong>, aplikasi akan mencari cover berdasarkan judul game dan menyimpan
                        hasilnya di cache. Poster lokal tetap tersedia saat cover tidak ditemukan.
                    </p>
                </div>
                <div class="hero-note">
                    <div class="mini">
                        <strong>{{ $topN }}</strong>
                        <span>rekomendasi teratas yang ditampilkan</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats">
            <div class="stat-card">
                <div class="stat-label">Total game</div>
                <div class="stat-value">{{ $sampleCount }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Top rekomendasi</div>
                <div class="stat-value">{{ $topN }}</div>
            </div>
        </section>

        <section class="input-card">
            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid-form">
                    <div class="field">
                        <label for="query">Preferensi / Keywords</label>
                        <input id="query" type="text" name="query" value="{{ old('query', $query) }}" placeholder="Contoh: action adventure roguelike">
                    </div>
                    <div class="field">
                        <label for="top_n">Jumlah</label>
                        <input id="top_n" type="number" name="top_n" min="1" max="10" value="{{ old('top_n', $topN) }}">
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; gap:16px; align-items:center; flex-wrap:wrap; margin-top:16px;">
                    <p class="muted" style="margin:0;">
                        Kolom yang disarankan: Title, Genres, Summary, image_url.
                    </p>
                    <button type="submit" class="button">Hitung Rekomendasi</button>
                </div>
            </form>
        </section>

        <section class="section">
            <h2 class="section-title">Kriteria pencarian</h2>
            <p class="section-subtitle">Rekomendasi berdasarkan preferensi dan keywords Anda.</p>

            <div class="panel">
                @if ($query)
                <div style="padding: 12px; background: rgba(59, 130, 246, 0.1); border-radius: 16px; border: 1px solid rgba(59, 130, 246, 0.3);">
                    <div class="pill-row">
                        <span class="pill">Keywords</span>
                    </div>
                    <p style="margin: 12px 0 0; font-size: 1.1rem; font-weight: 600; color: #0f172a;">
                        {{ $query }}
                    </p>
                </div>
                @else
                <p class="muted" style="margin:0;">Masukkan preferensi atau keywords untuk melihat rekomendasi game.</p>
                @endif
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Hasil rekomendasi</h2>
            <p class="section-subtitle">Diurutkan dari skor kecocokan tertinggi.</p>

            <div class="recommend-grid">
                @forelse ($recommended as $item)
                @php
                $fallbackUrl = route('game.cover.fallback', ['title' => $item['title']]);
                $coverUrl = !empty($item['image_url']) ? $item['image_url'] : $fallbackUrl;
                @endphp
                <article class="game-card">
                    <figure>
                        <img src="{{ $coverUrl }}"
                            alt="Cover {{ $item['title'] }}"
                            loading="lazy"
                            referrerpolicy="no-referrer"
                            onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';">
                    </figure>
                    <div class="content">
                        <h3>{{ $item['title'] }}</h3>
                        <p>{{ $item['summary'] }}</p>
                        <div class="rating-row">
                            <div>
                                <div class="muted" style="font-size:0.8rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em;">Genre</div>
                                <div style="font-weight:700;">{{ $item['genres'] }}</div>
                            </div>
                        </div>
                    </div>
                </article>
                @empty
                <div class="panel" style="grid-column: 1 / -1;">
                    <p class="muted" style="margin:0;">Belum ada hasil. Masukkan preferensi atau keywords game yang Anda cari.</p>
                </div>
                @endforelse
            </div>
        </section>

    </div>
</body>

</html>
