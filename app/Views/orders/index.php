<?php

use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Pedidos</h1>

    <table class="simple">
        <thead>
            <tr><th>#</th><th>Cliente</th><th>Vendedor</th><th>Total</th><th>Status</th><th>Data</th></tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><a href="/pedidos/<?= $o['id'] ?>">#<?= $o['id'] ?></a></td>
                    <td><?= View::e($o['client_name']) ?></td>
                    <td><?= View::e($o['seller_name']) ?></td>
                    <td><?= View::money((int) $o['total_cents']) ?></td>
                    <td><span class="badge-status <?= View::e($o['status']) ?>"><?= View::e($o['status']) ?></span></td>
                    <td><?= View::e($o['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr><td colspan="6">Nenhum pedido ainda. <a href="/catalogo">Ver catálogo</a>.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
