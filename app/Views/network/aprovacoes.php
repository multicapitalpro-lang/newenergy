<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Aprovação de cadastros</h1>
    <p style="color:var(--muted);margin-top:-12px">Licenciados que se auto-cadastraram e aguardam liberação pra usar o painel.</p>

    <?php foreach ($pending as $p): ?>
        <div class="card-box" style="margin-bottom:16px">
            <strong><?= View::e($p['name']) ?></strong> — <?= View::e($p['company_name']) ?><br>
            <span style="color:var(--muted);font-size:0.85rem">
                <?= View::e($p['email']) ?><?= $p['phone'] ? ' · ' . View::e($p['phone']) : '' ?><?= $p['document'] ? ' · ' . View::e($p['document']) : '' ?>
            </span>

            <div style="display:flex;gap:12px;margin-top:14px;align-items:center">
                <form method="post" action="/licenciados/<?= $p['id'] ?>/aprovar">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-accent">Aprovar</button>
                </form>
                <form method="post" action="/licenciados/<?= $p['id'] ?>/reprovar" style="display:flex;gap:8px;align-items:center">
                    <?= Csrf::field() ?>
                    <input type="text" name="reason" placeholder="Motivo da recusa" required>
                    <button type="submit" class="btn-link-danger">Reprovar</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($pending)): ?>
        <p>Nenhum cadastro pendente de aprovação.</p>
    <?php endif; ?>

    <p style="margin-top:24px"><a href="/licenciados">&larr; Voltar pra Expansão do Licenciado</a></p>
</div>
