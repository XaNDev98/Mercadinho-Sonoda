<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->get('secullum_autenticado')) {
            return redirect()->route('retiradas.create');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Informe a senha.',
        ]);

        $emailEnv = trim((string) env('SECULLUM_USERNAME'));
        $senhaEnv = trim((string) env('SECULLUM_PASSWORD'));

        $emailInformado = trim((string) $request->email);
        $senhaInformada = trim((string) $request->password);

        if ($emailInformado !== $emailEnv || $senhaInformada !== $senhaEnv) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Usuário ou senha inválidos.');
        }

        session([
            'secullum_autenticado' => true,
            'secullum_usuario' => $emailInformado,
        ]);

        $request->session()->regenerate();

        return redirect()
            ->route('retiradas.create')
            ->with('success', 'Login realizado com sucesso.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget([
            'secullum_autenticado',
            'secullum_usuario',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Logout realizado com sucesso.');
    }
}