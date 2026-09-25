<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="catalog-toolbar">
        <h1 class="section-title" style="margin:0">Metas — <?= View::e($period) ?></h1>
        <form method="get" action="/metas">
            <input type="month" name="periodo" value="<?= View::e($period) ?>" onchange="this.form.submit()">
        </form>
    </div>

    <?php if (!empty($team)): ?>
        <div class="card-box" style="margin-bottom:24px">
            <h3 style="margin-top:0">Nova meta</h3>
            <form method="post" action="/metas" class="form-grid">
                <?= Csrf::field() ?>
                <input type="hidden" name="period" value="<?= View::e($period) ?>">
                <div>
                    <label>Vendedor</label>
                    <select name="user_id" required>
                        <?php foreach ($team as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= View::e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Tipo</label>
                    <select name="target_type">
                        <option value="revenue">Faturamento (R$)</option>
                        <option value="orders_count">Nº de pedidos</option>
                    </select>
                </div>
                <div><label>Valor da meta</label><input type="text" name="target_value" placeholder="50000" required></div>
                <div style="align-self:end"><button type="submit" class="btn btn-accent">Definir meta</button></div>
            </form>
        </div>
    <?php endif; ?>

    <?php $canManage = !empty($team); ?>
    <table class="simple">
        <thead><tr><th>Vendedor</th><th>Tipo</th><th>Meta</th><th>Realizado</th><th>Progresso</th><?php if ($canManage): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
            <?php foreach ($goals as $g): ?>
                <?php
                    $isRevenue = $g['target_type'] === 'revenue';
                    $targetDisplay = $isRevenue ? View::money((int) $g['target_value']) : (int) $g['target_value'];
                    $actualDisplay = $isRevenue ? View::money((int) $g['actual']) : (int) $g['actual'];
                    $pct = $g['target_value'] > 0 ? min(100, round($g['actual'] / $g['target_value'] * 100)) : 0;
                ?>
                <tr>
                    <td><?= View::e($g['user_name']) ?></td>
                    <td><?= $isRevenue ? 'Faturamento' : 'Pedidos' ?></td>
                    <td><?= $targetDisplay ?></td>
                    <td><?= $actualDisplay ?></td>
                    <td>
                        <div class="progress" style="width:120px;display:inline-block;vertical-align:middle"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
                        <?= $pct ?>%
                    </td>
                    <?php if ($canManage): ?>
                        <td>
                            <form method="post" action="/metas/<?= $g['id'] ?>/excluir" onsubmit="return confirm('Remover meta?')">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn-link-danger">Remover</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($goals)): ?>
                <tr><td colspan="6">Nenhuma meta definida pra esse período.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
