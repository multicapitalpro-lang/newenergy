<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="catalog-toolbar">
        <h1 class="section-title" style="margin:0">Expansão do Licenciado</h1>
        <?php if ($stats['pendentes'] > 0): ?>
            <a href="/licenciados/aprovacoes" class="btn btn-dark">Aprovações pendentes (<?= $stats['pendentes'] ?>)</a>
        <?php endif; ?>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total'] ?></div>
            <div class="stat-label">Total de licenciados</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['ativos'] ?></div>
            <div class="stat-label">Ativos</div>
        </div>
        <div class="stat-card <?= $stats['pendentes'] > 0 ? 'attention' : '' ?>">
            <div class="stat-value"><?= $stats['pendentes'] ?></div>
            <div class="stat-label">Aguardando aprovação</div>
        </div>
        <div class="stat-card <?= $stats['sem_supervisor'] > 0 ? 'attention' : '' ?>">
            <div class="stat-value"><?= $stats['sem_supervisor'] ?></div>
            <div class="stat-label">Sem supervisor</div>
        </div>
    </div>

    <table class="simple">
        <thead>
            <tr>
                <th>Licenciado</th>
                <th>Empresa</th>
                <th>Status</th>
                <th>Equipe</th>
                <th>Pedidos</th>
                <th>Faturamento</th>
                <th>Supervisor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr class="<?= empty($r['supervisor_id']) ? 'row-attention' : '' ?>">
                    <td><?= View::e($r['name']) ?></td>
                    <td><?= View::e($r['company_name']) ?></td>
                    <td><span class="badge-status <?= View::e($r['onboarding_status']) ?>"><?= View::e(str_replace('_', ' ', $r['onboarding_status'])) ?></span></td>
                    <td><?= (int) $r['team_size'] ?></td>
                    <td><?= (int) $r['orders_count'] ?></td>
                    <td><?= View::money((int) $r['revenue_cents']) ?></td>
                    <td>
                        <form method="post" action="/minha-equipe/atribuir-supervisor" style="display:flex;gap:6px">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="licenciado_id" value="<?= $r['id'] ?>">
                            <select name="supervisor_id" onchange="this.form.submit()">
                                <option value="">— nenhum —</option>
                                <?php foreach ($supervisores as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= (int) $r['supervisor_id'] === (int) $s['id'] ? 'selected' : '' ?>><?= View::e($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7">Nenhum licenciado cadastrado ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
