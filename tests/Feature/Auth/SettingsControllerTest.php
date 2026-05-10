<?php

namespace Tests\Feature\Auth;

use App\Models\OrganizationUnit;
use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    private User $admin;
    private OrganizationUnit $orgUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
        $this->admin = User::factory()->create([
            'organization_unit_id' => null,
            'role' => 'admin',
        ]);
    }

    private function validPayload(): array
    {
        return [
            'default_shift_codes' => [
                ['day' => 'Sunday', 'code' => 'AA'],
                ['day' => 'Monday', 'code' => 'BB'],
                ['day' => 'Tuesday', 'code' => 'CC'],
                ['day' => 'Wednesday', 'code' => 'DD'],
                ['day' => 'Thursday', 'code' => 'EE'],
                ['day' => 'Friday', 'code' => 'FF'],
                ['day' => 'Saturday', 'code' => 'GG'],
            ],
            'minimum_overtime_hours' => 1.0,
            'overtime_minute_step' => 15,
        ];
    }

    // ─── Index ───────────────────────────────────────────────

    public function test_settings_index_returns_inertia_page(): void
    {
        Setting::set('minimum_overtime_hours', 1);
        Setting::set('overtime_minute_step', 15);

        $response = $this->actingAs($this->admin)->get('/admin/settings');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Admin/Settings')
                ->has('settings')
                ->has('organization_units');
        });
    }

    public function test_settings_index_returns_empty_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Admin/Settings')
                ->where('settings', [])
                ->has('organization_units');
        });
    }

    public function test_settings_index_includes_organization_units_ordered(): void
    {
        OrganizationUnit::factory()->create(['unit_path' => 'Zebra Team']);
        OrganizationUnit::factory()->create(['unit_path' => 'Alpha Team']);

        $response = $this->actingAs($this->admin)->get('/admin/settings');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Admin/Settings')
                ->has('organization_units', 3);
        });

        $props = $response->inertiaProps();
        $paths = collect($props['organization_units'])->pluck('unit_path')->toArray();
        $sorted = collect($paths)->sort()->values()->toArray();
        $this->assertSame($sorted, $paths, 'Organization units should be ordered by unit_path.');
    }

    public function test_settings_index_requires_admin_role(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $this->actingAs($approver)->get('/admin/settings')
            ->assertRedirect(route('404'));
    }

    public function test_settings_index_requires_authentication(): void
    {
        $this->get('/admin/settings')
            ->assertRedirect(route('login'));
    }

    // ─── Update ──────────────────────────────────────────────

    public function test_update_settings_successfully(): void
    {
        $response = $this->actingAs($this->admin)
            ->put('/admin/settings', $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', 'Settings updated successfully.');

        $this->assertNotNull(Setting::get('minimum_overtime_hours'));
        $this->assertNotNull(Setting::get('overtime_minute_step'));
        $this->assertNotNull(Setting::get('default_shift_codes'));
    }

    public function test_update_settings_persists_minimum_overtime_hours(): void
    {
        $payload = $this->validPayload();
        $payload['minimum_overtime_hours'] = 2.5;

        $this->actingAs($this->admin)->put('/admin/settings', $payload);

        $this->assertSame(2.5, Setting::get('minimum_overtime_hours'));
    }

    public function test_update_settings_persists_overtime_minute_step(): void
    {
        $payload = $this->validPayload();
        $payload['overtime_minute_step'] = 30;

        $this->actingAs($this->admin)->put('/admin/settings', $payload);

        $this->assertSame(30, Setting::get('overtime_minute_step'));
    }

    public function test_update_settings_persists_default_shift_codes(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', $this->validPayload());

        $codes = Setting::get('default_shift_codes');
        $this->assertCount(7, $codes);
        $this->assertSame('AA', $codes[0]['code']);
        $this->assertSame('GG', $codes[6]['code']);
    }

    public function test_update_settings_overwrites_existing(): void
    {
        Setting::set('minimum_overtime_hours', 1.0);
        Setting::set('overtime_minute_step', 15);

        $payload = $this->validPayload();
        $payload['minimum_overtime_hours'] = 0.75;
        $payload['overtime_minute_step'] = 5;

        $this->actingAs($this->admin)->put('/admin/settings', $payload);

        $this->assertSame(0.75, Setting::get('minimum_overtime_hours'));
        $this->assertSame(5, Setting::get('overtime_minute_step'));
    }

    // ─── Validation: default_shift_codes ─────────────────────

    public function test_update_fails_without_default_shift_codes(): void
    {
        $payload = $this->validPayload();
        unset($payload['default_shift_codes']);

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('default_shift_codes');
    }

    public function test_update_fails_with_wrong_size_shift_codes(): void
    {
        $payload = $this->validPayload();
        $payload['default_shift_codes'] = [
            ['day' => 'Sunday', 'code' => 'AA'],
        ];

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('default_shift_codes');
    }

    public function test_update_fails_with_missing_day_in_shift_codes(): void
    {
        $payload = $this->validPayload();
        $payload['default_shift_codes'][0] = ['code' => 'AA'];

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('default_shift_codes.0.day');
    }

    public function test_update_fails_with_missing_code_in_shift_codes(): void
    {
        $payload = $this->validPayload();
        $payload['default_shift_codes'][0] = ['day' => 'Sunday'];

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('default_shift_codes.0.code');
    }

    // ─── Validation: minimum_overtime_hours ──────────────────

    public function test_update_fails_without_minimum_overtime_hours(): void
    {
        $payload = $this->validPayload();
        unset($payload['minimum_overtime_hours']);

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('minimum_overtime_hours');
    }

    public function test_update_fails_with_non_numeric_minimum_hours(): void
    {
        $payload = $this->validPayload();
        $payload['minimum_overtime_hours'] = 'abc';

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('minimum_overtime_hours');
    }

    public function test_update_fails_with_zero_minimum_hours(): void
    {
        $payload = $this->validPayload();
        $payload['minimum_overtime_hours'] = 0;

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('minimum_overtime_hours');
    }

    public function test_update_fails_with_negative_minimum_hours(): void
    {
        $payload = $this->validPayload();
        $payload['minimum_overtime_hours'] = -1;

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('minimum_overtime_hours');
    }

    public function test_update_fails_with_non_quarter_increment(): void
    {
        $payload = $this->validPayload();
        $payload['minimum_overtime_hours'] = 0.33;

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('minimum_overtime_hours');
    }

    public function test_update_accepts_valid_quarter_increments(): void
    {
        foreach ([0.25, 0.50, 0.75, 1.00, 1.25, 2.00] as $value) {
            $payload = $this->validPayload();
            $payload['minimum_overtime_hours'] = $value;

            $this->actingAs($this->admin)->put('/admin/settings', $payload)
                ->assertSessionHasNoErrors();
        }
    }

    // ─── Validation: overtime_minute_step ────────────────────

    public function test_update_fails_without_overtime_minute_step(): void
    {
        $payload = $this->validPayload();
        unset($payload['overtime_minute_step']);

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('overtime_minute_step');
    }

    public function test_update_fails_with_invalid_minute_step(): void
    {
        $payload = $this->validPayload();
        $payload['overtime_minute_step'] = 7;

        $this->actingAs($this->admin)->put('/admin/settings', $payload)
            ->assertSessionHasErrors('overtime_minute_step');
    }

    public function test_update_accepts_all_valid_minute_steps(): void
    {
        foreach ([1, 5, 10, 15, 30] as $step) {
            $payload = $this->validPayload();
            $payload['overtime_minute_step'] = $step;

            $this->actingAs($this->admin)->put('/admin/settings', $payload)
                ->assertSessionHasNoErrors();
        }
    }

    // ─── Authorization ───────────────────────────────────────

    public function test_update_requires_admin_role(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $this->actingAs($approver)->put('/admin/settings', $this->validPayload())
            ->assertRedirect(route('404'));
    }

    public function test_update_requires_authentication(): void
    {
        $this->put('/admin/settings', $this->validPayload())
            ->assertRedirect(route('login'));
    }
}
