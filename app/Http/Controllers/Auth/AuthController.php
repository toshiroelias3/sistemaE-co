<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Muestra el formulario de Login
    public function showLogin() {
        return view('auth.login');
    }

    // Procesa el inicio de sesión seguro
    public function login(Request $request) {
        // 1. Validar credenciales con Rate Limiting nativo implícito
        $credentials = $request->validate([
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 2. Intentar autenticar (Laravel maneja la protección contra SQL Injection aquí)
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate(); // Previene ataques de fijación de sesión

            // Redirección inteligente según el rol
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin/dashboard')->with('success', 'Bienvenido al panel de administración.');
            }
            return redirect()->intended('/')->with('success', 'Sesión iniciada correctamente.');
        }

        // Si falla, regresamos con error genérico (Buena práctica de seguridad para no dar pistas a atacantes)
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    // Muestra el formulario de Registro
    public function showRegister() {
        return view('auth.register');
    }

    // Procesa el registro de un nuevo cliente
    public function register(Request $request) {
        // Validaciones estrictas de negocio
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // Exige campo password_confirmation
        ]);

        // Creación del usuario con Password Hashing seguro (Argon2id/Bcrypt automático en Laravel)
        $user = User::create([
            'name' => strip_tags($request->name), // Sanitización básica de XSS
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client', // Forzamos que siempre sea cliente por seguridad
        ]);

        // Iniciar sesión automáticamente después de registrarse
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Cuenta creada con éxito. ¡Bienvenido a TEZAN!');
    }

    // Formulario para pedir el enlace de recuperación
    public function showForgotPassword() {
        return view('auth.forgot-password');
    }

    // Envía el enlace de recuperación (mismo mensaje exista o no el correo, para no revelar cuentas)
    public function sendResetLink(Request $request) {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        return back()->with('success', 'Si el correo está registrado, te enviamos un enlace para crear una nueva contraseña. Revisa tu bandeja de entrada y la carpeta de spam.');
    }

    // Formulario para elegir la nueva contraseña (llega desde el enlace del correo)
    public function showResetPassword(Request $request, string $token) {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    // Guarda la nueva contraseña
    public function resetPassword(Request $request) {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.')
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }

    // Cierra la sesión de forma segura
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken(); // Destruye el token CSRF actual por seguridad
        return redirect('/')->with('success', 'Sesión cerrada correctamente.');
    }
}