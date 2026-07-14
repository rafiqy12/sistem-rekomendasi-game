<?php

namespace App\Http\Controllers;

use App\Services\GameCoverService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GameRecommendationController extends Controller
{
    public function index(Request $request, GameCoverService $gameCovers)
    {
        $games = $this->loadGames();
        $query = trim(mb_substr((string) $request->input('query', 'action adventure'), 0, 200));
        $topN = max(1, min(10, (int) $request->input('top_n', 5)));
        $threshold = max(0, min(1, (float) $request->input('threshold', 0.6)));

        $result = [
            'games' => $games,
            'query' => $query,
            'topN' => $topN,
            'threshold' => $threshold,
            'recommended' => collect(),
            'matchedGame' => null,
            'precision' => null,
            'sampleCount' => $games->count(),
        ];

        if ($games->isNotEmpty() && $query !== '') {
            $analysis = $this->recommendByUserPreference($games, $query, $topN, $threshold);
            $analysis['recommended'] = $gameCovers->addCovers($analysis['recommended']);
            $result = array_merge($result, $analysis);
        }

        return view('games', $result);
    }

    public function fallbackCover(Request $request)
    {
        $title = trim((string) $request->query('title', 'Game')) ?: 'Game';
        $initials = collect(preg_split('/\s+/', $title) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
        $initials = $initials ?: 'GM';

        $hue = hexdec(substr(sha1(mb_strtolower($title)), 0, 4)) % 360;
        $safeTitle = htmlspecialchars(mb_strimwidth($title, 0, 34, '...'), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $safeInitials = htmlspecialchars($initials, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="960" height="600" viewBox="0 0 960 600">
          <defs>
            <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
              <stop stop-color="hsl({$hue}, 72%, 38%)"/>
              <stop offset="1" stop-color="#0f172a"/>
            </linearGradient>
            <radialGradient id="glow" cx="25%" cy="15%" r="80%">
              <stop stop-color="rgba(255,255,255,.34)"/>
              <stop offset="1" stop-color="rgba(255,255,255,0)"/>
            </radialGradient>
          </defs>
          <rect width="960" height="600" fill="url(#bg)"/>
          <rect width="960" height="600" fill="url(#glow)"/>
          <circle cx="820" cy="110" r="170" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="32"/>
          <text x="64" y="310" fill="rgba(255,255,255,.20)" font-family="Arial, sans-serif" font-size="220" font-weight="900">{$safeInitials}</text>
          <rect x="64" y="442" width="80" height="8" rx="4" fill="#fff"/>
          <text x="64" y="520" fill="#fff" font-family="Arial, sans-serif" font-size="42" font-weight="700">{$safeTitle}</text>
        </svg>
        SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    private function loadGames(): Collection
    {
        $path = base_path('dataset/games.csv');
        if (! is_file($path) || ! is_readable($path)) {
            return collect($this->defaultGames())->unique(fn(array $game) => $this->gameSignature($game))->values();
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return collect($this->defaultGames());
        }

        $headers = null;
        $games = collect();

        while (($row = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(function ($header) {
                    return strtolower(trim((string) $header));
                }, $row);

                continue;
            }

            if (count(array_filter($row, fn($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $assoc[$header] = $row[$index] ?? '';
            }

            $title = (string) ($assoc['title'] ?? $assoc['name'] ?? '');
            if (trim($title) === '') {
                continue;
            }

            $games->push([
                'title' => $title,
                'genres' => (string) ($assoc['genres'] ?? ''),
                'summary' => (string) ($assoc['summary'] ?? $assoc['description'] ?? ''),
                'image_url' => (string) ($assoc['image_url'] ?? $assoc['image'] ?? ''),
            ]);
        }

        fclose($handle);

        $games = $games
            ->unique(fn(array $game) => $this->gameSignature($game))
            ->values();

        return $games->isNotEmpty()
            ? $games
            : collect($this->defaultGames())->unique(fn(array $game) => $this->gameSignature($game))->values();
    }

    private function gameSignature(array $game): string
    {
        return mb_strtolower(trim(($game['title'] ?? '') . '|' . ($game['genres'] ?? '') . '|' . ($game['summary'] ?? '') . '|' . ($game['image_url'] ?? '')));
    }

    private function defaultGames(): array
    {
        return [
            ['title' => 'Hades', 'genres' => 'Action, Roguelike', 'summary' => 'Battle out of hell in a rogue-like dungeon crawler inspired by Greek mythology.', 'image_url' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Hades_video_game_screenshot.jpg'],
            ['title' => 'Dead Cells', 'genres' => 'Action, Metroidvania', 'summary' => 'A roguevania action platformer with fast-paced combat and procedurally generated levels.', 'image_url' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Dead_cells_jumping_May_2022.gif'],
            ['title' => 'Hollow Knight', 'genres' => 'Adventure, Metroidvania', 'summary' => 'Explore a vast ruined kingdom of insects and uncover its mysteries.', 'image_url' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Hollow_Knight_PC_gameplay_screenshot.jpg'],
            ['title' => 'Castlevania: Symphony of the Night', 'genres' => 'Action, Platformer', 'summary' => 'Explore Dracula\'s castle in a classic side-scrolling adventure.', 'image_url' => 'https://commons.wikimedia.org/wiki/Special:FilePath/SotNGameplay.jpg'],
            ['title' => 'The Messenger', 'genres' => 'Action, Platformer', 'summary' => 'A ninja adventure with time-traveling levels and sharp combat.', 'image_url' => 'https://placehold.co/600x900/111827/f8fafc?text=The+Messenger'],
            ['title' => 'Metroid Prime 2: Echoes', 'genres' => 'Action, Adventure', 'summary' => 'A first-person adventure through a hostile alien world.', 'image_url' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Metroid_Prime_2_-_Echoes_-_HUD.png'],
        ];
    }

    private function recommendByUserPreference(Collection $games, string $userPreference, int $topN, float $threshold): array
    {
        // Tokenize the user preference input
        $userTokens = $this->tokenize($userPreference);

        if (empty($userTokens)) {
            return [
                'recommended' => collect(),
                'matchedGame' => null,
                'precision' => null,
            ];
        }

        // Build TF-IDF vectors for all games
        $indexedGames = $games->map(function ($game) {
            $game['content'] = trim($game['genres'] . ' ' . $game['summary']);
            $game['tokens'] = $this->tokenize($game['content']);
            return $game;
        })->values();

        $vectors = $this->buildTfIdfVectors($indexedGames);

        // Create a vector for user preference
        $userVector = $this->createUserPreferenceVector($userTokens, $indexedGames);

        // Score all games based on similarity to user preference
        $scores = [];
        foreach ($indexedGames as $index => $game) {
            $scores[] = [
                'title' => $game['title'],
                'genres' => $game['genres'],
                'summary' => $game['summary'],
                'image_url' => $game['image_url'] ?? '',
                'score' => $this->cosineSimilarity($userVector, $vectors[$index] ?? []),
            ];
        }

        // Sort by score (highest first)
        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
        $recommended = collect(array_slice($scores, 0, $topN));

        // Calculate precision
        $precision = $recommended->count() > 0
            ? $recommended->filter(fn($item) => $item['score'] >= $threshold)->count() / $recommended->count()
            : null;

        return [
            'recommended' => $recommended,
            'matchedGame' => null, // No reference game anymore
            'precision' => $precision,
        ];
    }

    private function createUserPreferenceVector(array $userTokens, Collection $indexedGames): array
    {
        // Get all tokens from all games to calculate document frequency
        $documents = $indexedGames->pluck('tokens')->all();
        $documentCount = count($documents);
        $documentFrequency = [];

        foreach ($documents as $tokens) {
            foreach (array_unique($tokens) as $token) {
                $documentFrequency[$token] = ($documentFrequency[$token] ?? 0) + 1;
            }
        }

        // Create TF-IDF vector for user preference
        $termCounts = array_count_values($userTokens);
        $tokenCount = max(count($userTokens), 1);
        $vector = [];

        foreach ($termCounts as $term => $count) {
            $tf = $count / $tokenCount;
            $idf = log(($documentCount + 1) / (($documentFrequency[$term] ?? 0) + 1)) + 1;
            $vector[$term] = $tf * $idf;
        }

        return $vector;
    }

    private function recommend(Collection $games, string $query, int $topN, float $threshold): array
    {
        $indexedGames = $games->map(function ($game) {
            $game['content'] = trim($game['genres'] . ' ' . $game['summary']);
            $game['tokens'] = $this->tokenize($game['content']);
            return $game;
        })->values();

        $matchIndex = $indexedGames->search(function ($game) use ($query) {
            return stripos($game['title'], $query) !== false;
        });

        if ($matchIndex === false) {
            $matchIndex = $indexedGames->search(function ($game) use ($query) {
                return stripos($game['content'], $query) !== false;
            });
        }

        if ($matchIndex === false) {
            return [
                'recommended' => collect(),
                'matchedGame' => null,
                'precision' => null,
            ];
        }

        $vectors = $this->buildTfIdfVectors($indexedGames);
        $baseVector = $vectors[$matchIndex] ?? [];
        $scores = [];

        foreach ($indexedGames as $index => $game) {
            if ($index === $matchIndex) {
                continue;
            }

            $scores[] = [
                'title' => $game['title'],
                'genres' => $game['genres'],
                'summary' => $game['summary'],
                'image_url' => $game['image_url'] ?? '',
                'score' => $this->cosineSimilarity($baseVector, $vectors[$index] ?? []),
            ];
        }

        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
        $recommended = collect(array_slice($scores, 0, $topN));
        $precision = $recommended->count() > 0
            ? $recommended->filter(fn($item) => $item['score'] >= $threshold)->count() / $recommended->count()
            : null;

        return [
            'recommended' => $recommended,
            'matchedGame' => $indexedGames[$matchIndex],
            'precision' => $precision,
        ];
    }

    private function tokenize(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text) ?? '';
        $text = preg_replace('/\s+/', ' ', $text) ?? '';
        $tokens = array_filter(explode(' ', trim($text)));

        return array_values($tokens);
    }

    private function buildTfIdfVectors(Collection $games): array
    {
        $documents = $games->pluck('tokens')->all();
        $documentCount = count($documents);
        $documentFrequency = [];

        foreach ($documents as $tokens) {
            foreach (array_unique($tokens) as $token) {
                $documentFrequency[$token] = ($documentFrequency[$token] ?? 0) + 1;
            }
        }

        $vectors = [];
        foreach ($documents as $tokens) {
            $termCounts = array_count_values($tokens);
            $tokenCount = max(count($tokens), 1);
            $vector = [];

            foreach ($termCounts as $term => $count) {
                $tf = $count / $tokenCount;
                $idf = log(($documentCount + 1) / (($documentFrequency[$term] ?? 0) + 1)) + 1;
                $vector[$term] = $tf * $idf;
            }

            $vectors[] = $vector;
        }

        return $vectors;
    }

    private function cosineSimilarity(array $left, array $right): float
    {
        $terms = array_unique(array_merge(array_keys($left), array_keys($right)));
        $dotProduct = 0.0;
        $leftMagnitude = 0.0;
        $rightMagnitude = 0.0;

        foreach ($terms as $term) {
            $leftValue = $left[$term] ?? 0.0;
            $rightValue = $right[$term] ?? 0.0;
            $dotProduct += $leftValue * $rightValue;
            $leftMagnitude += $leftValue ** 2;
            $rightMagnitude += $rightValue ** 2;
        }

        if ($leftMagnitude === 0.0 || $rightMagnitude === 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($leftMagnitude) * sqrt($rightMagnitude));
    }
}
