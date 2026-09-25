<?php

use App\Core\Csrf;
use App\Core\Roles;
use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Aprovar vendedores</h1>
    <p style="color:var(--muted);margin-top:-12px">Contratos assinados aguardando aprovação pra liberar fechamento de pedidos.</p>

    <?php foreach ($pending as $p): ?>
        <div class="card-box" style="margin-bottom:16px">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <div>
                    <strong><?= View::e($p['name']) ?></strong> — <?= View::e(Roles::label($p['role'])) ?><br>
                    <span style="color:var(--muted);font-size:0.85rem"><?= View::e($p['email']) ?></span>
                </div>
                <?php if (!empty($p['contract_path'])): ?>
                    <a href="<?= View::e($p['contract_path']) ?>" target="_blank" class="btn btn-dark">Ver contrato</a>
                <?php endif; ?>
            </div>

            <div style="display:flex;gap:12px;margin-top:14px;align-items:center">
                <form method="post" action="/vendedores/<?= $p['id'] ?>/aprovar">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-accent">Aprovar</button>
                </form>
                <form method="post" action="/vendedores/<?= $p['id'] ?>/reprovar" style="display:flex;gap:8px;align-items:center">
                    <?= Csrf::field() ?>
                    <input type="text" name="reason" placeholder="Motivo da recusa" required>
                    <button type="submit" class="btn-link-danger">Reprovar</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($pending)): ?>
        <p>Nenhum contrato pendente de aprovação.</p>
    <?php endif; ?>
</div>
