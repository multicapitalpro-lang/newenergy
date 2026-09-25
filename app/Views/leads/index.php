<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="catalog-toolbar">
        <h1 class="section-title" style="margin:0">Leads</h1>
        <div style="display:flex;gap:8px">
            <a href="/leads/extensoes" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Extensões de prazo</a>
            <?php if (in_array($user['role'], ['admin', 'gerente'], true)): ?>
                <a href="/leads/roteamento" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Roteamento</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-box" style="margin-bottom:24px">
        <h3 style="margin-top:0">Novo lead (cadastro manual)</h3>
        <form method="post" action="/leads" class="form-grid">
            <?= Csrf::field() ?>
            <div><label>Nome</label><input type="text" name="name" required></div>
            <div><label>Telefone</label><input type="text" name="phone"></div>
            <div><label>E-mail</label><input type="email" name="email"></div>
            <div><label>Cidade</label><input type="text" name="city"></div>
            <div class="span-4"><button type="submit" class="btn btn-accent">Adicionar lead</button></div>
        </form>
    </div>

    <table class="simple">
        <thead><tr><th>Nome</th><th>Contato</th><th>Cidade</th><th>Responsável</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
            <?php foreach ($leads as $lead): ?>
                <tr>
                    <td><a href="/leads/<?= $lead['id'] ?>"><?= View::e($lead['name']) ?></a></td>
                    <td><?= View::e($lead['phone']) ?> <?= View::e($lead['email']) ?></td>
                    <td><?= View::e($lead['city']) ?></td>
                    <td><?= View::e($lead['assigned_to_name'] ?? '—') ?></td>
                    <td><span class="badge-status <?= View::e($lead['status']) ?>"><?= View::e($lead['status']) ?></span></td>
                    <td><?= View::e($lead['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($leads)): ?>
                <tr><td colspan="6">Nenhum lead ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
