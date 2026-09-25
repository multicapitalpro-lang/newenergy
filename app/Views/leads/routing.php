<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/leads">Leads</a> &rsaquo; Roteamento</div>
    <h1 class="section-title">Roteamento de leads</h1>

    <?php if (!empty($_GET['salvo'])): ?>
        <p class="success">Configuração salva.</p>
    <?php endif; ?>

    <div class="card-box" style="margin-bottom:24px;max-width:520px">
        <p style="color:var(--muted);margin-top:0">
            Todo lead novo é distribuído automaticamente: primeiro escolhe o licenciado ativo
            que ficou mais tempo sem receber lead, depois o vendedor/gestor dentro dele que
            ficou mais tempo sem receber. Se não houver licenciado ativo, usa o responsável
            abaixo.
        </p>
        <form method="post" action="/leads/roteamento" class="stack">
            <?= Csrf::field() ?>
            <label>Responsável de fallback (sem licenciado elegível)</label>
            <select name="fallback_user_id">
                <option value="">— nenhum (lead fica sem responsável) —</option>
                <?php foreach ($licenciados as $l): ?>
                    <option value="<?= $l['id'] ?>" <?= (int) $fallbackUserId === (int) $l['id'] ? 'selected' : '' ?>><?= View::e($l['name']) ?> — <?= View::e($l['company_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-accent" style="margin-top:8px">Salvar</button>
        </form>
    </div>
</div>
