<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalPortalAndNamedCodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_professional_portal_with_logo_and_slfa_code(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('logo/sl-logo.png')
            ->assertSee('SLFA-XXXX-XXXX')
            ->assertSee('Assessment ini bukan psikotes klinis')
            ->assertDontSee('Build phases')
            ->assertDontSee('Roadmap')
            ->assertDontSee('MVP scope')
            ->assertDontSee('Phase 0');
    }

    public function test_old_cfa_codes_still_validate(): void
    {
        $displayCode = 'CFA-LEGACY-CODE';

        AccessCode::create([
            'code_hash' => hash('sha256', $displayCode),
            'display_code' => $displayCode,
            'status' => AccessCode::STATUS_UNUSED,
        ]);

        $this->post(route('code.validate'), [
            'access_code' => $displayCode,
        ])->assertRedirect(route('assessment.instructions'));
    }
}
