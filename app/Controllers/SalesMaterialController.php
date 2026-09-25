<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\SalesMaterial;

class SalesMaterialController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        View::render('materials/index', [
            'user' => $user,
            'materials' => SalesMaterial::all(),
        ], 'loja');
    }

    public function store(): void
    {
        $user = Auth::requireRole([Roles::ADMIN, Roles::GERENTE]);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/material-de-venda');
        }

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            Router::redirect('/material-de-venda');
        }

        $filePath = null;
        if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $dir = BASE_PATH . '/public_html/uploads/materials';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = uniqid('mat_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . $filename);
            $filePath = '/uploads/materials/' . $filename;
        }

        SalesMaterial::create($title, trim($_POST['description'] ?? '') ?: null, $filePath, trim($_POST['link'] ?? '') ?: null);

        Router::redirect('/material-de-venda');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole([Roles::ADMIN, Roles::GERENTE]);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/material-de-venda');
        }

        SalesMaterial::delete((int) $id);
        Router::redirect('/material-de-venda');
    }
}
