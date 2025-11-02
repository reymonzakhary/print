#!/bin/bash

# PostgreSQL 13.4 Manual Backup Script
# This script creates a complete backup of your PostgreSQL 13.4 instance

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration - adjust these as needed
COMPOSE_SERVICE="postgres"
BACKUP_DIR="./postgres-13.4-backup-$(date +%Y%m%d_%H%M%S)"
BACKUP_USER=""  # Will be detected automatically if not set

echo -e "${BLUE}=== PostgreSQL 13.4 Manual Backup Script ===${NC}"
echo -e "${YELLOW}Creating comprehensive backup in: ${BACKUP_DIR}${NC}"
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

# Check if docker compose is available
if ! command -v docker compose &> /dev/null; then
    if ! command -v docker-compose &> /dev/null; then
        error "Neither 'docker compose' nor 'docker-compose' found in PATH"
    else
        # Use docker-compose instead
        alias docker compose='docker-compose'
    fi
fi

# Check if container is running
log "Checking PostgreSQL container status..."
if ! docker compose ps | grep -q "$COMPOSE_SERVICE.*running"; then
    error "PostgreSQL container is not running. Please start it first with: docker compose up -d $COMPOSE_SERVICE"
fi

# Create backup directory structure
log "Creating backup directory structure..."
mkdir -p "$BACKUP_DIR"
mkdir -p "$BACKUP_DIR/databases"
mkdir -p "$BACKUP_DIR/globals"
mkdir -p "$BACKUP_DIR/individual"

# Step 1: Auto-detect the superuser if not specified
if [ -z "$BACKUP_USER" ]; then
    log "Auto-detecting PostgreSQL superuser..."

    # Try common superusers
    for user in postgres default admin root; do
        if docker compose exec $COMPOSE_SERVICE psql -U "$user" -d postgres -c "SELECT 1;" > /dev/null 2>&1; then
            BACKUP_USER="$user"
            log "Found working superuser: $BACKUP_USER"
            break
        fi
    done

    if [ -z "$BACKUP_USER" ]; then
        error "Could not auto-detect PostgreSQL superuser. Please set BACKUP_USER variable in the script."
    fi
else
    log "Using specified user: $BACKUP_USER"
fi

# Step 2: Get PostgreSQL version info
log "Getting PostgreSQL version information..."
PG_VERSION=$(docker compose exec $COMPOSE_SERVICE psql -U "$BACKUP_USER" -d postgres -t -c "SELECT version();" 2>/dev/null | head -1) || {
    warning "Could not get PostgreSQL version"
    PG_VERSION="Unknown"
}
echo "PostgreSQL Version: $PG_VERSION" > "$BACKUP_DIR/version_info.txt"

# Step 3: List all databases
log "Discovering all databases..."
DB_LIST=$(docker compose exec -T $COMPOSE_SERVICE psql -U "$BACKUP_USER" -d postgres -A -t -c "SELECT datname FROM pg_database WHERE datistemplate = false ORDER BY datname;" 2>/dev/null)

if [ -z "$DB_LIST" ]; then
    error "No databases found or connection failed"
fi

# Save database list
echo "$DB_LIST" > "$BACKUP_DIR/database_list.txt"

# Count databases
DB_COUNT=$(echo "$DB_LIST" | wc -l)
log "Found $DB_COUNT databases"

# Step 4: Backup roles and users
log "Backing up all roles and users..."
docker compose exec -T $COMPOSE_SERVICE pg_dumpall -U "$BACKUP_USER" --roles-only > "$BACKUP_DIR/globals/roles.sql" 2>/dev/null || {
    warning "Failed to backup roles"
}

# Step 5: Backup global objects
log "Backing up global objects (tablespaces, etc.)..."
docker compose exec -T $COMPOSE_SERVICE pg_dumpall -U "$BACKUP_USER" --globals-only > "$BACKUP_DIR/globals/globals.sql" 2>/dev/null || {
    warning "Failed to backup global objects"
}

# Step 6: Create full cluster backup with pg_dumpall
log "Creating complete cluster backup with pg_dumpall (this may take a while)..."
docker compose exec -T $COMPOSE_SERVICE pg_dumpall -U "$BACKUP_USER" > "$BACKUP_DIR/full_cluster_backup.sql" || {
    error "Failed to create full cluster backup"
}

# Compress the full backup
log "Compressing full cluster backup..."
gzip -c "$BACKUP_DIR/full_cluster_backup.sql" > "$BACKUP_DIR/full_cluster_backup.sql.gz" || {
    warning "Failed to compress full backup"
}

# Step 7: Backup each database individually
log "Creating individual database backups..."
echo "$DB_LIST" | while IFS= read -r db; do
    if [ -n "$db" ]; then
        log "Backing up database: $db"

        # Sanitize database name for filename
        safe_db_name=$(echo "$db" | sed 's/[^a-zA-Z0-9_-]/_/g')

        # Save original database name
        echo "$db" > "$BACKUP_DIR/individual/${safe_db_name}.dbname"

        # SQL format backup
        docker compose exec -T $COMPOSE_SERVICE pg_dump -U "$BACKUP_USER" -d "$db" > "$BACKUP_DIR/individual/${safe_db_name}.sql" 2>/dev/null || {
            warning "Failed to backup database: $db (SQL format)"
        }

        # Custom format backup (compressed)
        docker compose exec -T $COMPOSE_SERVICE pg_dump -U "$BACKUP_USER" -d "$db" -Fc > "$BACKUP_DIR/individual/${safe_db_name}.backup" 2>/dev/null || {
            warning "Failed to backup database: $db (custom format)"
        }

        # Directory format backup (for large databases)
        docker compose exec $COMPOSE_SERVICE pg_dump -U "$BACKUP_USER" -d "$db" -Fd -f "/tmp/${safe_db_name}_dir" 2>/dev/null && \
        docker cp "$COMPOSE_SERVICE:/tmp/${safe_db_name}_dir" "$BACKUP_DIR/individual/${safe_db_name}_dir" 2>/dev/null && \
        docker compose exec $COMPOSE_SERVICE rm -rf "/tmp/${safe_db_name}_dir" 2>/dev/null || {
            warning "Failed to create directory format backup for: $db"
        }

        # Get database size
        DB_SIZE=$(docker compose exec $COMPOSE_SERVICE psql -U "$BACKUP_USER" -d "$db" -t -c "SELECT pg_size_pretty(pg_database_size('$db'));" 2>/dev/null | xargs) || DB_SIZE="Unknown"
        echo "$db: $DB_SIZE" >> "$BACKUP_DIR/database_sizes.txt"

        # List extensions in this database
        docker compose exec -T $COMPOSE_SERVICE psql -U "$BACKUP_USER" -d "$db" -c "\dx" > "$BACKUP_DIR/individual/${safe_db_name}_extensions.txt" 2>/dev/null || true
    fi
done

# Step 8: Backup PostgreSQL configuration files
log "Backing up PostgreSQL configuration files..."
docker compose exec $COMPOSE_SERVICE cat /var/lib/postgresql/data/postgresql.conf > "$BACKUP_DIR/postgresql.conf" 2>/dev/null || {
    warning "Failed to backup postgresql.conf"
}

docker compose exec $COMPOSE_SERVICE cat /var/lib/postgresql/data/pg_hba.conf > "$BACKUP_DIR/pg_hba.conf" 2>/dev/null || {
    warning "Failed to backup pg_hba.conf"
}

# Step 9: Create backup summary
log "Creating backup summary..."
cat > "$BACKUP_DIR/backup_summary.txt" << EOF
PostgreSQL 13.4 Backup Summary
==============================
Backup Date: $(date)
Backup User: $BACKUP_USER
PostgreSQL Version: $PG_VERSION
Total Databases: $DB_COUNT

Backup Contents:
- full_cluster_backup.sql: Complete database cluster dump
- full_cluster_backup.sql.gz: Compressed cluster dump
- globals/roles.sql: All database roles and users
- globals/globals.sql: Global objects (tablespaces, etc.)
- individual/: Individual database backups in multiple formats
- postgresql.conf: PostgreSQL configuration
- pg_hba.conf: PostgreSQL authentication configuration
- database_list.txt: List of all databases
- database_sizes.txt: Size of each database

Individual Database Formats:
- *.sql: Plain SQL format (human-readable)
- *.backup: Custom compressed format (faster restore)
- *_dir/: Directory format (parallel restore capable)

To restore the entire cluster:
  docker compose exec -T postgres psql -U [superuser] < $BACKUP_DIR/full_cluster_backup.sql

To restore a specific database:
  createdb -U [superuser] [dbname]
  pg_restore -U [superuser] -d [dbname] $BACKUP_DIR/individual/[dbname].backup

EOF

# Step 10: Create a quick restore script
log "Creating restore helper script..."
cat > "$BACKUP_DIR/restore_guide.sh" << 'EOF'
#!/bin/bash
# PostgreSQL Restore Guide

echo "=== PostgreSQL Restore Guide ==="
echo ""
echo "1. To restore the entire cluster (all databases):"
echo "   docker compose exec -T postgres psql -U [superuser] < full_cluster_backup.sql"
echo ""
echo "2. To restore roles only:"
echo "   docker compose exec -T postgres psql -U [superuser] < globals/roles.sql"
echo ""
echo "3. To restore a specific database:"
echo "   # First create the database"
echo "   docker compose exec postgres createdb -U [superuser] [dbname]"
echo "   # Then restore it"
echo "   docker compose exec -T postgres pg_restore -U [superuser] -d [dbname] individual/[dbname].backup"
echo ""
echo "4. To list databases in this backup:"
echo "   cat database_list.txt"
echo ""
echo "Replace [superuser] with your PostgreSQL superuser (postgres, default, etc.)"
echo "Replace [dbname] with the actual database name"
EOF
chmod +x "$BACKUP_DIR/restore_guide.sh"

# Calculate backup size
BACKUP_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)

# Final summary
echo ""
echo -e "${GREEN}=== Backup Completed Successfully ===${NC}"
echo -e "${BLUE}Backup Location:${NC} $BACKUP_DIR"
echo -e "${BLUE}Backup Size:${NC} $BACKUP_SIZE"
echo -e "${BLUE}Databases Backed Up:${NC} $DB_COUNT"
echo -e "${BLUE}Backup User:${NC} $BACKUP_USER"
echo ""
echo -e "${YELLOW}Important files:${NC}"
echo "  - Full cluster backup: $BACKUP_DIR/full_cluster_backup.sql"
echo "  - Compressed backup: $BACKUP_DIR/full_cluster_backup.sql.gz"
echo "  - Individual databases: $BACKUP_DIR/individual/"
echo "  - Restore guide: $BACKUP_DIR/restore_guide.sh"
echo ""
echo -e "${GREEN}Backup completed at $(date)${NC}"