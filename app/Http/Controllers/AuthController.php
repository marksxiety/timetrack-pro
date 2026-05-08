<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\OrganizationUnit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Traits\HasScopedQueries;

class AuthController extends Controller
{
    use HasScopedQueries;
    public function register(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'employeeid' => 'required|unique:users|string|max:255',
            'organization_unit_id' => 'required|exists:organization_units,id',
            'role' => ['required', Rule::in(['employee', 'approver'])],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'organization_unit_id.required' => 'Unit is required'
        ]);

        // implement hash value in the password
        $data['password'] = bcrypt($data['password']);

        // Insert data of the user
        $user = User::create($data);

        Auth::login($user);

        // Redirect to home
        return redirect()->route('main')->with('message', 'Welcome, ' . $user->name);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            return redirect()->route('main')->with('message', 'Welcome back, ' . Auth::user()->name . '!');
        }

        return redirect()->back()->withErrors(['email' => 'Email or Password is incorrect.', 'password' => 'Email or Password is incorrect.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login page
        return redirect()->route('login')->with('message', 'You have been logged out successfully.');
    }

    public function updateProfileInformation(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return back()->withErrors(['message' => 'Invalid Request! Not authenticated user.']);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ];

        if (
            $request->filled('old_password') ||
            $request->filled('new_password') ||
            $request->filled('new_password_confirmation')
        ) {
            $rules['old_password'] = 'required|string|min:8';
            $rules['new_password'] = 'required|string|min:8|confirmed';
        }


        $request->validate($rules);

        // Check if old password matches
        if ($request->old_password && !Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Old password is incorrect.']);
        }

        // Check if new password is different from old password
        if ($request->old_password === $request->new_password && $request->new_password) {
            return back()->withErrors(['new_password' => 'New password must be different from the old password.']);
        }

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($user->avatar && file_exists(public_path('storage/' . $user->avatar))) {
                Storage::disk('public')->delete($user->avatar);
            }
            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->active = $request->active;

        // Only update avatar if a new one is uploaded
        if ($avatarPath) {
            $user->avatar = $avatarPath;
        }

        // Only update password if a new one is provided
        if ($request->filled('new_password')) {
            $user->password = bcrypt($request->new_password);
        }

        $user->save();

        return redirect()->back()->with('message', 'Profile has been updated successfully!');
    }

    public function loadUserProfile()
    {
        $user = Auth::user();
        return inertia('Profile', [
            'avatar_url' => $user->avatar ? Storage::url($user->avatar) : null,
        ]);
    }



    public function RegisteredUsers()
    {
        try {
            $orgUnitId = $this->getOrgUnitId();
            $usersQuery = User::query()->orderBy('id', 'asc');

            if ($orgUnitId !== null) {
                $usersQuery->where('organization_unit_id', $orgUnitId);
            }

            $users = $usersQuery->get()
                ->map(function ($user) {
                    $user->avatar_url = $user->avatar
                        ? Storage::url($user->avatar)
                        : null;
                    return $user;
                });

            $units = OrganizationUnit::select('id', 'unit_path')->get();

            return inertia('Approver/ManageUser', [
                'users' => $users,
                'units' => $units
            ]);
        } catch (\Throwable $th) {
            return inertia('Approver/ManageUser', [
                'users' => [],
                'units' => [],
                'errors' => 'Failed to load registered users'
            ]);
        }
    }


    public function updateUserInformation(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return back()->withErrors(['message' => 'User is not registered.']);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ];

        if ($request->filled('new_password') || $request->filled('new_password_confirmation')) {
            $rules['new_password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->active = $request->active;
        $user->organization_unit_id = $request->organization_unit_id;
        $user->role = $request->role;

        // Only update password if a new one is provided
        if ($request->filled('new_password')) {
            $user->password = bcrypt($request->new_password);
        }

        $user->update();
        return redirect()->back()->with('message', 'User Profile has been updated successfully!');
    }

    public function directRegisterForm() {
        $units = OrganizationUnit::select('id', 'unit_path')->get();

        return inertia('Auth/Register', [
            'units' => $units,
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Unable to send reset link. Please try again later.']);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('message', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, string $token)
    {
        return inertia('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('message', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
