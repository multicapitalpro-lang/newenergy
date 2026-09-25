<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Tabela de preços</h1>
    <p style="color:var(--muted);margin-top:-10px">Custo de distribuidor (Viva Bess), markup da New Energy e capacidade (usada pela calculadora).</p>

    <?php if (!empty($_GET['salvo'])): ?>
        <p class="success">Preço atualizado.</p>
    <?php endif; ?>

    <table class="simple">
        <thead><tr><th>Produto</th><th>Custo (R$)</th><th>Markup (%)</th><th>Capacidade (kWh)</th><th>Preço final</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <?php $formId = 'pricing-form-' . $p['id']; ?>
                <tr>
                    <td><?= View::e($p['name']) ?></td>
                    <td><input type="text" name="cost_price" form="<?= $formId ?>" value="<?= number_format($p['cost_price_cents'] / 100, 2, '.', '') ?>" style="max-width:100px"></td>
                    <td><input type="text" name="markup_percent" form="<?= $formId ?>" value="<?= $p['markup_percent'] !== null ? $p['markup_percent'] : '' ?>" placeholder="padrão" style="max-width:80px"></td>
                    <td><input type="text" name="capacity_kwh" form="<?= $formId ?>" value="<?= $p['capacity_kwh'] !== null ? $p['capacity_kwh'] : '' ?>" style="max-width:80px"></td>
                    <td><?= View::money((int) $p['sell_price_cents']) ?></td>
                    <td>
                        <form id="<?= $formId ?>" method="post" action="/tabela-precos/<?= $p['id'] ?>" style="display:inline">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy);padding:6px 12px">Salvar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
