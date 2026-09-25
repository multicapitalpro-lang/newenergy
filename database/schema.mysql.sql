-- Versão MySQL do schema.sql (SQLite), pra rodar na Hostinger.
-- Mesmos nomes de coluna, sem FK constraint reforçada (INT simples) --
-- mesmo padrão usado no schema.mysql.sql do Viva Bess.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'vendedor',
    manager_id INT,
    supervisor_id INT,
    company_name VARCHAR(255),
    document VARCHAR(32),
    phone VARCHAR(32),
    status VARCHAR(16) NOT NULL DEFAULT 'active',
    -- Aprovação de cadastro (só licenciado): aguardando_aprovacao | ativo | reprovado
    onboarding_status VARCHAR(24) NOT NULL DEFAULT 'ativo',
    onboarding_rejection_reason TEXT,
    -- Aprovação de contrato (só gestor/vendedor): pendente_envio | aguardando_aprovacao | aprovado | reprovado
    contract_status VARCHAR(24) NOT NULL DEFAULT 'aprovado',
    contract_path TEXT,
    contract_rejection_reason TEXT,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(64) NOT NULL UNIQUE,
    viva_bess_sku VARCHAR(64),
    name VARCHAR(255) NOT NULL,
    category VARCHAR(128),
    short_description VARCHAR(500),
    description TEXT,
    cost_price_cents INT NOT NULL,
    markup_percent DECIMAL(6,2),
    datasheet_path VARCHAR(255),
    image_path VARCHAR(255),
    capacity_kwh DECIMAL(10,2),
    active TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    document VARCHAR(32),
    email VARCHAR(255),
    phone VARCHAR(32),
    city VARCHAR(255),
    state VARCHAR(2),
    address VARCHAR(255),
    converted_from_lead_id INT,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS client_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(32),
    email VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(2),
    message TEXT,
    source VARCHAR(32) NOT NULL DEFAULT 'site',
    status VARCHAR(16) NOT NULL DEFAULT 'novo',
    assigned_to_user_id INT,
    assigned_at DATETIME,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lead_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lead_extension_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    requested_by INT NOT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'pendente',
    decided_by INT,
    decided_at DATETIME,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lead_routing_settings (
    id INT PRIMARY KEY,
    fallback_user_id INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    seller_id INT NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'aberto',
    discount_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
    total_cents INT NOT NULL DEFAULT 0,
    converted_order_id INT,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quote_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quote_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price_cents INT NOT NULL,
    subtotal_cents INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    approvable_type VARCHAR(32) NOT NULL DEFAULT 'quote',
    approvable_id INT NOT NULL,
    requested_discount_pct DECIMAL(6,2) NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'pendente',
    requested_by INT NOT NULL,
    decided_by INT,
    decided_at DATETIME,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    seller_id INT NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'pendente',
    total_cents INT NOT NULL DEFAULT 0,
    tracking_code VARCHAR(128),
    delivery_status VARCHAR(16) NOT NULL DEFAULT 'aguardando',
    delivered_at DATETIME,
    installation_status VARCHAR(16) NOT NULL DEFAULT 'nao_iniciada',
    installation_date DATE,
    installation_notes TEXT,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price_cents INT NOT NULL,
    subtotal_cents INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255),
    status VARCHAR(16) NOT NULL DEFAULT 'pendente',
    decided_by INT,
    decided_at DATETIME,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sales_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(500),
    file_path VARCHAR(255),
    link VARCHAR(255),
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(500),
    max_installments INT NOT NULL DEFAULT 1,
    interest_rate_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
    active TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    period VARCHAR(7) NOT NULL,
    target_type VARCHAR(24) NOT NULL DEFAULT 'revenue',
    target_value DECIMAL(14,2) NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
