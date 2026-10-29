-- RivolLeads: eventos anônimos coletados pelo rastreador.
-- Execute depois de criar a tabela projects.
CREATE TABLE IF NOT EXISTS project_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    event_name VARCHAR(64) NOT NULL DEFAULT 'pageview',
    client_id CHAR(36) DEFAULT NULL,
    session_id CHAR(36) DEFAULT NULL,
    page_url VARCHAR(2048) NOT NULL,
    referrer_url VARCHAR(2048) DEFAULT NULL,
    utm_source VARCHAR(255) DEFAULT NULL,
    utm_medium VARCHAR(255) DEFAULT NULL,
    utm_campaign VARCHAR(255) DEFAULT NULL,
    utm_term VARCHAR(255) DEFAULT NULL,
    utm_content VARCHAR(255) DEFAULT NULL,
    event_data JSON DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_project_events_created (project_id, created_at),
    KEY idx_project_events_name_created (project_id, event_name, created_at),
    KEY idx_project_events_session (project_id, session_id),
    CONSTRAINT fk_project_events_project
        FOREIGN KEY (project_id) REFERENCES projects (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
