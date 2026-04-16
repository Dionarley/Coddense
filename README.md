# Coddense

**A sanity way to maintain your codebase.** Coddense é uma interface web para mapeamento de repositórios de código PHP, extraindo classes, funções, traits e interfaces usando AST (Abstract Syntax Tree).

## Funcionalidades

- **Mapeamento de Repositórios**: Clone e análise automática de repositórios Git
- **Mapeamento Local**: Análise de pastas locais no sistema de arquivos
- **Extração de Entidades**: Identifica classes, interfaces, traits e funções
- **Detalhes Ricos**: Extrai métodos, propriedades, parâmetros, tipos de retorno e namespaces
- **Interface Web**: Dashboard SPA com Vue 3 + Inertia.js + Tailwind CSS
- **Processamento Assíncrono**: Jobs em fila para processar repositórios grandes
- **API REST**: Endpoints para integração com outras ferramentas

## Tecnologias

- **Backend**: Laravel 13 + PHP 8.3
- **Frontend**: Vue 3 + Inertia.js + Tailwind CSS v4
- **Parser**: nikic/php-parser (AST)
- **Banco**: PostgreSQL 16
- **Filas**: Laravel Queue (database)
- **Infra**: Docker + Docker Compose

## Instalação

### Com Docker (Recomendado)

```bash
# Clonar repositório
git clone <repo-url>
cd Coddense

# Copiar .env.docker
cp .env.docker .env

# Subir todos os serviços
docker compose up -d postgres
docker compose run --rm migrate
docker compose up -d app queue

# Acessar
open http://localhost:8080
```

### Manual (Desenvolvimento Local)

```bash
# Instalar dependências
composer install
npm install --legacy-peer-deps

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Banco de dados (PostgreSQL)
# Crie o banco 'coddense' antes de rodar as migrations
php artisan migrate

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
php artisan queue:work
```

## Uso

### Adicionar Repositório

1. Acesse a página inicial
2. Clique em "Mapear Novo Repo"
3. Informe nome e URL do repositório Git
4. O Coddense clonará e analisará automaticamente

### Docker Compose

```bash
# Iniciar todos os serviços
docker compose up -d

# Ver logs
docker compose logs -f

# Rodar migrations
docker compose run --rm migrate

# Parar serviços
docker compose down

# Reconstruir imagem
docker compose up -d --build app
```

### Manual

```bash
# Desenvolvimento (server + queue + logs + vite)
composer run dev

# Produção
npm run build
php artisan serve --host=0.0.0.0 --port=80
php artisan queue:work --tries=3
```

## API Endpoints

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/repositories` | Listar repositórios |
| POST | `/api/repositories` | Criar e processar repositório |
| GET | `/api/repositories/{id}` | Ver detalhes do repositório |
| DELETE | `/api/repositories/{id}` | Remover repositório |
| GET | `/api/repositories/{id}/entities` | Listar entidades mapeadas |

### Exemplo de Uso

```bash
# Criar repositório
curl -X POST http://localhost:8080/api/repositories \
  -H "Content-Type: application/json" \
  -d '{"name": "Meu Projeto", "remote_url": "https://github.com/user/repo.git"}'

# Listar repositórios
curl http://localhost:8080/api/repositories

# Ver entidades mapeadas
curl http://localhost:8080/api/repositories/1/entities
```

## Estrutura do Banco

### repositories
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do repositório |
| remote_url | string | URL do repositório Git |
| status | string | pending, processing, completed, failed |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

### code_entities
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| repository_id | bigint | FK para repositories |
| type | string | class, function, trait, interface |
| name | string | Nome da entidade |
| namespace | string | Namespace PHP (nullable) |
| file_path | string | Caminho do arquivo |
| details | json | Métodos, propriedades, parâmetros |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

## Testes

```bash
php artisan test
```

## Documentação

- [Ajuda do Usuário](./docs/help.md) - Guia completo de uso
- [AGENTS.md](./AGENTS.md) - Guia para desenvolvedores

## Licença

MIT
