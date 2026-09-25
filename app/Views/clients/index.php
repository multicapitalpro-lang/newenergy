<?php

use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Clientes</h1>

    <table class="simple">
        <thead><tr><th>Nome</th><th>Contato</th><th>Cidade</th><th>Vendedor</th><th>Data</th></tr></thead>
        <tbody>
            <?php foreach ($clients as $c): ?>
                <tr>
                    <td><a href="/clientes/<?= $c['id'] ?>"><?= View::e($c['name']) ?></a></td>
                    <td><?= View::e($c['phone']) ?> <?= View::e($c['email']) ?></td>
                    <td><?= View::e($c['city']) ?></td>
                    <td><?= View::e($c['seller_name']) ?></td>
                    <td><?= View::e($c['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($clients)): ?>
                <tr><td colspan="5">Nenhum cliente ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
