<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/leads">Leads</a> &rsaquo; <?= View::e($lead['name']) ?></div>

    <?php if (!empty($_GET['extensao_solicitada'])): ?>
        <p class="success">Pedido de extensão de prazo enviado.</p>
    <?php endif; ?>

    <div class="catalog-toolbar">
        <h1 class="section-title" style="margin:0"><?= View::e($lead['name']) ?></h1>
        <span class="badge-status <?= View::e($lead['status']) ?>"><?= View::e($lead['status']) ?></span>
    </div>

    <div class="card-box" style="margin-bottom:24px">
        <p><strong>Telefone:</strong> <?= View::e($lead['phone']) ?: '—' ?></p>
        <p><strong>E-mail:</strong> <?= View::e($lead['email']) ?: '—' ?></p>
        <p><strong>Cidade/UF:</strong> <?= View::e($lead['city']) ?> / <?= View::e($lead['state']) ?></p>
        <p><strong>Origem:</strong> <?= View::e($lead['source']) ?></p>
        <p><strong>Responsável:</strong> <?= View::e($lead['assigned_to_name'] ?? '—') ?></p>
        <?php if (!empty($lead['message'])): ?>
            <p><strong>Mensagem:</strong> <?= nl2br(View::e($lead['message'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="card-box" style="margin-bottom:24px">
        <h3 style="margin-top:0">Ações</h3>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <form method="post" action="/leads/<?= $lead['id'] ?>/status">
                <?= Csrf::field() ?>
                <input type="hidden" name="status" value="contatado">
                <button type="submit" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Marcar como contatado</button>
            </form>
            <form method="post" action="/leads/<?= $lead['id'] ?>/converter">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-accent">Converter em cliente</button>
            </form>
            <form method="post" action="/leads/<?= $lead['id'] ?>/status">
                <?= Csrf::field() ?>
                <input type="hidden" name="status" value="descartado">
                <button type="submit" class="btn" style="background:#f8d7da;color:#842029">Descartar</button>
            </form>
        </div>

        <details style="margin-top:16px">
            <summary style="cursor:pointer;font-weight:600;font-size:0.85rem">Solicitar extensão de prazo</summary>
            <form method="post" action="/leads/extensoes" class="stack" style="margin-top:10px;max-width:420px">
                <?= Csrf::field() ?>
                <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                <label>Motivo</label>
                <textarea name="reason" rows="2" required></textarea>
                <button type="submit" class="btn btn-dark" style="margin-top:8px">Solicitar</button>
            </form>
        </details>
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
        <form method="post" action="/leads/<?= $lead['id'] ?>/nota" class="stack" style="max-width:420px">
            <?= Csrf::field() ?>
            <textarea name="note" rows="2" placeholder="Nova anotação..." required></textarea>
            <button type="submit" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Adicionar anotação</button>
        </form>
    </div>
</div>
