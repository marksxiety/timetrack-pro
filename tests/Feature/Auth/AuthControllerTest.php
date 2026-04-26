<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private OrganizationUnit $orgUnit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orgUnit = OrganizationUnit::factory()->create(['unit_path' => 'Test Unit']);
    }

    // ─── Register ─────────────────────────────────────────────

    public function test_register_creates_user_and_redirects(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'employeeid' => 'EMP100',
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('main'));
        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'employeeid' => 'EMP100']);
    }

    public function test_register_fails_without_name(): void
    {
        $response = $this->post('/register', [
            'employeeid' => 'EMP100',
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'employeeid' => 'EMP100',
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_register_fails_with_duplicate_employeeid(): void
    {
        User::factory()->create(['employeeid' => 'EMP001']);

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'employeeid' => 'EMP001',
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['employeeid']);
    }

    public function test_register_fails_with_invalid_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'employeeid' => 'EMP100',
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'superadmin',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['role']);
    }

    public function test_register_fails_with_short_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'employeeid' => 'EMP100',
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_register_fails_with_unconfirmed_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'employeeid' => 'EMP100',
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    // ─── Login ───────────────────────────────────────────────

    public function test_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('main'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_fails_with_non_existent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    // ─── Logout ──────────────────────────────────────────────

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create(['organization_unit_id' => $this->orgUnit->id]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ─── Update Profile Information ──────────────────────────

    public function test_update_profile_updates_name_and_email(): void
    {
        $user = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->post('/profile/update/employee', [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'active' => true,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('New Name', $user->fresh()->name);
        $this->assertEquals('new@example.com', $user->fresh()->email);
    }

    public function test_update_profile_fails_with_old_password_mismatch(): void
    {
        $user = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'password' => bcrypt('current-password'),
        ]);

        $response = $this->actingAs($user)->post('/profile/update/employee', [
            'name' => $user->name,
            'email' => $user->email,
            'active' => true,
            'old_password' => 'wrong-password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['old_password']);
    }

    public function test_update_profile_fails_when_new_password_same_as_old(): void
    {
        $user = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'password' => bcrypt('samepassword'),
        ]);

        $response = $this->actingAs($user)->post('/profile/update/employee', [
            'name' => $user->name,
            'email' => $user->email,
            'active' => true,
            'old_password' => 'samepassword',
            'new_password' => 'samepassword',
            'new_password_confirmation' => 'samepassword',
        ]);

        $response->assertSessionHasErrors(['new_password']);
    }

    public function test_update_profile_fails_with_duplicate_email(): void
    {
        $user = User::factory()->create(['organization_unit_id' => $this->orgUnit->id]);
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->post('/profile/update/employee', [
            'name' => $user->name,
            'email' => 'taken@example.com',
            'active' => true,
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_profile_with_password_change(): void
    {
        $user = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'password' => bcrypt('old-password'),
        ]);

        $response = $this->actingAs($user)->post('/profile/update/employee', [
            'name' => $user->name,
            'email' => $user->email,
            'active' => true,
            'old_password' => 'old-password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    // ─── Load User Profile ───────────────────────────────────

    public function test_load_user_profile_without_avatar(): void
    {
        $user = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'avatar' => null,
        ]);

        $response = $this->actingAs($user)->get('/employee/profile');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Profile')
                ->where('avatar_url', null);
        });
    }

    public function test_load_user_profile_with_avatar(): void
    {
        $user = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'avatar' => 'avatars/photo.jpg',
        ]);

        $response = $this->actingAs($user)->get('/employee/profile');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Profile')
                ->has('avatar_url');
        });
    }

    // ─── Registered Users ────────────────────────────────────

    public function test_registered_users_returns_users_in_same_org_unit(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);
        User::factory()->create(['organization_unit_id' => $this->orgUnit->id]);

        $response = $this->actingAs($approver)->get('/users/registered');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Approver/ManageUser')
                ->has('users')
                ->has('units');
        });
    }

    // ─── Update User Information (approver/admin) ────────────

    public function test_update_user_information(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);
        $target = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($approver)->post('/users/update', [
            'id' => $target->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'active' => true,
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Updated Name', $target->fresh()->name);
    }

    public function test_update_user_information_fails_for_nonexistent_user(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);

        $response = $this->actingAs($approver)->post('/users/update', [
            'id' => 99999,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertSessionHasErrors(['message']);
    }

    public function test_update_user_information_with_password_change(): void
    {
        $approver = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'approver',
        ]);
        $target = User::factory()->create([
            'organization_unit_id' => $this->orgUnit->id,
        ]);

        $response = $this->actingAs($approver)->post('/users/update', [
            'id' => $target->id,
            'name' => $target->name,
            'email' => $target->email,
            'active' => true,
            'organization_unit_id' => $this->orgUnit->id,
            'role' => 'employee',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('newpassword123', $target->fresh()->password));
    }

    // ─── Direct Register Form ────────────────────────────────

    public function test_direct_register_form_returns_org_units(): void
    {
        OrganizationUnit::factory()->count(3)->create();

        $response = $this->get('/register');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Auth/Register')
                ->has('units');
        });
    }

    // ─── Password Reset ──────────────────────────────────────

    public function test_send_reset_link_with_valid_email(): void
    {
        $user = User::factory()->create(['organization_unit_id' => $this->orgUnit->id]);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $user->email])
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('message');
    }

    public function test_send_reset_link_with_nonexistent_email(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'nobody@example.com']);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_send_reset_link_without_email(): void
    {
        $response = $this->post('/forgot-password', []);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_show_reset_form(): void
    {
        $response = $this->get('/reset-password/some-token?email=user@example.com');

        $response->assertSuccessful();
        $response->assertInertia(function ($page) {
            return $page->component('Auth/ResetPassword')
                ->where('token', 'some-token')
                ->where('email', 'user@example.com');
        });
    }

    public function test_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['organization_unit_id' => $this->orgUnit->id]);

        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::PASSWORD_RESET);

        $response = $this->post('/reset-password', [
            'token' => 'valid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_reset_password_with_invalid_token(): void
    {
        $user = User::factory()->create(['organization_unit_id' => $this->orgUnit->id]);

        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::INVALID_TOKEN);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
