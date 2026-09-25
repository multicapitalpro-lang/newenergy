<?php

use App\Core\Csrf;
use App\Core\View;

$status = $user['contract_status'];
?>
<div class="wrap">
    <h1 class="section-title">Meu Contrato</h1>

    <?php if (!empty($blocked)): ?>
        <div class="card-box" style="margin-bottom:20px;border-color:#dc3545">
            <p style="margin:0">Você precisa ter o contrato aprovado antes de fechar pedidos.</p>
        </div>
    <?php endif; ?>

    <div class="card-box" style="max-width:560px">
        <p>Status: <span class="badge-status <?= View::e($status) ?>"><?= View::e(str_replace('_', ' ', $status)) ?></span></p>

        <?php if ($status === 'reprovado' && !empty($user['contract_rejection_reason'])): ?>
            <p><strong>Motivo da recusa:</strong> <?= View::e($user['contract_rejection_reason']) ?></p>
        <?php endif; ?>

        <?php if ($status === 'aprovado'): ?>
            <p>Seu contrato foi aprovado — você já pode fechar pedidos normalmente.</p>
        <?php else: ?>
            <p>Envie o contrato de adesão assinado (PDF ou foto/scan) pra liberar seu acesso a fechamento de pedidos.</p>
            <form method="post" action="/meu-contrato" enctype="multipart/form-data" class="stack">
                <?= Csrf::field() ?>
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                <button type="submit" class="btn btn-accent">Enviar contrato</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($user['contract_path'])): ?>
            <p style="margin-top:16px"><a href="<?= View::e($user['contract_path']) ?>" target="_blank">Ver arquivo enviado</a></p>
        <?php endif; ?>
    </div>
</div>
