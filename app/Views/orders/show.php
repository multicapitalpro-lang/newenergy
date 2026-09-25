<?php

use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/pedidos">Pedidos</a> &rsaquo; #<?= $order['id'] ?></div>

    <?php if (!empty($_GET['criado'])): ?>
        <p class="success">Pedido criado com sucesso!</p>
    <?php endif; ?>

    <h1 class="section-title">Pedido #<?= $order['id'] ?></h1>

    <div class="card-box" style="margin-bottom:24px">
        <p><strong>Cliente:</strong> <?= View::e($order['client_name']) ?></p>
        <p><strong>Vendedor:</strong> <?= View::e($order['seller_name']) ?></p>
        <p><strong>Status:</strong> <span class="badge-status <?= View::e($order['status']) ?>"><?= View::e($order['status']) ?></span></p>
        <p><strong>Total:</strong> <?= View::money((int) $order['total_cents']) ?></p>
    </div>

    <table class="simple">
        <thead>
            <tr><th>Produto</th><th>SKU</th><th>Quantidade</th><th>Preço unit.</th><th>Subtotal</th></tr>
        </thead>
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
</div>
