<?php

use App\Core\Csrf;
use App\Core\Roles;
use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Material de venda</h1>

    <?php if (in_array($user['role'], [Roles::ADMIN, Roles::GERENTE], true)): ?>
        <div class="card-box" style="margin-bottom:24px">
            <h3 style="margin-top:0">Novo material</h3>
            <form method="post" action="/material-de-venda" enctype="multipart/form-data" class="form-grid">
                <?= Csrf::field() ?>
                <div class="span-2"><label>Título</label><input type="text" name="title" required></div>
                <div class="span-2"><label>Descrição</label><input type="text" name="description"></div>
                <div class="span-2"><label>Arquivo</label><input type="file" name="file"></div>
                <div class="span-2"><label>Ou link</label><input type="text" name="link" placeholder="https://..."></div>
                <div class="span-4"><button type="submit" class="btn btn-accent">Adicionar</button></div>
            </form>
        </div>
    <?php endif; ?>

    <div class="grid">
        <?php foreach ($materials as $m): ?>
            <div class="card-box">
                <h3 style="margin-top:0"><?= View::e($m['title']) ?></h3>
                <p style="color:var(--muted)"><?= View::e($m['description']) ?></p>
                <?php if ($m['file_path']): ?>
                    <a href="<?= View::e($m['file_path']) ?>" class="btn btn-dark" target="_blank">Baixar arquivo</a>
                <?php elseif ($m['link']): ?>
                    <a href="<?= View::e($m['link']) ?>" class="btn btn-dark" target="_blank">Acessar link</a>
                <?php endif; ?>
                <?php if (in_array($user['role'], [Roles::ADMIN, Roles::GERENTE], true)): ?>
                    <form method="post" action="/material-de-venda/<?= $m['id'] ?>/excluir" onsubmit="return confirm('Remover?')" style="margin-top:8px">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn-link-danger">Remover</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($materials)): ?>
            <p>Nenhum material disponível ainda.</p>
        <?php endif; ?>
    </div>
</div>
