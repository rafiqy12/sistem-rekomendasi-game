<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GameCoverService
{
    /**
     * Add cover URLs only to games that do not already have one in the dataset.
     */
    public function addCovers(Collection $games): Collection
    {
        return $games->map(function (array $game) {
            if (! empty($game['image_url'])) {
                $game['cover_source'] = 'dataset';

                return $game;
            }

            $game['image_url'] = $this->findCover((string) ($game['title'] ?? '')) ?? '';
            $game['cover_source'] = $game['image_url'] !== '' ? 'catalog' : 'fallback';

            return $game;
        });
    }

    private function findCover(string $title): ?string
    {
        $normalizedTitle = $this->normalize($title);

        if ($normalizedTitle === '' || ! config('services.game_covers.enabled', true)) {
            return null;
        }

        // Increment the key version when the selected image format changes.
        $cacheKey = 'game-cover:v2:'.sha1($normalizedTitle);
        $cached = Cache::get($cacheKey);

        // An empty string is intentionally cached when no cover is found.
        if ($cached !== null) {
            return $cached !== '' ? (string) $cached : null;
        }

        try {
            // Never allow disabled certificate verification in production, even
            // when a copied local .env accidentally contains the workaround.
            $verifySsl = app()->environment('production')
                ? true
                : (bool) config('services.game_covers.verify_ssl', true);

            $response = Http::acceptJson()
                ->withUserAgent((string) config('services.game_covers.user_agent'))
                ->withOptions(['verify' => $verifySsl])
                ->timeout((int) config('services.game_covers.timeout', 3))
                ->get(rtrim((string) config('services.game_covers.url'), '/'), [
                    'title' => $title,
                    'limit' => 8,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $cover = $this->bestCover($response->json(), $normalizedTitle);

            Cache::put(
                $cacheKey,
                $cover ?? '',
                $cover ? now()->addDays(30) : now()->addDay()
            );

            return $cover;
        } catch (Throwable) {
            // Recommendations must still render when the external catalog is down.
            return null;
        }
    }

    private function bestCover(mixed $results, string $wantedTitle): ?string
    {
        if (! is_array($results)) {
            return null;
        }

        $candidates = collect($results)
            ->filter(fn ($item) => is_array($item) && ! empty($item['thumb']))
            ->map(function (array $item) use ($wantedTitle) {
                $candidateTitle = $this->normalize((string) ($item['external'] ?? ''));

                similar_text($wantedTitle, $candidateTitle, $similarity);

                return [
                    'url' => (string) $item['thumb'],
                    'score' => $candidateTitle === $wantedTitle ? 100.0 : $similarity,
                ];
            })
            ->sortByDesc('score')
            ->first();

        if (! $candidates || $candidates['score'] < 72 || ! str_starts_with($candidates['url'], 'https://')) {
            return null;
        }

        return $this->preferLargeSteamImage($candidates['url']);
    }

    private function preferLargeSteamImage(string $url): string
    {
        // CheapShark returns Steam's tiny 231x87 capsule. The matching
        // 616x353 capsule is sharper and has a card-friendly aspect ratio.
        if (str_contains($url, 'steamstatic.com/')) {
            return str_replace('capsule_231x87.', 'capsule_616x353.', $url);
        }

        return $url;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }
}
