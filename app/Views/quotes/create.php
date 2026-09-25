<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/orcamentos">Orçamentos</a> &rsaquo; Novo</div>
    <h1 class="section-title">Novo orçamento</h1>

    <p style="color:var(--muted)">Seu limite de desconto sem aprovação: <strong><?= number_format($discountLimit, 0) ?>%</strong>. Acima disso, o orçamento vai pra liberação de preço.</p>

    <form method="post" action="/orcamentos" class="card-box">
        <?= Csrf::field() ?>

        <label>Cliente</label>
        <select name="client_id" required>
            <option value="">Selecione</option>
            <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (string) $preselectedClientId === (string) $c['id'] ? 'selected' : '' ?>><?= View::e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <h3 style="margin-top:20px">Itens</h3>
        <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="form-grid" style="margin-bottom:10px">
                <div class="span-2">
                    <label>Produto</label>
                    <select name="product_id[]">
                        <option value="">—</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= View::e($p['name']) ?> (<?= View::money((int) $p['sell_price_cents']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Quantidade</label>
                    <input type="number" name="quantity[]" min="1" value="<?= $i === 0 ? 1 : '' ?>">
                </div>
            </div>
        <?php endfor; ?>

        <label>Desconto (%)</label>
        <input type="text" name="discount_percent" value="0" style="max-width:120px">

        <button type="submit" class="btn btn-accent" style="margin-top:16px">Criar orçamento</button>
    </form>
</div>
