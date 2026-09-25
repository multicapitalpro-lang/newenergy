<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Liberação de preço</h1>
    <p style="color:var(--muted);margin-top:-10px">Orçamentos com desconto acima do limite do vendedor, aguardando aprovação.</p>

    <table class="simple">
        <thead><tr><th>Orçamento</th><th>Solicitado por</th><th>Desconto pedido</th><th>Status</th><th>Data</th><?php if (in_array($user['role'], ['admin', 'gerente', 'supervisor', 'licenciado', 'gestor'], true)): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
            <?php foreach ($approvals as $a): ?>
                <tr>
                    <td><a href="/orcamentos/<?= $a['approvable_id'] ?>">#<?= $a['approvable_id'] ?></a></td>
                    <td><?= View::e($a['requested_by_name']) ?></td>
                    <td><?= number_format($a['requested_discount_pct'], 0) ?>%</td>
                    <td><span class="badge-status <?= View::e($a['status']) ?>"><?= View::e($a['status']) ?></span></td>
                    <td><?= View::e($a['created_at']) ?></td>
                    <?php if (in_array($user['role'], ['admin', 'gerente', 'supervisor', 'licenciado', 'gestor'], true)): ?>
                        <td>
                            <?php if ($a['status'] === 'pendente'): ?>
                                <form method="post" action="/aprovacoes/<?= $a['id'] ?>/decidir" style="display:inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="decision" value="aprovado">
                                    <button type="submit" class="btn-link-danger" style="color:#0f5132">Aprovar</button>
                                </form>
                                <form method="post" action="/aprovacoes/<?= $a['id'] ?>/decidir" style="display:inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="decision" value="recusado">
                                    <button type="submit" class="btn-link-danger">Recusar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($approvals)): ?>
                <tr><td colspan="6">Nenhuma liberação de preço pendente.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
