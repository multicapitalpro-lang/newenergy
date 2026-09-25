<?php

use App\Core\View;

$subtotal = array_sum(array_map(fn ($i) => $i['unit_price_cents'] * $i['quantity'], $items));
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta Comercial #<?= $quote['id'] ?> — SB New Energy</title>
    <style>
        body { font-family: system-ui, sans-serif; color: #1c2b3a; max-width: 800px; margin: 0 auto; padding: 40px 24px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #0d3b66; padding-bottom: 16px; margin-bottom: 24px; }
        .header .brand { font-size: 1.4rem; font-weight: 800; color: #0d3b66; }
        .header .brand span { color: #f4a300; }
        .meta { color: #767a80; font-size: 0.85rem; }
        h1 { font-size: 1.3rem; color: #0d3b66; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e4e6e8; font-size: 0.9rem; }
        th { background: #f5f6f8; text-transform: uppercase; font-size: 0.75rem; color: #767a80; }
        .totals { text-align: right; margin-top: 12px; }
        .totals .final { font-size: 1.4rem; font-weight: 800; color: #0d3b66; }
        .terms { margin-top: 32px; padding: 16px; background: #f5f6f8; border-radius: 6px; font-size: 0.8rem; color: #4a4b4e; }
        .print-btn { margin-top: 24px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">SB NEW<span>ENERGY</span></div>
        <div class="meta">Proposta #<?= $quote['id'] ?><br><?= View::e($quote['created_at']) ?></div>
    </div>

    <h1>Proposta Comercial</h1>
    <p><strong>Cliente:</strong> <?= View::e($quote['client_name']) ?></p>
    <p><strong>Consultor responsável:</strong> <?= View::e($quote['seller_name']) ?></p>

    <table>
        <thead><tr><th>Produto</th><th>Qtd.</th><th>Preço unit.</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= View::e($item['product_name']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= View::money((int) $item['unit_price_cents']) ?></td>
                    <td><?= View::money((int) ($item['unit_price_cents'] * $item['quantity'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <?php if ($quote['discount_percent'] > 0): ?>
            <p>Subtotal: <?= View::money((int) $subtotal) ?> — Desconto: <?= number_format($quote['discount_percent'], 0) ?>%</p>
        <?php endif; ?>
        <p class="final">Total: <?= View::money((int) $quote['total_cents']) ?></p>
    </div>

    <div class="terms">
        Proposta válida por 7 dias. Valores sujeitos a confirmação de disponibilidade de estoque
        no momento do fechamento. Condições de pagamento e financiamento a combinar com o
        consultor responsável.
    </div>

    <button class="btn print-btn" onclick="window.print()" style="padding:10px 20px;background:#f4a300;border:none;border-radius:6px;font-weight:700;cursor:pointer">Imprimir / Salvar PDF</button>
</body>
</html>
