<?php

use App\Core\View;

$status = $user['onboarding_status'];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro em análise — SB New Energy</title>
    <link rel="stylesheet" href="<?= View::asset('/assets/css/app.css') ?>">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1>SB New Energy</h1>

        <?php if ($status === 'reprovado'): ?>
            <p class="subtitle">Cadastro não aprovado</p>
            <p>Seu cadastro como licenciado não foi aprovado.</p>
            <?php if (!empty($user['onboarding_rejection_reason'])): ?>
                <p><strong>Motivo:</strong> <?= View::e($user['onboarding_rejection_reason']) ?></p>
            <?php endif; ?>
            <p>Fale com a equipe SB New Energy pra mais informações.</p>
        <?php else: ?>
            <p class="subtitle">Cadastro em análise</p>
            <p>Recebemos seu cadastro como licenciado. Alguém da nossa equipe vai revisar e aprovar em breve — você recebe acesso ao painel assim que isso acontecer.</p>
        <?php endif; ?>

        <p class="switch"><a href="/logout">Sair</a></p>
    </div>
</body>
</html>
