<?php

namespace App\Core;

class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'loja'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = BASE_PATH . '/app/Views/' . $template . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View não encontrada: {$template}");
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        $content = function () use ($viewFile, $data) {
            extract($data, EXTR_SKIP);
            require $viewFile;
        };

        require BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function money(int $cents): string
    {
        return 'R$ ' . number_format($cents / 100, 2, ',', '.');
    }

    public static function productThumb(array $product, string $class = 'product-thumb'): string
    {
        if (!empty($product['image_path'])) {
            return '<div class="' . $class . ' has-image"><img src="' . self::e($product['image_path']) . '" alt="' . self::e($product['name']) . '"></div>';
        }

        return '<div class="' . $class . '">🔋</div>';
    }

    public static function stars(): string
    {
        return '<span class="stars">★★★★★ <span class="count">0 avaliações</span></span>';
    }

    public static function installmentLine(int $priceCents): string
    {
        $installments = 12;
        $perInstallment = self::money((int) round($priceCents / $installments));
        return 'ou ' . $installments . 'x de ' . $perInstallment . ' sem juros no cartão';
    }

    public static function asset(string $path): string
    {
        $file = BASE_PATH . '/public_html' . $path;
        $version = file_exists($file) ? filemtime($file) : time();
        return $path . '?v=' . $version;
    }
}
