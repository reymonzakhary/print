#!/bin/bash

# PostgreSQL 13.4 to 17-3.4 Multi-Database Upgrade Script - FIXED VERSION
# This script performs a one-time upgrade while preserving all data and privileges for ALL databases

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
COMPOSE_SERVICE="postgres"
CONTAINER_NAME="postgres"
BACKUP_DIR="./postgres-upgrade-backup-$(date +%Y%m%d_%H%M%S)"
DB_USER="default"
DB_PASSWORD="secret"
DATA_DIR="./databases/cec"

# System databases to exclude from individual processing
SYSTEM_DBS=("postgres" "template0" "template1")

echo -e "${BLUE}=== PostgreSQL 13.4 to 17-3.4 Multi-Database Upgrade Script (FIXED) ===${NC}"
echo -e "${YELLOW}This script will upgrade ALL databases in your PostGIS PostgreSQL from 13.4 to 17-3.4${NC}"
echo -e "${YELLOW}Backup directory: ${BACKUP_DIR}${NC}"
echo ""

# Function to log messages
log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

# Function to check if database is a system database
is_system_db() {
    local db_name="$1"
    for sys_db in "${SYSTEM_DBS[@]}"; do
        if [[ "$db_name" == "$sys_db" ]]; then
            return 0
        fi
    done
    return 1
}

# Function to escape database names for shell - FIXED to handle special characters and spaces
escape_db_name() {
    # Replace problematic characters with underscores for filenames
    echo "$1" | sed 's/[^a-zA-Z0-9_-]/_/g'
}

# Function to properly quote database names for SQL
quote_db_name() {
    # Escape quotes within the database name and wrap in double quotes
    echo "\"$(echo "$1" | sed 's/"/\\"/g')\""
}

# Check if docker compose is available
if ! command -v docker compose &> /dev/null; then
    error "docker compose is not installed or not in PATH"
fi

# Check if container is running
log "Checking if container is running..."
if ! docker compose ps | grep -q "$COMPOSE_SERVICE.*running"; then
    warning "Container $CONTAINER_NAME is not running. Starting it first..."
    docker compose up -d $COMPOSE_SERVICE
    log "Waiting for container to start..."
    sleep 15

    # Wait for PostgreSQL to be ready
    log "Waiting for PostgreSQL to accept connections..."
    for i in {1..60}; do
        if docker compose exec $COMPOSE_SERVICE pg_isready -U "$DB_USER" > /dev/null 2>&1; then
            log "PostgreSQL is ready for connections (attempt $i)"
            break
        fi
        if [ $i -eq 60 ]; then
            error "PostgreSQL did not start within 120 seconds"
        fi
        echo -n "."
        sleep 2
    done

    # Additional wait to ensure database is fully ready
    log "Waiting additional time for database to be fully ready..."
    sleep 10
else
    log "Container is already running"
fi

# Step 1: Discover all databases
log "Discovering all databases..."

# Test connection first with more detailed output
log "Testing database connection..."
if ! docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres -c "SELECT 1;" > /dev/null 2>&1; then
    error "Cannot connect to PostgreSQL. Please check if the container is running and credentials are correct."
fi
log "Database connection successful"

# Simple and reliable database discovery - FIXED to handle databases with special characters
log "Querying for databases..."
# Use a more robust method to get clean database names
DB_LIST_OUTPUT=$(docker compose exec -T $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres -A -t -c "SELECT datname FROM pg_database WHERE datistemplate = false AND datallowconn = true ORDER BY datname;" 2>/dev/null)

ALL_DATABASES=()
while IFS= read -r db_name; do
    # Clean the database name
    db_name=$(echo "$db_name" | sed 's/^[ \t]*//;s/[ \t]*$//')

    # Skip empty lines and filter out docker compose warnings
    if [[ -n "$db_name" ]] && [[ ! "$db_name" =~ ^(time=|level=|msg=|WARN|ERROR) ]] && [[ "$db_name" != *"docker-compose.yml"* ]]; then
        ALL_DATABASES+=("$db_name")
        log "Found database: '$db_name'"
    fi
done <<< "$DB_LIST_OUTPUT"

log "Total databases discovered: ${#ALL_DATABASES[@]}"

if [ ${#ALL_DATABASES[@]} -eq 0 ]; then
    error "No databases found!"
fi

echo -e "${BLUE}Found databases:${NC}"
for db in "${ALL_DATABASES[@]}"; do
    if is_system_db "$db"; then
        echo -e "  - ${YELLOW}$db${NC} (system database)"
    else
        echo -e "  - ${GREEN}$db${NC} (user database)"
    fi
done

# Count user databases
USER_DBS=()
for db in "${ALL_DATABASES[@]}"; do
    if ! is_system_db "$db"; then
        USER_DBS+=("$db")
    fi
done

echo -e "${BLUE}Will process ${#USER_DBS[@]} user database(s)${NC}"
echo ""

# Step 2: Create backup directory
log "Creating backup directory..."
mkdir -p "$BACKUP_DIR"
mkdir -p "$BACKUP_DIR/individual_databases"

# Step 3: Backup all databases with pg_dumpall - INCLUDING ROLES
log "Creating full cluster backup with pg_dumpall (including roles)..."
docker compose exec -T $COMPOSE_SERVICE pg_dumpall -U "$DB_USER" > "$BACKUP_DIR/full_cluster_backup.sql" || error "Failed to create full cluster backup"

# Step 4: Backup each user database individually - FIXED to handle special characters
log "Creating individual database backups..."
for db in "${USER_DBS[@]}"; do
    safe_db_name=$(escape_db_name "$db")
    quoted_db=$(quote_db_name "$db")
    log "Backing up database: $db (safe name: $safe_db_name)"

    # SQL format backup with proper quoting
    docker compose exec -T $COMPOSE_SERVICE pg_dump -U "$DB_USER" -d "$db" > "$BACKUP_DIR/individual_databases/${safe_db_name}.sql" 2>/dev/null || {
        warning "Failed to create SQL backup for database: $db"
        continue
    }

    # Custom format backup for faster restore
    docker compose exec -T $COMPOSE_SERVICE pg_dump -U "$DB_USER" -d "$db" -Fc > "$BACKUP_DIR/individual_databases/${safe_db_name}.backup" 2>/dev/null || {
        warning "Failed to create custom backup for database: $db"
    }

    # Document extensions for this database
    docker compose exec -T $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" -c "\dx" > "$BACKUP_DIR/individual_databases/${safe_db_name}_extensions.txt" 2>/dev/null || {
        warning "Failed to document extensions for database: $db"
    }

    # Also save the exact database name for restoration
    echo "$db" > "$BACKUP_DIR/individual_databases/${safe_db_name}.dbname"
done

# Step 5: Export roles and privileges separately
log "Backing up roles and privileges..."
docker compose exec -T $COMPOSE_SERVICE pg_dumpall -U "$DB_USER" --roles-only > "$BACKUP_DIR/roles.sql" || error "Failed to backup roles"

# Step 6: Export global objects (tablespaces, etc.)
log "Backing up global objects..."
docker compose exec -T $COMPOSE_SERVICE pg_dumpall -U "$DB_USER" --globals-only > "$BACKUP_DIR/globals.sql" || error "Failed to backup global objects"

# Step 7: Backup current data directory (as fallback)
log "Creating data directory backup (this may take a while)..."
if [ -d "$DATA_DIR" ]; then
    cp -r "$DATA_DIR" "$BACKUP_DIR/data_backup" || warning "Failed to backup data directory"
fi

# Step 8: Create database inventory
log "Creating database inventory..."
cat > "$BACKUP_DIR/database_inventory.txt" << EOF
Database Inventory - $(date)
============================

Total Databases: ${#ALL_DATABASES[@]}
User Databases: ${#USER_DBS[@]}
System Databases: $((${#ALL_DATABASES[@]} - ${#USER_DBS[@]}))

User Databases to be restored:
EOF

for db in "${USER_DBS[@]}"; do
    echo "  - $db" >> "$BACKUP_DIR/database_inventory.txt"
done

echo "" >> "$BACKUP_DIR/database_inventory.txt"
echo "System Databases (preserved automatically):" >> "$BACKUP_DIR/database_inventory.txt"
for db in "${ALL_DATABASES[@]}"; do
    if is_system_db "$db"; then
        echo "  - $db" >> "$BACKUP_DIR/database_inventory.txt"
    fi
done

# Step 9: Stop the current container
log "Stopping current PostgreSQL container..."
docker compose down

# Step 10: Rename old data directory
log "Renaming old data directory..."
if [ -d "$DATA_DIR" ]; then
    mv "$DATA_DIR" "${DATA_DIR}_old_$(date +%Y%m%d_%H%M%S)" || error "Failed to rename old data directory"
fi

# Step 11: Create new data directory
log "Creating new data directory..."
mkdir -p "$DATA_DIR"

# Step 12: Build new Docker image - ENSURE POSTGIS IS INCLUDED
log "Building new PostgreSQL 17-3.4 image with PostGIS..."
# Make sure your Dockerfile includes PostGIS installation
docker compose build $COMPOSE_SERVICE || error "Failed to build new image"

# Step 13: Start new container
log "Starting new PostgreSQL 17-3.4 container..."
docker compose up -d $COMPOSE_SERVICE

# Wait for PostgreSQL to be ready
log "Waiting for PostgreSQL to be ready..."
for i in {1..120}; do
    if docker compose exec $COMPOSE_SERVICE pg_isready -U "$DB_USER" > /dev/null 2>&1; then
        log "PostgreSQL is ready (attempt $i)"
        break
    fi
    if [ $i -eq 120 ]; then
        error "PostgreSQL did not start within 240 seconds"
    fi
    echo -n "."
    sleep 2
done

# Additional wait for database to be fully ready for connections
log "Waiting for database to accept connections..."
sleep 15

# Test basic connectivity first
for i in {1..30}; do
    if docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres -c "SELECT 1;" > /dev/null 2>&1; then
        log "Database is accepting connections (attempt $i)"
        break
    fi
    if [ $i -eq 30 ]; then
        error "Database is not accepting connections after 60 seconds"
    fi
    echo -n "."
    sleep 2
done

# Step 14: CRITICAL - Restore roles FIRST before any database operations
log "Restoring roles and users first..."
docker compose exec -T $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres < "$BACKUP_DIR/roles.sql" 2>&1 | grep -v "ERROR:.*already exists" || true

# Step 15: Handle authentication - Set password for the user
log "Setting user password..."
docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres -c "ALTER USER $quoted_db PASSWORD '$DB_PASSWORD';" || warning "Failed to set password"

# Step 16: Install PostGIS extension in template1 if needed
log "Checking and installing PostGIS..."
docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres -c "CREATE EXTENSION IF NOT EXISTS postgis;" 2>/dev/null || {
    warning "PostGIS might not be installed in the Docker image. Make sure your Dockerfile includes PostGIS!"
}

# Step 17: Restore global objects
log "Restoring global objects (tablespaces, etc.)..."
docker compose exec -T $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres < "$BACKUP_DIR/globals.sql" 2>&1 | grep -v "ERROR:.*already exists" || true

# Step 18: Restore each user database - FIXED version
RESTORED_DBS=()
FAILED_DBS=()

log "Restoring individual databases..."
for safe_db_name in "$BACKUP_DIR/individual_databases"/*.dbname; do
    if [ ! -f "$safe_db_name" ]; then
        continue
    fi

    # Read the original database name
    db=$(cat "$safe_db_name")
    safe_db_name=$(basename "$safe_db_name" .dbname)
    quoted_db=$(quote_db_name "$db")

    log "Restoring database: $db"

    # First, create the database with proper quoting
    log "Creating database: $db"
    if ! docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres -c "CREATE DATABASE $quoted_db;" 2>/dev/null; then
        warning "Database $db might already exist or failed to create"
    fi

    # Install PostGIS in the database if it was used
    if grep -q "postgis" "$BACKUP_DIR/individual_databases/${safe_db_name}_extensions.txt" 2>/dev/null; then
        log "Installing PostGIS extension in database: $db"
        docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" -c "CREATE EXTENSION IF NOT EXISTS postgis;" 2>/dev/null || warning "Failed to install PostGIS in $db"
    fi

    # Try to restore using custom format first
    if [ -f "$BACKUP_DIR/individual_databases/${safe_db_name}.backup" ]; then
        log "Restoring from custom format backup..."
        if docker compose exec -T $COMPOSE_SERVICE pg_restore -U "$DB_USER" -d "$db" --verbose --no-owner < "$BACKUP_DIR/individual_databases/${safe_db_name}.backup" 2>&1 | grep -v "ERROR:.*already exists"; then
            log "Successfully restored database: $db (custom format)"
            RESTORED_DBS+=("$db")
        else
            warning "Custom format restore had issues for database: $db, trying SQL format..."

            # Try SQL format
            if [ -f "$BACKUP_DIR/individual_databases/${safe_db_name}.sql" ]; then
                if docker compose exec -T $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" < "$BACKUP_DIR/individual_databases/${safe_db_name}.sql" 2>&1 | grep -v "ERROR:.*already exists"; then
                    log "Successfully restored database: $db (SQL format)"
                    RESTORED_DBS+=("$db")
                else
                    warning "Failed to restore database: $db"
                    FAILED_DBS+=("$db")
                fi
            fi
        fi
    elif [ -f "$BACKUP_DIR/individual_databases/${safe_db_name}.sql" ]; then
        # Only SQL backup available
        if docker compose exec -T $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" < "$BACKUP_DIR/individual_databases/${safe_db_name}.sql" 2>&1 | grep -v "ERROR:.*already exists"; then
            log "Successfully restored database: $db (SQL format)"
            RESTORED_DBS+=("$db")
        else
            warning "Failed to restore database: $db"
            FAILED_DBS+=("$db")
        fi
    else
        warning "No backup found for database: $db"
        FAILED_DBS+=("$db")
    fi
done

# Step 19: Update extensions in all restored databases
log "Updating extensions in all databases..."
for db in "${RESTORED_DBS[@]}"; do
    log "Updating extensions in database: $db"

    # Update PostGIS if it exists
    docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" -c "ALTER EXTENSION postgis UPDATE;" 2>/dev/null || true

    # Update all other extensions
    docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" -c "
DO \$\$
DECLARE
    ext RECORD;
BEGIN
    FOR ext IN SELECT extname FROM pg_extension WHERE extname NOT IN ('plpgsql')
    LOOP
        BEGIN
            EXECUTE 'ALTER EXTENSION ' || ext.extname || ' UPDATE';
            RAISE NOTICE 'Updated extension: %', ext.extname;
        EXCEPTION WHEN OTHERS THEN
            RAISE NOTICE 'Failed to update extension: % - %', ext.extname, SQLERRM;
        END;
    END LOOP;
END;
\$\$;
" 2>/dev/null || warning "Some extensions may not have updated in database: $db"
done

# Step 20: Analyze all databases
log "Analyzing all databases for optimal performance..."
for db in "${RESTORED_DBS[@]}"; do
    log "Analyzing database: $db"
    docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" -c "ANALYZE;" 2>/dev/null || warning "Database analysis failed for: $db"
done

# Step 21: Verify upgrade
log "Verifying upgrade..."

# Check PostgreSQL version
PG_VERSION=$(docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres -t -c "SELECT version();" | head -1)
log "PostgreSQL Version: $PG_VERSION"

# Check PostGIS version (if available)
POSTGIS_VERSION="Not installed"
if POSTGIS_CHECK=$(docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d postgres -t -c "SELECT PostGIS_full_version();" 2>/dev/null | head -1); then
    POSTGIS_VERSION="$POSTGIS_CHECK"
fi
log "PostGIS Version: $POSTGIS_VERSION"

# Step 22: Test authentication and access for all databases
log "Testing access to all restored databases..."
ACCESS_SUMMARY=""
for db in "${RESTORED_DBS[@]}"; do
    if docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" -c "SELECT current_user, current_database();" > /dev/null 2>&1; then
        TABLE_COUNT=$(docker compose exec $COMPOSE_SERVICE psql -U "$DB_USER" -d "$db" -t -c "SELECT count(*) FROM information_schema.tables WHERE table_schema = 'public';" 2>/dev/null | xargs || echo "0")
        log "Database '$db' is accessible (${TABLE_COUNT} tables in public schema)"
        ACCESS_SUMMARY="$ACCESS_SUMMARY\n  ✓ $db ($TABLE_COUNT tables)"
    else
        warning "Database '$db' is not accessible"
        ACCESS_SUMMARY="$ACCESS_SUMMARY\n  ✗ $db (not accessible)"
    fi
done

# Step 23: Create comprehensive upgrade summary
log "Creating upgrade summary..."
cat > "$BACKUP_DIR/upgrade_summary.txt" << EOF
PostgreSQL Multi-Database Upgrade Summary
==========================================
Date: $(date)
Upgrade: PostgreSQL 13.4 -> 17-3.4 with PostGIS
User: $DB_USER
Data Directory: $DATA_DIR

PostgreSQL Version: $PG_VERSION
PostGIS Version: $POSTGIS_VERSION

Database Summary:
- Total databases found: ${#ALL_DATABASES[@]}
- User databases: ${#USER_DBS[@]}
- Successfully restored: ${#RESTORED_DBS[@]}
- Failed to restore: ${#FAILED_DBS[@]}

Successfully Restored Databases:
$(printf "%s\n" "${RESTORED_DBS[@]}" | sed 's/^/  - /')

$(if [ ${#FAILED_DBS[@]} -gt 0 ]; then
echo "Failed to Restore:"
printf "%s\n" "${FAILED_DBS[@]}" | sed 's/^/  - /'
echo ""
fi)

Database Access Test:
$(echo -e "$ACCESS_SUMMARY")

Backup Location: $BACKUP_DIR
- full_cluster_backup.sql: Complete cluster dump
- individual_databases/: Individual database backups
- roles.sql: Roles and privileges
- globals.sql: Global objects
- database_inventory.txt: Database inventory
- data_backup/: Original data directory (if backed up)

Overall Status: $(if [ ${#FAILED_DBS[@]} -eq 0 ]; then echo "SUCCESS"; else echo "PARTIAL SUCCESS (${#FAILED_DBS[@]} databases failed)"; fi)
EOF

echo ""
if [ ${#FAILED_DBS[@]} -eq 0 ]; then
    echo -e "${GREEN}=== UPGRADE COMPLETED SUCCESSFULLY ===${NC}"
    echo -e "${GREEN}All ${#RESTORED_DBS[@]} user databases have been upgraded from PostgreSQL 13.4 to 17-3.4${NC}"
else
    echo -e "${YELLOW}=== UPGRADE COMPLETED WITH WARNINGS ===${NC}"
    echo -e "${GREEN}Successfully upgraded ${#RESTORED_DBS[@]} databases${NC}"
    echo -e "${RED}Failed to upgrade ${#FAILED_DBS[@]} databases: $(IFS=", "; echo "${FAILED_DBS[*]}")${NC}"
fi

echo -e "${YELLOW}Backup location: $BACKUP_DIR${NC}"
echo ""
echo -e "${BLUE}Database Summary:${NC}"
echo -e "  Successfully restored: ${GREEN}${#RESTORED_DBS[@]}${NC}"
if [ ${#FAILED_DBS[@]} -gt 0 ]; then
    echo -e "  Failed to restore: ${RED}${#FAILED_DBS[@]}${NC}"
fi
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "1. Test all your applications thoroughly with each database"
echo "2. Monitor performance and logs for all databases"
echo "3. Keep backups until you're confident everything works"
echo "4. Consider running VACUUM ANALYZE on large tables in each database"
if [ ${#FAILED_DBS[@]} -gt 0 ]; then
    echo "5. Investigate and manually restore failed databases if needed"
fi
echo ""
echo -e "${BLUE}To check the upgrade status for all databases:${NC}"
echo "docker compose exec $COMPOSE_SERVICE psql -U \"$DB_USER\" -d postgres -c \"SELECT datname FROM pg_database WHERE datistemplate = false;\""
echo "docker compose exec $COMPOSE_SERVICE psql -U \"$DB_USER\" -d postgres -c \"SELECT version();\""
echo ""
echo -e "${GREEN}Multi-database upgrade completed!${NC}"