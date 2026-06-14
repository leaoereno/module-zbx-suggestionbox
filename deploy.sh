#!/bin/bash
# ============================================================
# deploy.sh — Suggestion Box Module
# Destino: lab-zbx (192.168.0.151) ou lnxdczbxfront01/02
# Autor: Rafael Leão — NOC Claro Empresas / Embratel
# ============================================================

set -e

MODULE_NAME="module-zbx-suggestion-box"
MODULE_SRC="$(cd "$(dirname "$0")" && pwd)"
ZBX_MODULES_PATH="/usr/share/zabbix/modules"

# ---- Alvos (ajuste conforme ambiente) ----
LAB_HOST="192.168.0.151"
PROD_HOSTS=("lnxdczbxfront01" "lnxdczbxfront02")
DB_HOST="localhost"   # ajuste para o host MariaDB
DB_USER="zabbix"
DB_PASS=""            # preencher ou usar ~/.my.cnf
DB_NAME="zabbix"

TARGET="${1:-lab}"   # uso: ./deploy.sh lab | prod | sql

# ============================================================
echo "==> [Suggestion Box] Deploy — alvo: $TARGET"
# ============================================================

deploy_files() {
    local host="$1"
    echo "  -> Copiando arquivos para $host:$ZBX_MODULES_PATH/$MODULE_NAME"
    ssh "$host" "mkdir -p $ZBX_MODULES_PATH/$MODULE_NAME"
    rsync -avz --delete \
        --exclude='.git' \
        --exclude='deploy.sh' \
        --exclude='install.sql' \
        --exclude='*.md' \
        "$MODULE_SRC/" \
        "$host:$ZBX_MODULES_PATH/$MODULE_NAME/"
    echo "  -> Ajustando permissões em $host"
    ssh "$host" "chown -R apache:apache $ZBX_MODULES_PATH/$MODULE_NAME && chmod -R 755 $ZBX_MODULES_PATH/$MODULE_NAME"
}

run_sql() {
    local host="$1"
    echo "  -> Executando install.sql em $host ($DB_NAME)"
    if [ -n "$DB_PASS" ]; then
        ssh "$host" "mysql -h $DB_HOST -u $DB_USER -p'$DB_PASS' $DB_NAME" < "$MODULE_SRC/install.sql"
    else
        ssh "$host" "mysql -h $DB_HOST -u $DB_USER $DB_NAME" < "$MODULE_SRC/install.sql"
    fi
}

clear_opcache() {
    local host="$1"
    echo "  -> Limpando OPcache em $host"
    ssh "$host" "php -r 'if(function_exists(\"opcache_reset\")) opcache_reset();' 2>/dev/null || true"
    ssh "$host" "systemctl reload php-fpm 2>/dev/null || systemctl reload php8.2-fpm 2>/dev/null || true"
}

case "$TARGET" in
    lab)
        deploy_files "$LAB_HOST"
        run_sql "$LAB_HOST"
        clear_opcache "$LAB_HOST"
        echo ""
        echo "✅  Deploy concluído em lab-zbx ($LAB_HOST)"
        echo "    Acesse: Zabbix > Serviços > Suggestion Box"
        echo "    Ative o módulo em: Administration > General > Modules"
        ;;
    prod)
        for host in "${PROD_HOSTS[@]}"; do
            deploy_files "$host"
            clear_opcache "$host"
        done
        echo "  -> SQL apenas no primeiro nó (shared DB)"
        run_sql "${PROD_HOSTS[0]}"
        echo ""
        echo "✅  Deploy concluído em produção"
        ;;
    sql)
        echo "  -> Apenas SQL no host: $LAB_HOST"
        run_sql "$LAB_HOST"
        ;;
    local)
        # deploy local (quando executado direto no servidor Zabbix)
        DEST="$ZBX_MODULES_PATH/$MODULE_NAME"
        echo "  -> Instalando localmente em $DEST"
        mkdir -p "$DEST"
        rsync -av --delete \
            --exclude='.git' \
            --exclude='deploy.sh' \
            --exclude='install.sql' \
            --exclude='*.md' \
            "$MODULE_SRC/" "$DEST/"
        chown -R apache:apache "$DEST"
        chmod -R 755 "$DEST"
        echo "  -> Executando SQL..."
        mysql -h "$DB_HOST" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" < "$MODULE_SRC/install.sql"
        php -r 'opcache_reset();' 2>/dev/null || true
        echo "✅  Instalação local concluída."
        ;;
    *)
        echo "Uso: $0 [lab|prod|sql|local]"
        exit 1
        ;;
esac
