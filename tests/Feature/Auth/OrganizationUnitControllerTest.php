<?php

namespace Tests\Feature\Auth;

use App\Models\OrganizationUnit;
use App\Models\User;
use Tests\TestCase;

class OrganizationUnitControllerTest extends TestCase
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

    // ─── Store ───────────────────────────────────────────────

    public function test_store_creates_organization_unit(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/organization-units', [
                'unit_path' => 'Engineering',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', 'Organization unit created successfully.');
        $this->assertDatabaseHas('organization_units', ['unit_path' => 'Engineering']);
    }

    public function test_store_fails_without_unit_path(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/organization-units', [])
            ->assertSessionHasErrors('unit_path');
    }

    public function test_store_fails_with_duplicate_unit_path(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/organization-units', [
                'unit_path' => $this->orgUnit->unit_path,
            ])
            ->assertSessionHasErrors('unit_path');
    }

    public function test_store_fails_with_too_long_unit_path(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/organization-units', [
                'unit_path' => str_repeat('A', 51),
            ])
            ->assertSessionHasErrors('unit_path');
    }

    public function test_store_accepts_max_length_unit_path(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/organization-units', [
                'unit_path' => str_repeat('A', 50),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('organization_units', ['unit_path' => str_repeat('A', 50)]);
    }

    public function test_store_requires_admin_role(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $this->actingAs($approver)
            ->post('/admin/organization-units', ['unit_path' => 'New Unit'])
            ->assertRedirect(route('404'));
    }

    public function test_store_requires_authentication(): void
    {
        $this->post('/admin/organization-units', ['unit_path' => 'New Unit'])
            ->assertRedirect(route('login'));
    }

    // ─── Update ──────────────────────────────────────────────

    public function test_update_changes_unit_path(): void
    {
        $response = $this->actingAs($this->admin)
            ->put("/admin/organization-units/{$this->orgUnit->id}", [
                'unit_path' => 'Renamed Unit',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', 'Organization unit updated successfully.');
        $this->assertSame('Renamed Unit', $this->orgUnit->fresh()->unit_path);
    }

    public function test_update_fails_without_unit_path(): void
    {
        $this->actingAs($this->admin)
            ->put("/admin/organization-units/{$this->orgUnit->id}", [])
            ->assertSessionHasErrors('unit_path');
    }

    public function test_update_fails_with_duplicate_unit_path(): void
    {
        $other = OrganizationUnit::factory()->create(['unit_path' => 'Other Unit']);

        $this->actingAs($this->admin)
            ->put("/admin/organization-units/{$this->orgUnit->id}", [
                'unit_path' => $other->unit_path,
            ])
            ->assertSessionHasErrors('unit_path');
    }

    public function test_update_allows_same_unit_path(): void
    {
        $this->actingAs($this->admin)
            ->put("/admin/organization-units/{$this->orgUnit->id}", [
                'unit_path' => $this->orgUnit->unit_path,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_update_fails_with_too_long_unit_path(): void
    {
        $this->actingAs($this->admin)
            ->put("/admin/organization-units/{$this->orgUnit->id}", [
                'unit_path' => str_repeat('A', 51),
            ])
            ->assertSessionHasErrors('unit_path');
    }

    public function test_update_requires_admin_role(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $this->actingAs($approver)
            ->put("/admin/organization-units/{$this->orgUnit->id}", ['unit_path' => 'Renamed'])
            ->assertRedirect(route('404'));
    }

    // ─── Destroy ─────────────────────────────────────────────

    public function test_destroy_deletes_and_reassigns_users(): void
    {
        $target = OrganizationUnit::factory()->create(['unit_path' => 'Target Unit']);
        $fallback = OrganizationUnit::factory()->create(['unit_path' => 'Fallback Unit']);

        $user1 = User::factory()->create([
            'organization_unit_id' => $target->id,
            'role' => 'employee',
        ]);
        $user2 = User::factory()->create([
            'organization_unit_id' => $target->id,
            'role' => 'employee',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/organization-units/{$target->id}", [
                'reassign_to' => $fallback->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message');
        $this->assertDatabaseMissing('organization_units', ['id' => $target->id]);
        $this->assertSame($fallback->id, $user1->fresh()->organization_unit_id);
        $this->assertSame($fallback->id, $user2->fresh()->organization_unit_id);
    }

    public function test_destroy_deletes_required_hours_for_unit(): void
    {
        $target = OrganizationUnit::factory()->create(['unit_path' => 'Target Unit']);
        $fallback = OrganizationUnit::factory()->create(['unit_path' => 'Fallback Unit']);

        \App\Models\RequiredHours::create([
            'year' => 2026,
            'week' => 10,
            'required_hours' => 40,
            'organization_unit_id' => $target->id,
        ]);

        $this->actingAs($this->admin)
            ->delete("/admin/organization-units/{$target->id}", [
                'reassign_to' => $fallback->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('required_hours', ['organization_unit_id' => $target->id]);
    }

    public function test_destroy_fails_without_reassign_to(): void
    {
        $target = OrganizationUnit::factory()->create(['unit_path' => 'Target Unit']);

        $this->actingAs($this->admin)
            ->delete("/admin/organization-units/{$target->id}")
            ->assertSessionHasErrors('reassign_to');
    }

    public function test_destroy_fails_with_nonexistent_reassign_to(): void
    {
        $target = OrganizationUnit::factory()->create(['unit_path' => 'Target Unit']);

        $this->actingAs($this->admin)
            ->delete("/admin/organization-units/{$target->id}", [
                'reassign_to' => 999999,
            ])
            ->assertSessionHasErrors('reassign_to');
    }

    public function test_destroy_fails_when_reassign_to_same_unit(): void
    {
        $target = OrganizationUnit::factory()->create(['unit_path' => 'Target Unit']);

        $this->actingAs($this->admin)
            ->delete("/admin/organization-units/{$target->id}", [
                'reassign_to' => $target->id,
            ])
            ->assertSessionHasErrors('reassign_to');

        $this->assertDatabaseHas('organization_units', ['id' => $target->id]);
    }

    public function test_destroy_with_no_users_still_deletes(): void
    {
        $target = OrganizationUnit::factory()->create(['unit_path' => 'Target Unit']);
        $fallback = OrganizationUnit::factory()->create(['unit_path' => 'Fallback Unit']);

        $this->actingAs($this->admin)
            ->delete("/admin/organization-units/{$target->id}", [
                'reassign_to' => $fallback->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('organization_units', ['id' => $target->id]);
    }

    public function test_destroy_requires_admin_role(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);
        $target = OrganizationUnit::factory()->create(['unit_path' => 'Target Unit']);
        $fallback = OrganizationUnit::factory()->create(['unit_path' => 'Fallback Unit']);

        $this->actingAs($approver)
            ->delete("/admin/organization-units/{$target->id}", [
                'reassign_to' => $fallback->id,
            ])
            ->assertRedirect(route('404'));
    }

    public function test_destroy_requires_authentication(): void
    {
        $target = OrganizationUnit::factory()->create(['unit_path' => 'Target Unit']);
        $fallback = OrganizationUnit::factory()->create(['unit_path' => 'Fallback Unit']);

        $this->delete("/admin/organization-units/{$target->id}", [
            'reassign_to' => $fallback->id,
        ])
            ->assertRedirect(route('login'));
    }
}
