<?php

namespace Tests\Feature;

use App\Services\GameCoverService;
use Illuminate\Support\Facades\Http;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        config(['services.game_covers.enabled' => false]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_recommendations_use_a_catalog_cover_when_dataset_has_no_image(): void
    {
        config(['services.game_covers.enabled' => true]);

        Http::fake([
            'www.cheapshark.com/*' => Http::response([
                ['external' => 'Catalog Test Game', 'thumb' => 'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/123/capsule_231x87.jpg'],
            ]),
        ]);

        $games = app(GameCoverService::class)->addCovers(collect([
            [
                'title' => 'Catalog Test Game',
                'genres' => 'Adventure',
                'summary' => 'A test game.',
                'image_url' => '',
            ],
        ]));

        $this->assertSame(
            'https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/123/capsule_616x353.jpg',
            $games->first()['image_url']
        );
        $this->assertSame('catalog', $games->first()['cover_source']);
    }

    public function test_local_fallback_cover_is_an_svg(): void
    {
        $response = $this->get(route('game.cover.fallback', ['title' => 'Hollow Knight']));

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml; charset=UTF-8')
            ->assertSee('Hollow Knight', false);
    }
}
