<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Product;

class ProductController
{
    public function show(string $id): void
    {
        $product = Product::find((int) $id);
        if (!$product || !$product['active']) {
            http_response_code(404);
            require BASE_PATH . '/app/Views/errors/404.php';
            return;
        }

        View::render('products/show', [
            'user' => Auth::user(),
            'product' => $product,
        ], 'loja');
    }
}
