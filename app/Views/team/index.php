<?php

use App\Core\Csrf;
use App\Core\Roles;
use App\Core\View;
?>
<div class="wrap">
    <h1 class="section-title">Minha Equipe</h1>

    <?php if ($view === 'gerente'): ?>

        <div class="card-box" style="margin-bottom:24px">
            <h3 style="margin-top:0">Novo Supervisor</h3>
            <p style="color:var(--muted);margin-top:-6px">Supervisor é suporte nacional — dá apoio aos licenciados que forem atribuídos a ele.</p>
            <form method="post" action="/minha-equipe/supervisores" class="form-grid">
                <?= Csrf::field() ?>
                <div><label>Nome</label><input type="text" name="name" required></div>
                <div><label>E-mail</label><input type="email" name="email" required></div>
                <div><label>Telefone</label><input type="text" name="phone"></div>
                <div><label>Senha</label><input type="password" name="password" minlength="6" required></div>
                <div class="span-4"><button type="submit" class="btn btn-accent">Adicionar supervisor</button></div>
            </form>
        </div>

        <div class="card-box" style="margin-bottom:24px">
            <h3 style="margin-top:0">Atribuir Licenciado a um Supervisor</h3>
            <form method="post" action="/minha-equipe/atribuir-supervisor" class="form-grid">
                <?= Csrf::field() ?>
                <div class="span-2">
                    <label>Licenciado</label>
                    <select name="licenciado_id" required>
                        <?php foreach ($licenciados as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= View::e($l['name']) ?> — <?= View::e($l['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="span-2">
                    <label>Supervisor</label>
                    <select name="supervisor_id">
                        <option value="">— nenhum —</option>
                        <?php foreach ($supervisores as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= View::e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="span-4"><button type="submit" class="btn btn-dark">Salvar atribuição</button></div>
            </form>
        </div>

        <h3>Supervisores</h3>
        <table class="simple">
            <thead><tr><th>Nome</th><th>E-mail</th><th>Licenciados sob ele</th></tr></thead>
            <tbody>
                <?php foreach ($supervisores as $s): ?>
                    <tr>
                        <td><?= View::e($s['name']) ?></td>
                        <td><?= View::e($s['email']) ?></td>
                        <td><?= count(array_filter($licenciados, fn ($l) => (int) $l['supervisor_id'] === (int) $s['id'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($supervisores)): ?>
                    <tr><td colspan="3">Nenhum supervisor cadastrado ainda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 style="margin-top:32px">Licenciados</h3>
        <table class="simple">
            <thead><tr><th>Nome</th><th>Empresa</th><th>Supervisor</th></tr></thead>
            <tbody>
                <?php foreach ($licenciados as $l): ?>
                    <?php $sup = array_values(array_filter($supervisores, fn ($s) => (int) $s['id'] === (int) $l['supervisor_id']))[0] ?? null; ?>
                    <tr>
                        <td><?= View::e($l['name']) ?></td>
                        <td><?= View::e($l['company_name']) ?></td>
                        <td><?= $sup ? View::e($sup['name']) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($licenciados)): ?>
                    <tr><td colspan="3">Nenhum licenciado cadastrado ainda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($view === 'supervisor'): ?>

        <p style="color:var(--muted)">Você acompanha os licenciados abaixo (só visualização — quem cadastra é o próprio licenciado ou o gerente).</p>

        <?php foreach ($licenciados as $licenciado): ?>
            <div class="card-box" style="margin-bottom:20px">
                <h3 style="margin-top:0"><?= View::e($licenciado['name']) ?> — <?= View::e($licenciado['company_name']) ?></h3>
                <table class="simple">
                    <thead><tr><th>Nome</th><th>Papel</th><th>E-mail</th></tr></thead>
                    <tbody>
                        <?php foreach ($teams[$licenciado['id']] as $member): ?>
                            <tr>
                                <td><?= View::e($member['name']) ?></td>
                                <td><?= View::e(Roles::label($member['role'])) ?></td>
                                <td><?= View::e($member['email']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($teams[$licenciado['id']])): ?>
                            <tr><td colspan="3">Nenhum vendedor/gestor cadastrado ainda.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
        <?php if (empty($licenciados)): ?>
            <p>Nenhum licenciado atribuído a você ainda.</p>
        <?php endif; ?>

    <?php else: ?>

        <?php if ($canManage): ?>
            <div class="card-box" style="margin-bottom:24px">
                <h3 style="margin-top:0">Novo integrante</h3>
                <form method="post" action="/minha-equipe" class="form-grid">
                    <?= Csrf::field() ?>
                    <div><label>Nome</label><input type="text" name="name" required></div>
                    <div><label>E-mail</label><input type="email" name="email" required></div>
                    <div><label>Telefone</label><input type="text" name="phone"></div>
                    <div>
                        <label>Papel</label>
                        <select name="role">
                            <option value="vendedor">Vendedor</option>
                            <option value="gestor">Gestor</option>
                        </select>
                    </div>
                    <div class="span-2"><label>Senha</label><input type="password" name="password" minlength="6" required></div>
                    <div class="span-2" style="align-self:end"><button type="submit" class="btn btn-accent">Adicionar</button></div>
                </form>
            </div>
        <?php endif; ?>

        <table class="simple">
            <thead><tr><th>Nome</th><th>Papel</th><th>E-mail</th><th>Telefone</th></tr></thead>
            <tbody>
                <?php foreach ($team as $s): ?>
                    <tr>
                        <td><?= View::e($s['name']) ?></td>
                        <td><?= View::e(Roles::label($s['role'])) ?></td>
                        <td><?= View::e($s['email']) ?></td>
                        <td><?= View::e($s['phone']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($team)): ?>
                    <tr><td colspan="4">Nenhum integrante cadastrado ainda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>
