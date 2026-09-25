<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Router;
use App\Core\View;
use App\Models\Lead;

class PublicLeadController
{
    public function show(): void
    {
        View::render('contact', ['user' => Auth::user()], 'loja');
    }

    public function store(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/contato');
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Router::redirect('/contato');
        }

        $id = Lead::create([
            'name' => $name,
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'message' => trim($_POST['message'] ?? ''),
            'source' => 'site',
        ]);

        Router::redirect('/contato?enviado=1');
    }
}
