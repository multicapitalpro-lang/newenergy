<?php

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/app.php';

$config = require BASE_PATH . '/config/app.php';
$sqlitePath = $config['db']['sqlite_path'];

$isNew = !file_exists($sqlitePath);
$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$schema = file_get_contents(BASE_PATH . '/database/schema.sql');
$pdo->exec($schema);

echo ($isNew ? "Banco criado" : "Schema atualizado") . " em {$sqlitePath}\n";

// Seed: um exemplo de cada papel da hierarquia completa, só se vazio.
$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($userCount === 0) {
    $hash = password_hash('trocar123', PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');

    $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, role, company_name, created_at)
         VALUES (?, ?, ?, 'admin', 'SB New Energy', ?)"
    )->execute(['Admin New Energy', 'admin@newenergy.local', $hash, $now]);

    $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, role, company_name, created_at)
         VALUES (?, ?, ?, 'gerente', 'SB New Energy', ?)"
    )->execute(['Gustavo Gerente', 'gerente@newenergy.local', $hash, $now]);
    $gerenteId = (int) $pdo->lastInsertId();

    $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, role, manager_id, company_name, created_at)
         VALUES (?, ?, ?, 'supervisor', ?, 'SB New Energy', ?)"
    )->execute(['Sandra Supervisora', 'supervisor@newenergy.local', $hash, $gerenteId, $now]);
    $supervisorId = (int) $pdo->lastInsertId();

    $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, role, supervisor_id, company_name, document, created_at)
         VALUES (?, ?, ?, 'licenciado', ?, ?, ?, ?)"
    )->execute(['Carlos Licenciado', 'licenciado@newenergy.local', $hash, $supervisorId, 'Licenciado Demo LTDA', '12.345.678/0001-00', $now]);
    $licenciadoId = (int) $pdo->lastInsertId();

    $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, role, manager_id, created_at)
         VALUES (?, ?, ?, 'gestor', ?, ?)"
    )->execute(['Gabriel Gestor', 'gestor@newenergy.local', $hash, $licenciadoId, $now]);

    $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, role, manager_id, created_at)
         VALUES (?, ?, ?, 'vendedor', ?, ?)"
    )->execute(['Ana Vendedora', 'vendedor@newenergy.local', $hash, $licenciadoId, $now]);

    echo "Usuários criados (todos / trocar123): admin, gerente, supervisor, licenciado, gestor, vendedor @newenergy.local\n";
}

// Catálogo espelhando os produtos da Viva Bess (cost_price_cents = preço de
// distribuidor de lá). Sem API entre os dois sistemas ainda -- atualização
// manual por enquanto (ver memória do projeto).
$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
if ($productCount === 0) {
    $products = [
        // sku, viva_bess_sku, name, category, short_description, cost_price_cents
        ['NE-ONE-5KWH', 'VB-ONE-5KWH', 'Only One 5kWh (All-in-One)', 'Residencial', 'Bateria residencial com inversor integrado — ideal pra backup e uso em horário de pico.', 1925000],
        ['NE-ONE-10KWH', 'VB-ONE-10KWH', 'Only One 10kWh (All-in-One)', 'Residencial', 'Versão de maior capacidade da linha All-in-One, pra residências de alto consumo.', 3450000],
        ['NE-COM-241', 'VB-COM-241', 'BESS 241 kWh', 'Comercial/Industrial', 'Solução de armazenamento pra pequenas indústrias e comércios — reduz conta de horário de pico.', 45800000],
        ['NE-COM-261', 'VB-COM-261', 'BESS 261 kWh', 'Comercial/Industrial', 'Capacidade extra pra operações com maior demanda de energia.', 49900000],
        ['NE-EV-CHARGER', 'VB-EV-CHARGER', 'Eletroposto + BESS (combo)', 'Eletropostos', 'Carregador de veículo elétrico com BESS de suporte — evita sobrecarga na rede local.', 68000000],
    ];

    foreach ($products as [$sku, $vivaBessSku, $name, $category, $desc, $costCents]) {
        $pdo->prepare(
            "INSERT INTO products (sku, viva_bess_sku, name, category, short_description, cost_price_cents, image_path, active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)"
        )->execute([$sku, $vivaBessSku, $name, $category, $desc, $costCents, '/uploads/products/' . $sku . '.png', date('Y-m-d H:i:s')]);
    }

    echo "Catálogo de exemplo (5 produtos, espelhando a Viva Bess) criado.\n";
}

echo "Pronto.\n";
