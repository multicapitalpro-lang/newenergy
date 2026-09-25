<?php

use App\Core\Auth;
use App\Core\View;

$currentUser = Auth::user();
$categories = \App\Models\Product::categories();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SB New Energy — Distribuição de Energia e Armazenamento</title>
    <link rel="stylesheet" href="<?= View::asset('/assets/css/app.css') ?>">
</head>
<body>
    <div class="announce-bar">REDE DE LICENCIADOS SB NEW ENERGY &nbsp;|&nbsp; ARMAZENAMENTO DE ENERGIA, ELETROPOSTOS E MAIS</div>

    <header class="site-header">
        <a href="/" class="brand">SB NEW<span>ENERGY</span></a>

        <form class="search-form" method="get" action="/catalogo">
            <input type="text" name="busca" placeholder="O que você procura?" value="<?= View::e($_GET['busca'] ?? '') ?>">
            <button type="submit" aria-label="Buscar">🔍</button>
        </form>

        <div class="header-actions">
            <?php if ($currentUser): ?>
                <a href="/logout">Sair (<?= View::e($currentUser['name']) ?> · <?= View::e(\App\Core\Roles::label($currentUser['role'])) ?>)</a>
            <?php else: ?>
                <a href="/login">👤 Entrar</a>
                <a href="/cadastro" class="cta">Sou Licenciado</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($currentUser): ?>
        <div class="category-bar internal-nav">
            <a href="/leads">📋 Leads</a>
            <a href="/clientes">👤 Clientes</a>
            <a href="/orcamentos">🧾 Orçamentos</a>
            <a href="/pedidos">📦 Pedidos</a>
            <a href="/aprovacoes">✅ Aprovações</a>
            <a href="/calculadora">🧮 Calculadora</a>
            <a href="/material-de-venda">📁 Material</a>
            <?php if (in_array($currentUser['role'], ['admin', 'gerente', 'supervisor', 'licenciado', 'gestor'], true)): ?>
                <a href="/minha-equipe">👥 Minha Equipe</a>
            <?php endif; ?>
            <?php if (in_array($currentUser['role'], ['admin', 'gerente'], true)): ?>
                <a href="/tabela-precos">💲 Tabela de preços</a>
                <a href="/config-pagamentos">💳 Pagamentos</a>
            <?php endif; ?>
            <a href="/catalogo">🔋 Catálogo</a>
        </div>
    <?php else: ?>
        <div class="category-bar">
            <a href="/catalogo">Todos os produtos</a>
            <?php foreach ($categories as $cat): ?>
                <a href="/catalogo?categoria=<?= urlencode($cat) ?>"><?= View::e($cat) ?></a>
            <?php endforeach; ?>
            <a href="/calculadora">Calculadora de economia</a>
            <a href="/contato">Fale com um especialista</a>
        </div>
    <?php endif; ?>

    <?= $content() ?>

    <footer class="site-footer">
        SB New Energy — Distribuição de sistemas de armazenamento de energia via rede de licenciados parceiros.
    </footer>
</body>
</html>
