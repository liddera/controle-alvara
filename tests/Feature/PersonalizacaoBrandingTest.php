<?php

namespace Tests\Feature;

use App\Models\Personalizacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonalizacaoBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_header_and_sidebar_compact_logos_separately(): void
    {
        config(['filesystems.default' => 'local']);
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('profile.personalization.update'), [
            'header_logo' => UploadedFile::fake()->image('header.png', 400, 100),
            'sidebar_compact_logo' => UploadedFile::fake()->image('compact.png', 180, 180),
            'sidebar_bg_color' => '#111827',
            'sidebar_text_color' => '#ffffff',
            'sidebar_hover_color' => '#1f2937',
        ]);

        $response->assertSessionHasNoErrors();

        $personalizacao = Personalizacao::query()->firstOrFail();

        $this->assertSame('personalizacao/header_logo_' . $user->id . '.png', $personalizacao->header_logo_path);
        $this->assertSame($personalizacao->header_logo_path, $personalizacao->logo_path);
        $this->assertSame('personalizacao/sidebar_compact_logo_' . $user->id . '.png', $personalizacao->sidebar_compact_logo_path);

        Storage::disk('local')->assertExists($personalizacao->header_logo_path);
        Storage::disk('local')->assertExists($personalizacao->sidebar_compact_logo_path);
    }

    public function test_logo_accessors_fallback_to_legacy_logo_path_when_new_paths_are_empty(): void
    {
        config(['filesystems.default' => 'local']);
        Storage::fake('local');

        $user = User::factory()->create();
        $this->actingAs($user);

        $personalizacao = Personalizacao::query()->create([
            'owner_id' => $user->id,
            'logo_path' => 'personalizacao/legacy_logo.png',
        ]);

        $this->assertSame($personalizacao->logo_url, $personalizacao->header_logo_url);
        $this->assertSame($personalizacao->logo_url, $personalizacao->sidebar_compact_logo_url);
    }

    public function test_it_removes_header_logo_without_removing_compact_logo(): void
    {
        config(['filesystems.default' => 'local']);
        Storage::fake('local');

        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::disk('local')->put('personalizacao/header_logo_' . $user->id . '.png', 'header');
        Storage::disk('local')->put('personalizacao/sidebar_compact_logo_' . $user->id . '.png', 'compact');

        $personalizacao = Personalizacao::query()->create([
            'owner_id' => $user->id,
            'header_logo_path' => 'personalizacao/header_logo_' . $user->id . '.png',
            'logo_path' => 'personalizacao/header_logo_' . $user->id . '.png',
            'sidebar_compact_logo_path' => 'personalizacao/sidebar_compact_logo_' . $user->id . '.png',
        ]);

        $response = $this->delete(route('profile.personalization.header-logo.destroy'));

        $response->assertRedirect();

        $personalizacao->refresh();

        $this->assertNull($personalizacao->header_logo_path);
        $this->assertNull($personalizacao->logo_path);
        $this->assertSame('personalizacao/sidebar_compact_logo_' . $user->id . '.png', $personalizacao->sidebar_compact_logo_path);

        Storage::disk('local')->assertMissing('personalizacao/header_logo_' . $user->id . '.png');
        Storage::disk('local')->assertExists('personalizacao/sidebar_compact_logo_' . $user->id . '.png');
    }
}
