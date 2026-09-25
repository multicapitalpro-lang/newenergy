#!/usr/bin/env bash
# Sobe o painel SB New Energy localmente. Cria/atualiza o banco SQLite e
# inicia o servidor embutido do PHP em http://localhost:8001
set -e

cd "$(dirname "$0")"

PHP="/c/tools/php/php.exe"

"$PHP" database/migrate.php
"$PHP" -S localhost:8001 -t public_html scripts/router.php
