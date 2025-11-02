#!/bin/bash

# Manual PostgreSQL Authentication Fix
# Run these commands step by step if the automated script fails

echo "=== Manual PostgreSQL Authentication Fix ==="
echo ""
echo "Step 1: Connect to the PostgreSQL container"
echo "Run: docker compose exec postgres bash"
echo ""
echo "Step 2: Inside the container, temporarily disable authentication"
echo "Run the following commands:"
echo ""
cat << 'MANUAL_STEPS'
# Inside the container:

# 1. Edit pg_hba.conf to use trust authentication temporarily
sed -i.backup 's/scram-sha-256/trust/g' /var/lib/postgresql/data/pg_hba.conf

# 2. Reload PostgreSQL
psql -U postgres -c "SELECT pg_reload_conf();"

# 3. Connect as postgres user and fix the default user
psql -U postgres << EOF
-- Drop and recreate the user to ensure clean state
DROP USER IF EXISTS "default";
CREATE USER "default" WITH PASSWORD 'secret' SUPERUSER CREATEDB CREATEROLE REPLICATION;

-- Verify the user was created
SELECT usename, usesuper, usecreatedb, usecreaterole FROM pg_user WHERE usename = 'default';
EOF

# 4. Restore original authentication
mv /var/lib/postgresql/data/pg_hba.conf.backup /var/lib/postgresql/data/pg_hba.conf

# 5. Reload PostgreSQL again
psql -U postgres -c "SELECT pg_reload_conf();"

# 6. Exit the container
exit

MANUAL_STEPS

echo ""
echo "Step 3: Test the connection from outside the container"
echo "Run: docker compose exec postgres psql -U default -d postgres -c 'SELECT current_user;'"
echo ""
echo "If you still have issues, you might need to restart the container:"
echo "docker compose restart postgres"
echo ""