<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/">Home</a> &rsaquo; <a href="/catalogo?categoria=<?= urlencode($product['category'] ?? '') ?>"><?= View::e($product['category'] ?? 'Catálogo') ?></a> &rsaquo; <?= View::e($product['name']) ?></div>

    <div class="product-detail">
        <?= View::productThumb($product, 'thumb-large') ?>

        <div>
            <span class="product-badge" style="position:static;display:inline-block;margin-bottom:10px">New Energy</span>
            <h1 style="margin:0 0 6px;font-size:1.5rem"><?= View::e($product['name']) ?></h1>
            <?= View::stars() ?>

            <div class="buy-box">
                <span class="price-main"><?= View::money((int) $product['sell_price_cents']) ?></span>
                <div class="price-sub"><?= View::installmentLine((int) $product['sell_price_cents']) ?></div>

                <?php if ($user): ?>
                    <form method="post" action="/pedidos" class="stack" style="margin-top:16px;max-width:360px">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                        <label for="quantity">Quantidade</label>
                        <input type="number" id="quantity" name="quantity" min="1" value="1" required>

                        <label for="client_name">Nome do cliente</label>
                        <input type="text" id="client_name" name="client_name" required>

                        <label for="client_document">CNPJ/CPF do cliente</label>
                        <input type="text" id="client_document" name="client_document">

                        <label for="client_email">E-mail do cliente</label>
                        <input type="email" id="client_email" name="client_email">

                        <label for="client_phone">Telefone do cliente</label>
                        <input type="text" id="client_phone" name="client_phone">

                        <button type="submit" class="btn btn-accent" style="margin-top:8px">FAZER PEDIDO</button>
                    </form>
                <?php else: ?>
                    <p style="margin:14px 0 8px">Pra fazer um pedido, entre com sua conta de licenciado/vendedor:</p>
                    <div style="display:flex;gap:8px">
                        <a href="/login?next=<?= urlencode('/produtos/' . $product['id']) ?>" class="btn btn-dark">Entrar</a>
                        <a href="/cadastro" class="btn btn-accent">Sou licenciado</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($product['datasheet_path'])): ?>
                <p style="margin-top:16px"><a href="<?= View::e($product['datasheet_path']) ?>">📄 Baixar datasheet</a></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($product['short_description']) || !empty($product['description'])): ?>
        <div class="card-box" style="margin-top:32px">
            <h2 style="margin-top:0">Descrição</h2>
            <p><?= View::e($product['short_description']) ?></p>
            <?php if (!empty($product['description'])): ?>
                <p><?= nl2br(View::e($product['description'])) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
