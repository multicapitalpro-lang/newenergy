<?php

use App\Core\Csrf;
use App\Core\Roles;
use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Configuração de pagamentos</h1>
    <p style="color:var(--muted);margin-top:-10px">Formas de financiamento oferecidas ao cliente final (ex: cartão parcelado, financiamento bancário).</p>

    <?php if (in_array($user['role'], [Roles::ADMIN, Roles::GERENTE], true)): ?>
        <div class="card-box" style="margin-bottom:24px">
            <h3 style="margin-top:0">Nova forma de pagamento</h3>
            <form method="post" action="/config-pagamentos" class="form-grid">
                <?= Csrf::field() ?>
                <div><label>Nome</label><input type="text" name="name" placeholder="ex: BTG - Cartão" required></div>
                <div><label>Parcelas máx.</label><input type="number" name="max_installments" value="1" min="1"></div>
                <div><label>Taxa de juros (%)</label><input type="text" name="interest_rate_percent" value="0"></div>
                <div><label>Descrição</label><input type="text" name="description"></div>
                <div class="span-4"><button type="submit" class="btn btn-accent">Adicionar</button></div>
            </form>
        </div>
    <?php endif; ?>

    <table class="simple">
        <thead><tr><th>Nome</th><th>Descrição</th><th>Parcelas</th><th>Juros</th><?php if (in_array($user['role'], [Roles::ADMIN, Roles::GERENTE], true)): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
            <?php foreach ($methods as $m): ?>
                <tr>
                    <td><?= View::e($m['name']) ?></td>
                    <td><?= View::e($m['description']) ?></td>
                    <td><?= (int) $m['max_installments'] ?>x</td>
                    <td><?= $m['interest_rate_percent'] > 0 ? number_format($m['interest_rate_percent'], 2) . '%' : 'Sem juros' ?></td>
                    <?php if (in_array($user['role'], [Roles::ADMIN, Roles::GERENTE], true)): ?>
                        <td>
                            <form method="post" action="/config-pagamentos/<?= $m['id'] ?>/excluir" onsubmit="return confirm('Remover?')">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn-link-danger">Remover</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($methods)): ?>
                <tr><td colspan="5">Nenhuma forma de pagamento cadastrada ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
