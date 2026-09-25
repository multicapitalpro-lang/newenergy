<?php

use App\Core\View;
?>
<section class="hero">
    <div class="hero-inner">
        <div>
            <h1>Energia e armazenamento pra sua revenda crescer</h1>
            <p>A SB New Energy conecta sua equipe de vendas aos melhores produtos de armazenamento de energia (BESS), eletropostos e soluções relacionadas — com suporte de ponta a ponta.</p>
            <div class="actions">
                <a href="/catalogo" class="btn btn-accent">Ver catálogo</a>
                <a href="/cadastro" class="btn btn-outline">Quero ser licenciado</a>
            </div>
        </div>
        <?php if (!empty($featured[0]['image_path'])): ?>
            <div class="hero-image">
                <img src="<?= View::e($featured[0]['image_path']) ?>" alt="<?= View::e($featured[0]['name']) ?>">
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="badges">
    <div class="badge-item">
        <div class="icon">🔋</div>
        <div><h3>Catálogo completo</h3><p>Armazenamento de energia, eletropostos e mais, tudo num só lugar.</p></div>
    </div>
    <div class="badge-item">
        <div class="icon">👥</div>
        <div><h3>Rede de licenciados</h3><p>Monte sua equipe de vendedores e acompanhe cada pedido.</p></div>
    </div>
    <div class="badge-item">
        <div class="icon">🛠️</div>
        <div><h3>Suporte técnico</h3><p>Apoio no dimensionamento e na validação de projetos.</p></div>
    </div>
    <div class="badge-item">
        <div class="icon">💳</div>
        <div><h3>Facilidade de pagamento</h3><p>Condições facilitadas pro seu cliente fechar negócio.</p></div>
    </div>
</div>

<div class="wrap">
    <h2 class="section-title">Produtos em destaque</h2>
    <div class="grid">
        <?php foreach ($featured as $product): ?>
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
        <?php if (empty($featured)): ?>
            <p>Nenhum produto cadastrado ainda.</p>
        <?php endif; ?>
    </div>
</div>

<div class="split-cta">
    <div class="card">
        <h3>Sou Licenciado</h3>
        <p>Monte sua rede de vendedores, acompanhe pedidos e comissões numa plataforma só.</p>
        <a href="/cadastro" class="btn btn-dark">Quero ser licenciado</a>
    </div>
    <div class="card">
        <h3>Sou Integrador</h3>
        <p>Compre direto pelo catálogo, com todo o suporte de um licenciado da rede SB New Energy perto de você.</p>
        <a href="/catalogo" class="btn btn-dark">Ver catálogo</a>
    </div>
</div>
