-- ============================================================
-- Suggestion Box — Schema de Instalação
-- Zabbix 7.0 / MariaDB 10.11
-- Autor: Rafael Leão — NOC Claro Empresas / Embratel
-- ============================================================

-- Tabela principal de sugestões
CREATE TABLE IF NOT EXISTS zbx_suggestions (
    suggestionid  BIGINT       NOT NULL,
    userid        BIGINT       NOT NULL,
    title         VARCHAR(200) NOT NULL,
    description   TEXT         DEFAULT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (suggestionid),
    KEY idx_zbx_sug_userid (userid),
    KEY idx_zbx_sug_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags das sugestões
CREATE TABLE IF NOT EXISTS zbx_suggestion_tags (
    tagid         BIGINT       NOT NULL,
    suggestionid  BIGINT       NOT NULL,
    tag           VARCHAR(50)  NOT NULL,
    PRIMARY KEY (tagid),
    KEY idx_zbx_sug_tag_sid (suggestionid),
    KEY idx_zbx_sug_tag_tag (tag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Votos
CREATE TABLE IF NOT EXISTS zbx_suggestion_votes (
    voteid        BIGINT    NOT NULL,
    suggestionid  BIGINT    NOT NULL,
    userid        BIGINT    NOT NULL,
    voted_at      DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (voteid),
    UNIQUE KEY uk_zbx_vote_user (suggestionid, userid),
    KEY idx_zbx_vote_sid (suggestionid),
    KEY idx_zbx_vote_uid (userid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- role_rule: liberar acesso às actions do módulo
-- ============================================================

-- suggestion.box.list e votar/salvar/deletar → todos os roles (type 1, 2, 3)
INSERT IGNORE INTO role_rule (role_ruleid, roleid, type, name, value_int, value_str, value_moduleid)
SELECT
    (SELECT COALESCE(MAX(role_ruleid), 0) FROM role_rule) + ROW_NUMBER() OVER (ORDER BY r.roleid, a.action_name),
    r.roleid,
    2,
    'actions',
    1,
    a.action_name,
    NULL
FROM role r
JOIN (
    SELECT 'suggestion.box.list'   AS action_name UNION ALL
    SELECT 'suggestion.box.save'   UNION ALL
    SELECT 'suggestion.box.vote'   UNION ALL
    SELECT 'suggestion.box.delete'
) a
WHERE r.type IN (1, 2, 3);

-- suggestion.box.report → apenas Super Admin (type 3)
INSERT IGNORE INTO role_rule (role_ruleid, roleid, type, name, value_int, value_str, value_moduleid)
SELECT
    (SELECT COALESCE(MAX(role_ruleid), 0) FROM role_rule) + ROW_NUMBER() OVER (ORDER BY r.roleid),
    r.roleid,
    2,
    'actions',
    1,
    'suggestion.box.report',
    NULL
FROM role r
WHERE r.type = 3;

-- ============================================================
-- Verificação
-- ============================================================
SELECT 'Tables OK:' AS status;
SHOW TABLES LIKE 'zbx_suggestion%';

SELECT 'role_rules OK:' AS status;
SELECT r.name AS role_name, r.type AS role_type, rr.value_str AS action
FROM role_rule rr
JOIN role r ON r.roleid = rr.roleid
WHERE rr.value_str LIKE 'suggestion.box.%'
ORDER BY rr.value_str, r.type;
