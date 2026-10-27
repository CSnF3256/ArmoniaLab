<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\ValidEcuadorianCedula;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'El correo o la contraseña no son correctos.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('cursos.index'))
            ->with('success', 'Sesión iniciada correctamente.');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', 'string', 'in:estudiante,docente,admin'],
            'cedula'   => ['nullable', 'string', 'unique:users,cedula', new ValidEcuadorianCedula()],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'cedula.unique' => 'Esta cédula ya se encuentra registrada.',
            'role.in'       => 'El rol seleccionado no es válido.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'cedula'   => $data['cedula'] ?? null,
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('acordes.index')
            ->with('success', 'Cuenta creada correctamente. ¡Bienvenido a ArmoníaLab!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
