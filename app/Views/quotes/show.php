<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/orcamentos">Orçamentos</a> &rsaquo; #<?= $quote['id'] ?></div>

    <?php if (!empty($_GET['criado'])): ?>
        <p class="success">Orçamento criado<?= $quote['status'] === 'aguardando_aprovacao' ? ' — desconto acima do seu limite, aguardando liberação de preço' : '' ?>!</p>
    <?php endif; ?>

    <div class="catalog-toolbar">
        <h1 class="section-title" style="margin:0">Orçamento #<?= $quote['id'] ?></h1>
        <span class="badge-status <?= View::e($quote['status']) ?>"><?= View::e(str_replace('_', ' ', $quote['status'])) ?></span>
    </div>

    <div class="card-box" style="margin-bottom:24px">
        <p><strong>Cliente:</strong> <?= View::e($quote['client_name']) ?></p>
        <p><strong>Vendedor:</strong> <?= View::e($quote['seller_name']) ?></p>
        <p><strong>Desconto:</strong> <?= number_format($quote['discount_percent'], 0) ?>%</p>
        <p><strong>Total:</strong> <?= View::money((int) $quote['total_cents']) ?></p>
    </div>

    <table class="simple" style="margin-bottom:24px">
        <thead><tr><th>Produto</th><th>SKU</th><th>Quantidade</th><th>Preço unit.</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= View::e($item['product_name']) ?></td>
                    <td><?= View::e($item['sku']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= View::money((int) $item['unit_price_cents']) ?></td>
                    <td><?= View::money((int) $item['subtotal_cents']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="/orcamentos/<?= $quote['id'] ?>/proposta" class="btn btn-dark" target="_blank">Ver proposta comercial</a>

        <?php if ($quote['status'] === 'aprovado'): ?>
            <form method="post" action="/orcamentos/<?= $quote['id'] ?>/converter">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-accent">Converter em pedido</button>
            </form>
        <?php endif; ?>

        <?php if (in_array($user['role'], ['admin', 'gerente', 'licenciado'], true) && $quote['status'] === 'aberto'): ?>
            <form method="post" action="/orcamentos/<?= $quote['id'] ?>/status">
                <?= Csrf::field() ?>
                <input type="hidden" name="status" value="aprovado">
                <button type="submit" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Aprovar</button>
            </form>
        <?php endif; ?>
    </div>
</div>
