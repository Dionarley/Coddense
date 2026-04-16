# Coddense - AGENTS.md

## Visão Geral
Coddense é uma aplicação Laravel para mapeamento de repositórios PHP usando AST.

## Stack
- PHP 8.3+ / Laravel 13
- Vue 3 + Inertia.js + Tailwind CSS v4
- nikic/php-parser para AST
- Docker + Docker Compose (produção)
- PostgreSQL 16 (Docker) / SQLite (desenvolvimento)

---

## Comandos de Build/Lint/Test

### Docker
```bash
# Subir todos os serviços
docker compose up -d

# Ver logs
docker compose logs -f

# Reconstruir imagem
docker compose up -d --build app

# Rodar migrations
docker compose run --rm migrate

# Parar serviços
docker compose down
```

### PHP/Laravel
```bash
# Todos os testes
php artisan test

# Teste específico (unidade)
php artisan test --testsuite=Unit

# Teste específico (feature)
php artisan test --testsuite=Feature

# Teste específico (Docker)
php artisan test --testsuite=Docker

# Teste único
php artisan test --filter=ProcessRepositoryJob

# PHPUnit direto
./vendor/bin/phpunit --filter=test_repository_creation

# Lint (Pint)
./vendor/bin/pint
./vendor/bin/pint --test

# Verificar sintaxe PHP
php -l app/Services/CodeParserService.php
```

### Frontend
```bash
# Build produção
npm run build

# Dev server
npm run dev

# Install dependências
npm install --legacy-peer-deps
```

---

## Convenções PHP (PSR-12 + Laravel)

### Imports
```php
// 1. Laravel Framework
use Illuminate\Support\Facades\Log;

// 2. Classes internas (App\*)
use App\Models\Repository;
use App\Services\CodeParserService;

// 3. Pacotes externos
use PhpParser\NodeFinder;

// 4. Classes nativa PHP
use Countable, ArrayIterator;
```

### Classes e Métodos
```php
// PascalCase para classes
class RepositoryController extends Controller
{
    // camelCase para métodos
    public function index(): \Illuminate\Http\Response
    {
        // código
    }
}
```

### Tipos e Type Hints
- Sempre usar tipos explícitos em métodos públicos
- Union types: `int|string`
- Nullable: `?string` ou `string|null`
- Return type: `void` quando não há retorno

### Tratamento de Erros
```php
try {
    // código
} catch (\Throwable $e) {
    Log::error('Mensagem', ['context' => $data]);
    throw $e;
}
```

### Jobs/Queues
- Usar `public int $repositoryId` ao invés de `Repository $repository`
- Sempre usar `escapeshellarg()` em comandos shell
- Implementar `failed()` para marcar status

### Naming Conventions
- Variáveis: `$repositoryId` (camelCase)
- Constantes: `MAX_RETRY_COUNT` (SCREAMING_SNAKE_CASE)
- Métodos: `processRepository()` (camelCase)
- Classes: `CodeParserService` (PascalCase)
- Arquivos: `CodeParserService.php` (PascalCase)

---

## Convenções Vue (Composition API)

### Script Setup
```vue
<script setup>
import { ref, computed, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    repositories: Array,
    flash: Object,
});

const showModal = ref(false);
const form = reactive({ name: '' });
</script>
```

### Template
- Tailwind CSS para styling
- Portuguese labels/buttons (interface em PT-BR)

---

## Estrutura de Pastas
```
app/
├── Http/Controllers/  # Controllers (web + API)
├── Jobs/              # Queue jobs
├── Models/            # Eloquent models
├── Services/           # Business logic
database/
├── migrations/       # DB migrations
resources/js/
├── Pages/            # Inertia pages (Vue)
tests/
├── Feature/          # Feature tests
├── Unit/             # Unit tests
├── Docker/           # Docker tests
docker/
├── nginx.conf       # Nginx config
├── supervisord.conf # Process manager
├── migrations/    # SQL migration data
scripts/
├── migrate_sqlite_to_pgsql.sh
├── import_to_pgsql.sh
```

---

## Padrões Importantes

### Models
- Located in: `app/Models/`
- Eloquent relationships defined as methods: `public function codeEntities(): HasMany`

### Services
- Lógica de negócio complexa vai em `app/Services/`
- Injetar via constructor ou método handle

### Jobs
- Sempre usar ID ao invés de modelo serializado
- Manter jobs idempotentes quando possível

### Docker (Produção)
- Usar PostgreSQL 16 Alpine
- Health checks em todos os serviços
- Profiles para migrations

### Segurança
- Nunca expor chaves (APP_KEY, DB_PASSWORD) em código
- Usar variáveis de ambiente
- .env sempre no .gitignore