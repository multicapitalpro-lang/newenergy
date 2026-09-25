<?php

use App\Core\Roles;
use App\Core\View;
?>
<div class="wrap">
    <div class="catalog-toolbar">
        <h1 class="section-title" style="margin:0">Vendedores</h1>
        <form method="get" action="/vendedores">
            <select name="periodo" onchange="this.form.submit()">
                <option value="">Todo o período</option>
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <?php $p = date('Y-m', strtotime("-$i months")); ?>
                    <option value="<?= $p ?>" <?= $period === $p ? 'selected' : '' ?>><?= $p ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <table class="simple">
        <thead><tr><th>#</th><th>Nome</th><th>Papel</th><th>Pedidos</th><th>Faturamento</th><th>Clientes</th><th>Leads</th></tr></thead>
        <tbody>
            <?php foreach ($ranking as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?><?= $i === 0 ? ' 🏆' : '' ?></td>
                    <td><?= View::e($r['name']) ?></td>
                    <td><?= View::e(Roles::label($r['role'])) ?></td>
                    <td><?= (int) $r['orders_count'] ?></td>
                    <td><?= View::money((int) $r['revenue_cents']) ?></td>
                    <td><?= (int) $r['clients_count'] ?></td>
                    <td><?= (int) $r['leads_count'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($ranking)): ?>
                <tr><td colspan="7">Nenhum dado ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
