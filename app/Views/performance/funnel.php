<?php

$stages = [
    ['label' => 'Leads recebidos', 'value' => $funnel['leads']],
    ['label' => 'Leads contatados', 'value' => $funnel['leads_contatados']],
    ['label' => 'Clientes (convertidos)', 'value' => $funnel['clientes']],
    ['label' => 'Orçamentos feitos', 'value' => $funnel['orcamentos']],
    ['label' => 'Orçamentos aprovados', 'value' => $funnel['orcamentos_aprovados']],
    ['label' => 'Pedidos fechados', 'value' => $funnel['pedidos']],
];
$max = max(array_column($stages, 'value')) ?: 1;
?>
<div class="wrap">
    <h1 class="section-title">Funil de conversão</h1>

    <div class="card-box">
        <?php foreach ($stages as $i => $stage): ?>
            <?php
                $pct = round($stage['value'] / $max * 100);
                $prevValue = $i > 0 ? $stages[$i - 1]['value'] : null;
                $convRate = $prevValue ? round($stage['value'] / $prevValue * 100) : null;
            ?>
            <div style="margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:4px">
                    <span><?= htmlspecialchars($stage['label']) ?></span>
                    <span><strong><?= $stage['value'] ?></strong><?= $convRate !== null ? " ({$convRate}% do anterior)" : '' ?></span>
                </div>
                <div class="progress" style="height:22px">
                    <div class="progress-fill" style="width: <?= $pct ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
