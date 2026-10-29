-- RivolLeads: projetos/sites cadastrados por usuário.
-- Execute uma única vez no banco utilizado pela aplicação.
-- A coluna user_id não possui FK propositalmente: confirme o tipo exato de users.id
-- antes de adicionar uma constraint, para evitar incompatibilidade de tipos.

CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    domain VARCHAR(253) NOT NULL,
    public_key VARCHAR(64) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_projects_public_key (public_key),
    KEY idx_projects_user_created (user_id, created_at),
    KEY idx_projects_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
