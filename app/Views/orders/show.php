<?php

use App\Core\Csrf;
use App\Core\Roles;
use App\Core\View;

$canManage = in_array($user['role'], Roles::MANAGEMENT, true);
?>
<div class="wrap">
    <div class="breadcrumb"><a href="/pedidos">Pedidos</a> &rsaquo; #<?= $order['id'] ?></div>

    <?php if (!empty($_GET['criado'])): ?>
        <p class="success">Pedido criado com sucesso!</p>
    <?php endif; ?>

    <h1 class="section-title">Pedido #<?= $order['id'] ?></h1>

    <div class="card-box" style="margin-bottom:24px">
        <p><strong>Cliente:</strong> <?= View::e($order['client_name']) ?></p>
        <p><strong>Vendedor:</strong> <?= View::e($order['seller_name']) ?></p>
        <p><strong>Status:</strong> <span class="badge-status <?= View::e($order['status']) ?>"><?= View::e($order['status']) ?></span></p>
        <p><strong>Total:</strong> <?= View::money((int) $order['total_cents']) ?></p>
    </div>

    <table class="simple" style="margin-bottom:24px">
        <thead>
            <tr><th>Produto</th><th>SKU</th><th>Quantidade</th><th>Preço unit.</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= View::e($item['product_name']) ?></td>
                    <td><?= View::e($item['sku']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= View::money((int) $item['unit_price_cents']) ?></td>
                    <td><?= View::money((int) $item['subtotal_cents']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="card-box" style="margin-bottom:24px">
        <h3 style="margin-top:0">Acompanhar a entrega</h3>
        <p>Status: <span class="badge-status <?= View::e($order['delivery_status']) ?>"><?= View::e(str_replace('_', ' ', $order['delivery_status'])) ?></span>
            <?php if ($order['tracking_code']): ?> — Rastreio: <strong><?= View::e($order['tracking_code']) ?></strong><?php endif; ?>
            <?php if ($order['delivered_at']): ?> — Entregue em <?= View::e($order['delivered_at']) ?><?php endif; ?>
        </p>
        <?php if ($canManage): ?>
            <form method="post" action="/pedidos/<?= $order['id'] ?>/entrega" class="form-grid">
                <?= Csrf::field() ?>
                <div><label>Código de rastreio</label><input type="text" name="tracking_code" value="<?= View::e($order['tracking_code']) ?>"></div>
                <div>
                    <label>Status</label>
                    <select name="delivery_status">
                        <option value="aguardando" <?= $order['delivery_status'] === 'aguardando' ? 'selected' : '' ?>>Aguardando</option>
                        <option value="em_transito" <?= $order['delivery_status'] === 'em_transito' ? 'selected' : '' ?>>Em trânsito</option>
                        <option value="entregue" <?= $order['delivery_status'] === 'entregue' ? 'selected' : '' ?>>Entregue</option>
                    </select>
                </div>
                <div style="align-self:end"><button type="submit" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Atualizar</button></div>
            </form>
        <?php endif; ?>
    </div>

    <div class="card-box" style="margin-bottom:24px">
        <h3 style="margin-top:0">Pós-venda de instalação</h3>
        <p>Status: <span class="badge-status <?= View::e($order['installation_status']) ?>"><?= View::e(str_replace('_', ' ', $order['installation_status'])) ?></span>
            <?php if ($order['installation_date']): ?> — Agendada pra <?= View::e($order['installation_date']) ?><?php endif; ?>
        </p>
        <?php if ($order['installation_notes']): ?><p><?= nl2br(View::e($order['installation_notes'])) ?></p><?php endif; ?>
        <?php if ($canManage): ?>
            <form method="post" action="/pedidos/<?= $order['id'] ?>/instalacao" class="form-grid">
                <?= Csrf::field() ?>
                <div>
                    <label>Status</label>
                    <select name="installation_status">
                        <option value="nao_iniciada" <?= $order['installation_status'] === 'nao_iniciada' ? 'selected' : '' ?>>Não iniciada</option>
                        <option value="agendada" <?= $order['installation_status'] === 'agendada' ? 'selected' : '' ?>>Agendada</option>
                        <option value="concluida" <?= $order['installation_status'] === 'concluida' ? 'selected' : '' ?>>Concluída</option>
                    </select>
                </div>
                <div><label>Data</label><input type="date" name="installation_date" value="<?= View::e($order['installation_date']) ?>"></div>
                <div class="span-2"><label>Observações</label><input type="text" name="installation_notes" value="<?= View::e($order['installation_notes']) ?>"></div>
                <div class="span-4"><button type="submit" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy)">Atualizar</button></div>
            </form>
        <?php endif; ?>
    </div>

    <div class="card-box">
        <h3 style="margin-top:0">Documentos</h3>
        <table class="simple" style="margin-bottom:16px">
            <thead><tr><th>Nome</th><th>Enviado por</th><th>Status</th><?php if ($canManage): ?><th></th><?php endif; ?></tr></thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td><?= $doc['file_path'] ? '<a href="' . View::e($doc['file_path']) . '" target="_blank">' . View::e($doc['name']) . '</a>' : View::e($doc['name']) ?></td>
                        <td><?= View::e($doc['uploaded_by_name']) ?></td>
                        <td><span class="badge-status <?= View::e($doc['status']) ?>"><?= View::e($doc['status']) ?></span></td>
                        <?php if ($canManage): ?>
                            <td>
                                <?php if ($doc['status'] === 'pendente'): ?>
                                    <form method="post" action="/pedidos/<?= $order['id'] ?>/documentos/<?= $doc['id'] ?>/decidir" style="display:inline">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="decision" value="aprovado">
                                        <button type="submit" class="btn-link-danger" style="color:#0f5132">Aprovar</button>
                                    </form>
                                    <form method="post" action="/pedidos/<?= $order['id'] ?>/documentos/<?= $doc['id'] ?>/decidir" style="display:inline">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="decision" value="recusado">
                                        <button type="submit" class="btn-link-danger">Recusar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="<?= $canManage ? 4 : 3 ?>">Nenhum documento enviado ainda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form method="post" action="/pedidos/<?= $order['id'] ?>/documentos" enctype="multipart/form-data" class="form-grid">
            <?= Csrf::field() ?>
            <div class="span-2"><label>Nome do documento</label><input type="text" name="name" placeholder="ex: Contrato assinado" required></div>
            <div class="span-2"><label>Arquivo</label><input type="file" name="file"></div>
            <div class="span-4"><button type="submit" class="btn btn-accent">Enviar documento</button></div>
        </form>
    </div>
</div>
