<?php

use App\Core\Csrf;
use App\Core\View;

$old = $old ?? [];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro — SB New Energy</title>
    <link rel="stylesheet" href="<?= View::asset('/assets/css/app.css') ?>">
</head>
<body class="auth-page">
    <form class="auth-card" method="post" action="/cadastro" style="width:420px">
        <h1>Criar conta</h1>
        <p class="subtitle">Cadastro de licenciados SB New Energy</p>

        <?php if (!empty($error)): ?>
            <p class="error"><?= View::e($error) ?></p>
        <?php endif; ?>

        <?= Csrf::field() ?>

        <label for="name">Seu nome</label>
        <input type="text" id="name" name="name" value="<?= View::e($old['name'] ?? '') ?>" required>

        <label for="company_name">Empresa</label>
        <input type="text" id="company_name" name="company_name" value="<?= View::e($old['company_name'] ?? '') ?>" required>

        <label for="document">CNPJ</label>
        <input type="text" id="document" name="document" value="<?= View::e($old['document'] ?? '') ?>">

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= View::e($old['email'] ?? '') ?>" required>

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" minlength="6" required>

        <button type="submit" class="btn btn-accent">Criar conta</button>

        <p class="switch">Já tem conta? <a href="/login">Entrar</a></p>
    </form>
</body>
</html>
