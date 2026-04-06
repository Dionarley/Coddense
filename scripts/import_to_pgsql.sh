#!/bin/bash
set -e

echo "=== Importing SQLite data to PostgreSQL ==="

# Check if PostgreSQL is running
if ! docker compose ps postgres | grep -q "Up"; then
    echo "Starting PostgreSQL..."
    docker compose up -d postgres
    sleep 5
fi

# Wait for PostgreSQL to be ready
echo "Waiting for PostgreSQL to be ready..."
until docker compose exec postgres pg_isready -U postgres > /dev/null 2>&1; do
    sleep 1
done

# Create database if not exists
docker compose exec -T postgres psql -U postgres -tc "SELECT 1 FROM pg_database WHERE datname = 'coddense'" | grep -q 1 || \
    docker compose exec -T postgres psql -U postgres -c "CREATE DATABASE coddense;"

# Run migrations first
echo "Running Laravel migrations..."
docker compose run --rm migrate

# Import data
echo "Importing repositories..."
cat docker/migrations/01_repositories.sql | docker compose exec -T postgres psql -U postgres -d coddense

echo "Importing code_entities..."
cat docker/migrations/02_code_entities.sql | docker compose exec -T postgres psql -U postgres -d coddense

# Reset sequences
echo "Resetting sequences..."
docker compose exec -T postgres psql -U postgres -d coddense <<EOF
SELECT setval('repositories_id_seq', (SELECT MAX(id) FROM repositories));
SELECT setval('code_entities_id_seq', (SELECT MAX(id) FROM code_entities));
EOF

echo "Migration complete!"
echo "Verifying data..."
docker compose exec -T postgres psql -U postgres -d coddense -c "SELECT COUNT(*) as repos FROM repositories;"
docker compose exec -T postgres psql -U postgres -d coddense -c "SELECT COUNT(*) as entities FROM code_entities;"
