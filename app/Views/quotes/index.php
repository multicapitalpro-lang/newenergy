<?php

use App\Core\View;
?>
<div class="wrap">
    <div class="catalog-toolbar">
        <h1 class="section-title" style="margin:0">Orçamentos</h1>
        <a href="/orcamentos/novo" class="btn btn-accent">Novo orçamento</a>
    </div>

    <table class="simple">
        <thead><tr><th>#</th><th>Cliente</th><th>Vendedor</th><th>Desconto</th><th>Total</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
            <?php foreach ($quotes as $q): ?>
                <tr>
                    <td><a href="/orcamentos/<?= $q['id'] ?>">#<?= $q['id'] ?></a></td>
                    <td><?= View::e($q['client_name']) ?></td>
                    <td><?= View::e($q['seller_name']) ?></td>
                    <td><?= number_format($q['discount_percent'], 0) ?>%</td>
                    <td><?= View::money((int) $q['total_cents']) ?></td>
                    <td><span class="badge-status <?= View::e($q['status']) ?>"><?= View::e(str_replace('_', ' ', $q['status'])) ?></span></td>
                    <td><?= View::e($q['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($quotes)): ?>
                <tr><td colspan="7">Nenhum orçamento ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
