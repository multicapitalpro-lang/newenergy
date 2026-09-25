<?php

use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/">Home</a> &rsaquo; <?= $currentCategory ? View::e($currentCategory) : 'Catálogo' ?></div>

    <div class="layout-2col">
        <aside class="filter-box">
            <h4>Categorias</h4>
            <ul>
                <li><a href="/catalogo" class="<?= $currentCategory ? '' : 'active' ?>">Todos os produtos</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li><a href="/catalogo?categoria=<?= urlencode($cat) ?>" class="<?= $currentCategory === $cat ? 'active' : '' ?>"><?= View::e($cat) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div>
            <div class="catalog-toolbar">
                <h1 class="section-title" style="margin:0"><?= $currentCategory ? View::e($currentCategory) : ($query !== '' ? 'Resultados para "' . View::e($query) . '"' : 'Todos os produtos') ?></h1>
                <form method="get" action="/catalogo" id="sort-form">
                    <?php foreach (['categoria' => $currentCategory, 'busca' => $query] as $k => $v): ?>
                        <?php if ($v): ?><input type="hidden" name="<?= $k ?>" value="<?= View::e($v) ?>"><?php endif; ?>
                    <?php endforeach; ?>
                    <select name="ordenar" onchange="document.getElementById('sort-form').submit()">
                        <option value="">Ordenar por: Relevância</option>
                        <option value="menor-preco" <?= $sort === 'menor-preco' ? 'selected' : '' ?>>Menor preço</option>
                        <option value="maior-preco" <?= $sort === 'maior-preco' ? 'selected' : '' ?>>Maior preço</option>
                        <option value="nome" <?= $sort === 'nome' ? 'selected' : '' ?>>Nome (A-Z)</option>
                    </select>
                </form>
            </div>

            <div class="grid">
                <?php foreach ($products as $product): ?>
                    <a class="product-card" href="/produtos/<?= $product['id'] ?>">
                        <span class="product-badge">New Energy</span>
                        <?= View::productThumb($product) ?>
                        <div class="product-body">
                            <span class="product-category"><?= View::e($product['category'] ?? 'SB New Energy') ?></span>
                            <h3><?= View::e($product['name']) ?></h3>
                            <?= View::stars() ?>
                            <div class="price-block">
                                <span class="price-main"><?= View::money((int) $product['sell_price_cents']) ?></span>
                                <span class="price-sub"><?= View::installmentLine((int) $product['sell_price_cents']) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <p>Nenhum produto encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
