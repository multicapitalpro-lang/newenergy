<?php

use App\Core\Csrf;
use App\Core\View;

$old = $old ?? [];
?>
<div class="wrap" style="max-width:640px">
    <h1 class="section-title">Calculadora de economia e dimensionamento</h1>
    <p style="color:var(--muted)">
        Uma prévia rápida de qual equipamento pode atender sua necessidade. Não substitui o
        dimensionamento de um engenheiro responsável antes da compra efetiva.
    </p>

    <?php if (!empty($error)): ?>
        <p class="error"><?= View::e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="/calculadora" class="card-box stack" style="margin-bottom:24px">
        <?= Csrf::field() ?>

        <label>Valor médio da sua conta de energia (R$/mês)</label>
        <input type="text" name="monthly_bill" placeholder="450,00" value="<?= View::e($old['monthly_bill'] ?? '') ?>" required>

        <label>Quantas horas você quer ficar com energia armazenada?</label>
        <input type="text" name="autonomy_hours" placeholder="4" value="<?= View::e($old['autonomy_hours'] ?? '') ?>" required>

        <label>Tipo de imóvel</label>
        <select name="property_type">
            <option value="residencial" <?= ($old['property_type'] ?? '') === 'residencial' ? 'selected' : '' ?>>Residencial</option>
            <option value="comercial" <?= ($old['property_type'] ?? '') === 'comercial' ? 'selected' : '' ?>>Comercial/Industrial</option>
        </select>

        <button type="submit" class="btn btn-accent" style="margin-top:8px">Calcular</button>
    </form>

    <?php if ($result): ?>
        <div class="card-box" style="margin-bottom:24px">
            <h3 style="margin-top:0">Resultado estimado</h3>
            <p>Consumo mensal estimado: <strong><?= number_format($result['monthly_kwh'], 0) ?> kWh</strong></p>
            <p>Capacidade de bateria recomendada: <strong><?= number_format($result['required_kwh'], 1) ?> kWh</strong></p>

            <?php if ($result['product']): ?>
                <div class="card-box" style="margin-top:16px;background:var(--bg)">
                    <span class="product-category"><?= View::e($result['product']['category']) ?></span>
                    <h3 style="margin:4px 0"><?= View::e($result['product']['name']) ?></h3>
                    <p style="color:var(--muted);margin:0 0 8px">Capacidade: <?= number_format($result['product']['capacity_kwh'], 1) ?> kWh</p>
                    <span class="price-main"><?= View::money((int) $result['product']['sell_price_cents']) ?></span>
                    <p><a href="/produtos/<?= $result['product']['id'] ?>" class="btn btn-dark" style="margin-top:8px">Ver produto</a></p>
                </div>
            <?php else: ?>
                <p>Nenhum produto do catálogo atende essa capacidade ainda — fale com um especialista.</p>
            <?php endif; ?>

            <p style="margin-top:16px;font-size:0.8rem;color:var(--muted)">
                Essa é uma estimativa pra você ter uma prévia. A instalação e a compra efetiva
                dependem da validação de um engenheiro responsável.
            </p>
            <a href="/contato" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Solicitar orçamento formal</a>
        </div>
    <?php endif; ?>
</div>
