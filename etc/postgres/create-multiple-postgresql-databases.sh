#!/bin/bash

set -e
set -u

# Clean up deprecated configuration parameters first
function cleanup_deprecated_config() {
  echo "Cleaning up deprecated PostgreSQL configuration parameters..."

  if [ -f "$PGDATA/postgresql.conf" ]; then
    # Remove deprecated parameters that cause startup failures in PG 15+
    sed -i '/^[[:space:]]*stats_temp_directory/d' "$PGDATA/postgresql.conf"
    sed -i '/^[[:space:]]*checkpoint_segments/d' "$PGDATA/postgresql.conf"
    echo "  Removed deprecated configuration parameters"
  fi
}

function create_user_and_database() {
  local database=$1
  echo "  Creating user and database '$database'"
  psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-EOSQL
      CREATE USER $database;
      CREATE DATABASE $database;
      GRANT ALL PRIVILEGES ON DATABASE $database TO $database;
EOSQL
}

# Run config cleanup first
cleanup_deprecated_config

# Then create multiple databases if requested
if [ -n "$POSTGRES_MULTIPLE_DATABASES" ]; then
  echo "Multiple database creation requested: $POSTGRES_MULTIPLE_DATABASES"
  for db in $(echo $POSTGRES_MULTIPLE_DATABASES | tr ',' ' '); do
   create_user_and_database $db
  done
  echo "Multiple databases created"
fi