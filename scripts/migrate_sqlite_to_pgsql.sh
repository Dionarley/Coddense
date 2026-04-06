#!/bin/bash
set -e

echo "=== Coddense - SQLite to PostgreSQL Migration ==="

BACKUP_FILE="database/backup_$(date +%Y%m%d_%H%M%S).sqlite"
MIGRATION_DIR="docker/migrations"

# Backup SQLite
echo "Backing up SQLite database..."
cp database/database.sqlite "$BACKUP_FILE"
echo "Backup saved to: $BACKUP_FILE"

# Create migration directory
mkdir -p "$MIGRATION_DIR"

# Export repositories
echo "Exporting repositories table..."
sqlite3 database/database.sqlite <<EOF
.mode insert repositories
.output $MIGRATION_DIR/01_repositories.sql
SELECT * FROM repositories;
EOF

# Export code_entities
echo "Exporting code_entities table..."
sqlite3 database/database.sqlite <<EOF
.mode insert code_entities
.output $MIGRATION_DIR/02_code_entities.sql
SELECT * FROM code_entities;
EOF

echo "Migration files created in $MIGRATION_DIR/"
echo ""
echo "Next steps:"
echo "1. docker compose up -d postgres"
echo "2. docker compose run --rm migrate"
echo "3. Import data: cat $MIGRATION_DIR/*.sql | docker compose exec -T postgres psql -U postgres -d coddense"
