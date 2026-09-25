<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Router;
use App\Core\View;
use App\Models\User;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::user()) {
            Router::redirect('/');
        }
        View::render('auth/login', ['next' => $_GET['next'] ?? '/'], null);
    }

    public function login(): void
    {
        $next = $_POST['next'] ?: '/';

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            View::render('auth/login', ['error' => 'Sessão expirada, tente de novo.', 'next' => $next], null);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!Auth::attempt($email, $password)) {
            View::render('auth/login', ['error' => 'E-mail ou senha inválidos.', 'next' => $next], null);
            return;
        }

        Router::redirect($next);
    }

    public function logout(): void
    {
        Auth::logout();
        Router::redirect('/login');
    }

    public function showRegister(): void
    {
        if (Auth::user()) {
            Router::redirect('/');
        }
        View::render('auth/register', [], null);
    }

    public function register(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            View::render('auth/register', ['error' => 'Sessão expirada, tente de novo.'], null);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $companyName = trim($_POST['company_name'] ?? '');
        $document = trim($_POST['document'] ?? '');

        if ($name === '' || $email === '' || strlen($password) < 6 || $companyName === '') {
            View::render('auth/register', [
                'error' => 'Preencha nome, e-mail, empresa e uma senha com pelo menos 6 caracteres.',
                'old' => $_POST,
            ], null);
            return;
        }

        if (User::findByEmail($email)) {
            View::render('auth/register', [
                'error' => 'Já existe uma conta com esse e-mail.',
                'old' => $_POST,
            ], null);
            return;
        }

        // Cadastro público sempre cria um Licenciado (dono de rede). Vendedores
        // são cadastrados pelo próprio licenciado em "Minha Equipe", não aqui --
        // mesmo padrão do EcoDiffusore (Licenciado se auto-cadastra, Vendedor não).
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'licenciado',
            'company_name' => $companyName,
            'document' => $document,
        ]);

        Auth::attempt($email, $password);
        Router::redirect('/?bem-vindo=1');
    }
}
