<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['status' => 'active'], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an active account.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectTo($request->user());
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'barangays' => Barangay::query()->active()->ordered()->get(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'barangay_id' => [
                'required',
                'integer',
                Rule::exists('barangays', 'id')->where('is_active', true),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'purok' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:190'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'terms' => ['accepted'],
            'password' => PasswordPolicy::requiredRules($request),
        ], [
            'terms.accepted' => 'You must accept the data privacy notice to continue.',
            'name.min' => 'Please enter your full name (at least 2 characters).',
            'birth_date.before' => 'Birth date must be in the past.',
            'barangay_id.required' => 'Please choose the barangay you live in.',
            'barangay_id.exists' => 'That barangay is not part of this city.',
        ] + PasswordPolicy::messages());

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'role' => 'resident',
            'barangay_id' => (int) $data['barangay_id'],
            'phone' => $data['phone'] ?? null,
            'purok' => $data['purok'] ?? null,
            'address' => $data['address'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('resident.dashboard')
            ->with('success', 'Welcome, '.explode(' ', $user->name)[0].'! Your resident account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }

    /**
     * Two interfaces, one landing page each: an officer opens the City
     * Administrator console, everyone else opens the Resident portal.
     */
    private function redirectTo(User $user): RedirectResponse
    {
        return redirect()->intended(
            $user->isOfficial() ? route('admin.dashboard') : route('resident.dashboard')
        );
    }
}
