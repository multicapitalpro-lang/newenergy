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
    -- Aprovação de cadastro (só licenciado): aguardando_aprovacao | ativo | reprovado
    onboarding_status TEXT NOT NULL DEFAULT 'ativo',
    onboarding_rejection_reason TEXT,
    -- Aprovação de contrato (só gestor/vendedor): pendente_envio | aguardando_aprovacao | aprovado | reprovado
    contract_status TEXT NOT NULL DEFAULT 'aprovado',
    contract_path TEXT,
    contract_rejection_reason TEXT,
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
    capacity_kwh REAL, -- capacidade de armazenamento, usada pela Calculadora pra recomendar produto
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
    city TEXT,
    state TEXT,
    address TEXT,
    converted_from_lead_id INTEGER REFERENCES leads(id),
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS client_notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL REFERENCES clients(id),
    user_id INTEGER NOT NULL REFERENCES users(id),
    note TEXT NOT NULL,
    created_at TEXT NOT NULL
);

-- CRM: Leads. Ver App\Core\Roles / LeadController -- roteamento simplificado
-- do GeoMatch do EcoDiffusore (sem geolocalização por enquanto: round robin
-- por licenciado menos servido recentemente, depois vendedor dentro dele).
CREATE TABLE IF NOT EXISTS leads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT,
    email TEXT,
    city TEXT,
    state TEXT,
    message TEXT,
    source TEXT NOT NULL DEFAULT 'site', -- site | manual | indicacao | whatsapp
    status TEXT NOT NULL DEFAULT 'novo', -- novo | contatado | convertido | descartado
    assigned_to_user_id INTEGER REFERENCES users(id),
    assigned_at TEXT,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS lead_notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lead_id INTEGER NOT NULL REFERENCES leads(id),
    user_id INTEGER NOT NULL REFERENCES users(id),
    note TEXT NOT NULL,
    created_at TEXT NOT NULL
);

-- Pedido de extensão de prazo pra trabalhar um lead antes de ser considerado
-- parado (a UI de "redistribuir leads parados" fica pra depois -- por ora
-- só o fluxo de solicitar/aprovar/recusar existe).
CREATE TABLE IF NOT EXISTS lead_extension_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lead_id INTEGER NOT NULL REFERENCES leads(id),
    requested_by INTEGER NOT NULL REFERENCES users(id),
    reason TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'pendente', -- pendente | aprovado | recusado
    decided_by INTEGER REFERENCES users(id),
    decided_at TEXT,
    created_at TEXT NOT NULL
);

-- Configuração de roteamento de leads (linha única, id=1). fallback_user_id é
-- pra quem vai o lead quando não há licenciado/vendedor ativo elegível.
CREATE TABLE IF NOT EXISTS lead_routing_settings (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    fallback_user_id INTEGER REFERENCES users(id)
);

-- Orçamento: Lead/Cliente -> itens com preço -> aprovado -> vira Pedido.
-- Mesma ideia do Quote/QuoteItem do EcoDiffusore.
CREATE TABLE IF NOT EXISTS quotes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL REFERENCES clients(id),
    seller_id INTEGER NOT NULL REFERENCES users(id),
    status TEXT NOT NULL DEFAULT 'aberto', -- aberto | aguardando_aprovacao | aprovado | recusado | convertido
    discount_percent REAL NOT NULL DEFAULT 0,
    total_cents INTEGER NOT NULL DEFAULT 0, -- já com desconto aplicado
    converted_order_id INTEGER REFERENCES orders(id),
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS quote_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    quote_id INTEGER NOT NULL REFERENCES quotes(id),
    product_id INTEGER NOT NULL REFERENCES products(id),
    quantity INTEGER NOT NULL,
    unit_price_cents INTEGER NOT NULL,
    subtotal_cents INTEGER NOT NULL
);

-- Liberação de preço: quando o desconto do orçamento passa do limite do
-- papel de quem criou (ver App\Core\DiscountLimits), fica pendente aqui até
-- Licenciado/Gestor/admin aprovar. approvable_type existe pra poder reusar
-- essa tabela com Pedidos no futuro, igual ao polimorfismo do EcoDiffusore.
CREATE TABLE IF NOT EXISTS approvals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    approvable_type TEXT NOT NULL DEFAULT 'quote',
    approvable_id INTEGER NOT NULL,
    requested_discount_pct REAL NOT NULL,
    status TEXT NOT NULL DEFAULT 'pendente', -- pendente | aprovado | recusado
    requested_by INTEGER NOT NULL REFERENCES users(id),
    decided_by INTEGER REFERENCES users(id),
    decided_at TEXT,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL REFERENCES clients(id),
    seller_id INTEGER NOT NULL REFERENCES users(id), -- quem fechou a venda (licenciado ou vendedor)
    status TEXT NOT NULL DEFAULT 'pendente', -- pendente | confirmado | entregue | cancelado
    total_cents INTEGER NOT NULL DEFAULT 0,
    -- Acompanhar a entrega
    tracking_code TEXT,
    delivery_status TEXT NOT NULL DEFAULT 'aguardando', -- aguardando | em_transito | entregue
    delivered_at TEXT,
    -- Pós-venda de instalação
    installation_status TEXT NOT NULL DEFAULT 'nao_iniciada', -- nao_iniciada | agendada | concluida
    installation_date TEXT,
    installation_notes TEXT,
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

-- Documentos anexados a um pedido (ex: contrato assinado, CNPJ do cliente)
-- que precisam de aprovação antes da entrega seguir.
CREATE TABLE IF NOT EXISTS order_documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL REFERENCES orders(id),
    uploaded_by INTEGER NOT NULL REFERENCES users(id),
    name TEXT NOT NULL,
    file_path TEXT,
    status TEXT NOT NULL DEFAULT 'pendente', -- pendente | aprovado | recusado
    decided_by INTEGER REFERENCES users(id),
    decided_at TEXT,
    created_at TEXT NOT NULL
);

-- Material de venda: recursos que o admin disponibiliza pra rede toda.
CREATE TABLE IF NOT EXISTS sales_materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    file_path TEXT,
    link TEXT,
    created_at TEXT NOT NULL
);

-- Configuração de pagamentos: formas de financiamento oferecidas ao cliente
-- final (ex: BTG 21x sem juros, mencionado na reunião de kickoff).
CREATE TABLE IF NOT EXISTS payment_methods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    max_installments INTEGER NOT NULL DEFAULT 1,
    interest_rate_percent REAL NOT NULL DEFAULT 0,
    active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
);

-- Metas: meta mensal de um usuário (receita ou nº de pedidos), atribuída por
-- quem está acima dele na hierarquia (ou por ele mesmo).
CREATE TABLE IF NOT EXISTS goals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id),
    period TEXT NOT NULL, -- 'AAAA-MM'
    target_type TEXT NOT NULL DEFAULT 'revenue', -- revenue | orders_count
    target_value REAL NOT NULL,
    created_by INTEGER NOT NULL REFERENCES users(id),
    created_at TEXT NOT NULL
);
