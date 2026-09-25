<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Minha Equipe</h1>

    <div class="card-box" style="margin-bottom:24px">
        <h3 style="margin-top:0">Novo vendedor</h3>
        <form method="post" action="/minha-equipe" class="form-grid">
            <?= Csrf::field() ?>
            <div>
                <label for="name">Nome</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="phone">Telefone</label>
                <input type="text" id="phone" name="phone">
            </div>
            <div>
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>
            <div class="span-4">
                <button type="submit" class="btn btn-accent">Adicionar vendedor</button>
            </div>
        </form>
    </div>

    <table class="simple">
        <thead>
            <tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php foreach ($team as $s): ?>
                <tr>
                    <td><?= View::e($s['name']) ?></td>
                    <td><?= View::e($s['email']) ?></td>
                    <td><?= View::e($s['phone']) ?></td>
                    <td><?= View::e($s['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($team)): ?>
                <tr><td colspan="4">Nenhum vendedor cadastrado ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
