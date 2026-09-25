-- Schema inicial do painel SB New Energy. Compatível com SQLite (dev local) e
-- pensado pra migrar sem drama pra MySQL quando formos pra Hostinger.
-- Estrutura de papéis/hierarquia inspirada no painel EcoDiffusore (mesmo dono):
-- 5 papéis de parceiro (Gerente/Supervisor/Licenciado/Gestor/Vendedor) + admin,
-- com duas hierarquias independentes -- ver App\Core\Roles para a explicação
-- completa de cada uma.

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'vendedor', -- admin | gerente | supervisor | licenciado | gestor | vendedor
    manager_id INTEGER REFERENCES users(id), -- gestor/vendedor -> licenciado dono; supervisor -> gerente que cadastrou
    supervisor_id INTEGER REFERENCES users(id), -- licenciado -> supervisor designado pra apoiar ele (atribuição separada do manager_id)
    company_name TEXT,
    document TEXT, -- CNPJ ou CPF
    phone TEXT,
    status TEXT NOT NULL DEFAULT 'active', -- active | inactive
    created_at TEXT NOT NULL
);

-- Catálogo da New Energy. Espelha o catálogo da Viva Bess (cost_price_cents é o
-- preço de distribuidor de lá) e aplica um markup -- é esse markup que é o
-- ganho da SB New Energy. Sem integração via API com a Viva Bess ainda
-- (ver memória do projeto); por enquanto é atualizado manualmente.
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sku TEXT NOT NULL UNIQUE,
    viva_bess_sku TEXT, -- SKU correspondente no catálogo da Viva Bess, pra rastreio manual
    name TEXT NOT NULL,
    category TEXT,
    short_description TEXT,
    description TEXT,
    cost_price_cents INTEGER NOT NULL, -- preço de distribuidor Viva Bess
    markup_percent REAL, -- se nulo, usa default_markup_percent do config
    datasheet_path TEXT,
    image_path TEXT,
    active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
);

-- Cliente final (integrador) de um vendedor/licenciado. Cadastro simples --
-- sem portal próprio ainda, diferente do ClientPortal do EcoDiffusore.
CREATE TABLE IF NOT EXISTS clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    seller_id INTEGER NOT NULL REFERENCES users(id),
    name TEXT NOT NULL,
    document TEXT,
    email TEXT,
    phone TEXT,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL REFERENCES clients(id),
    seller_id INTEGER NOT NULL REFERENCES users(id), -- quem fechou a venda (licenciado ou vendedor)
    status TEXT NOT NULL DEFAULT 'pendente', -- pendente | confirmado | entregue | cancelado
    total_cents INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL REFERENCES orders(id),
    product_id INTEGER NOT NULL REFERENCES products(id),
    quantity INTEGER NOT NULL,
    unit_price_cents INTEGER NOT NULL, -- preço da New Energy (com markup) congelado no momento do pedido
    subtotal_cents INTEGER NOT NULL
);
