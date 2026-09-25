<?php

use App\Core\Csrf;
use App\Core\View;
?>
<div class="wrap" style="max-width:560px">
    <h1 class="section-title">Fale com um especialista</h1>
    <p style="color:var(--muted)">Conte um pouco sobre o seu projeto que um dos nossos licenciados entra em contato.</p>

    <?php if (!empty($_GET['enviado'])): ?>
        <p class="success">Recebemos seu contato! Em breve alguém da nossa rede fala com você.</p>
    <?php else: ?>
        <form method="post" action="/contato" class="stack card-box">
            <?= Csrf::field() ?>
            <label>Nome</label>
            <input type="text" name="name" required>
            <label>Telefone</label>
            <input type="text" name="phone">
            <label>E-mail</label>
            <input type="email" name="email">
            <label>Cidade</label>
            <input type="text" name="city">
            <label>Como podemos ajudar?</label>
            <textarea name="message" rows="3"></textarea>
            <button type="submit" class="btn btn-accent" style="margin-top:8px">Enviar</button>
        </form>
    <?php endif; ?>
</div>
