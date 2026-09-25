<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/leads">Leads</a> &rsaquo; Extensões de prazo</div>
    <h1 class="section-title">Extensões de prazo</h1>

    <table class="simple">
        <thead><tr><th>Lead</th><th>Solicitado por</th><th>Motivo</th><th>Status</th><th>Data</th><?php if (in_array($user['role'], ['admin', 'licenciado', 'gestor'], true)): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td><a href="/leads/<?= $r['lead_id'] ?>"><?= View::e($r['lead_name']) ?></a></td>
                    <td><?= View::e($r['requested_by_name']) ?></td>
                    <td><?= View::e($r['reason']) ?></td>
                    <td><span class="badge-status <?= View::e($r['status']) ?>"><?= View::e($r['status']) ?></span></td>
                    <td><?= View::e($r['created_at']) ?></td>
                    <?php if (in_array($user['role'], ['admin', 'licenciado', 'gestor'], true)): ?>
                        <td>
                            <?php if ($r['status'] === 'pendente'): ?>
                                <form method="post" action="/leads/extensoes/<?= $r['id'] ?>/decidir" style="display:inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="decision" value="aprovado">
                                    <button type="submit" class="btn-link-danger" style="color:#0f5132">Aprovar</button>
                                </form>
                                <form method="post" action="/leads/extensoes/<?= $r['id'] ?>/decidir" style="display:inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="decision" value="recusado">
                                    <button type="submit" class="btn-link-danger">Recusar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($requests)): ?>
                <tr><td colspan="6">Nenhum pedido de extensão ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
