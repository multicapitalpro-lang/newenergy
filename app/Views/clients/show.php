<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/clientes">Clientes</a> &rsaquo; <?= View::e($client['name']) ?></div>

    <?php if (!empty($_GET['convertido'])): ?>
        <p class="success">Lead convertido em cliente com sucesso!</p>
    <?php endif; ?>

    <h1 class="section-title"><?= View::e($client['name']) ?></h1>

    <div class="card-box" style="margin-bottom:24px">
        <p><strong>Documento:</strong> <?= View::e($client['document']) ?: '—' ?></p>
        <p><strong>Telefone:</strong> <?= View::e($client['phone']) ?: '—' ?></p>
        <p><strong>E-mail:</strong> <?= View::e($client['email']) ?: '—' ?></p>
        <p><strong>Cidade/UF:</strong> <?= View::e($client['city']) ?> / <?= View::e($client['state']) ?></p>
        <p><strong>Vendedor responsável:</strong> <?= View::e($client['seller_name'] ?? '') ?></p>
    </div>

    <div class="card-box" style="margin-bottom:24px">
        <h3 style="margin-top:0">Pedidos</h3>
        <table class="simple">
            <thead><tr><th>#</th><th>Status</th><th>Total</th><th>Data</th></tr></thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><a href="/pedidos/<?= $o['id'] ?>">#<?= $o['id'] ?></a></td>
                        <td><span class="badge-status <?= View::e($o['status']) ?>"><?= View::e($o['status']) ?></span></td>
                        <td><?= View::money((int) $o['total_cents']) ?></td>
                        <td><?= View::e($o['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="4">Nenhum pedido ainda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-box">
        <h3 style="margin-top:0">Anotações</h3>
        <div class="notes-list">
            <?php foreach ($notes as $note): ?>
                <div class="note">
                    <div class="meta"><?= View::e($note['user_name']) ?> — <?= View::e($note['created_at']) ?></div>
                    <?= nl2br(View::e($note['note'])) ?>
                </div>
            <?php endforeach; ?>
            <?php if (empty($notes)): ?>
                <p style="color:var(--muted)">Nenhuma anotação ainda.</p>
            <?php endif; ?>
        </div>
        <form method="post" action="/clientes/<?= $client['id'] ?>/nota" class="stack" style="max-width:420px">
            <?= Csrf::field() ?>
            <textarea name="note" rows="2" placeholder="Nova anotação..." required></textarea>
            <button type="submit" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Adicionar anotação</button>
        </form>
    </div>
</div>
