<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Product;

class HomeController
{
    public function index(): void
    {
        $products = Product::all(onlyActive: true);

        View::render('home', [
            'user' => Auth::user(),
            'products' => $products,
            'featured' => array_slice($products, 0, 4),
            'categories' => Product::categories(),
        ], 'loja');
    }

    public function catalog(): void
    {
        $category = $_GET['categoria'] ?? null;
        $query = trim($_GET['busca'] ?? '');
        $sort = $_GET['ordenar'] ?? null;

        $products = Product::search([
            'category' => $category ?: null,
            'q' => $query ?: null,
            'sort' => $sort,
        ]);

        View::render('catalog', [
            'user' => Auth::user(),
            'products' => $products,
            'categories' => Product::categories(),
            'currentCategory' => $category,
            'query' => $query,
            'sort' => $sort,
        ], 'loja');
    }
}
