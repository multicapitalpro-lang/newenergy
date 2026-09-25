<?php

use App\Core\Csrf;
use App\Core\View;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — SB New Energy</title>
    <link rel="stylesheet" href="<?= View::asset('/assets/css/app.css') ?>">
</head>
<body class="auth-page">
    <form class="auth-card" method="post" action="/login">
        <h1>SB New Energy</h1>
        <p class="subtitle">Painel de licenciados e vendedores</p>

        <?php if (!empty($error)): ?>
            <p class="error"><?= View::e($error) ?></p>
        <?php endif; ?>

        <?= Csrf::field() ?>
        <input type="hidden" name="next" value="<?= View::e($next ?? '/') ?>">

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required autofocus>

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn-accent">Entrar</button>

        <p class="switch">Ainda não tem conta? <a href="/cadastro">Cadastre-se</a></p>
        <p class="switch"><a href="/">&larr; Voltar pra loja</a></p>
    </form>
</body>
</html>
