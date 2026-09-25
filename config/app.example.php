<?php

return [
    'app_name' => 'SB New Energy',
    'env' => 'local',
    'url' => 'http://localhost:8001',

    // driver: 'sqlite' (dev local, sem instalar nada) ou 'mysql' (Hostinger, quando tivermos)
    'db' => [
        'driver' => 'sqlite',
        'sqlite_path' => BASE_PATH . '/database/newenergy.sqlite',
        // usados só quando driver = mysql
        'host' => '127.0.0.1',
        'name' => 'newenergy',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],

    'session_name' => 'newenergy_session',

    // Markup padrão da SB New Energy sobre o preço de distribuidor da Viva Bess,
    // quando o produto não tem um markup próprio definido. Valor de exemplo tirado
    // da reunião de kickoff (equipamento de ~19k pra distribuidor, ~25k pro integrador).
    'default_markup_percent' => 30,
];
