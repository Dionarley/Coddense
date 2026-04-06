# Coddense

**A sanity way to maintain your codebase.** Coddense é uma interface web para mapeamento de repositórios de código PHP, extraindo classes, funções, traits e interfaces usando AST (Abstract Syntax Tree).

## Funcionalidades

- **Mapeamento de Repositórios**: Clone e análise automática de repositórios Git
- **Extração de Entidades**: Identifica classes, interfaces, traits e funções
- **Detalhes Ricos**: Extrai métodos, propriedades, parâmetros, tipos de retorno e namespaces
- **Interface Web**: Dashboard SPA com Vue 3 + Inertia.js + Tailwind CSS
- **Processamento Assíncrono**: Jobs em fila para processar repositórios grandes
- **API REST**: Endpoints para integração com outras ferramentas

## Tecnologias

- **Backend**: Laravel 13 + PHP 8.3
- **Frontend**: Vue 3 + Inertia.js + Tailwind CSS
- **Parser**: nikic/php-parser (AST)
- **Filas**: Laravel Queue

## Instalação

```bash
# Clonar repositório
git clone <repo-url>
cd Coddense

# Instalar dependências PHP
composer install

# Instalar dependências JS
npm install --legacy-peer-deps

# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Criar banco de dados SQLite
touch database/database.sqlite

# Executar migrations
php artisan migrate

# Compilar assets
npm run build
```

## Uso

### Desenvolvimento

```bash
# Iniciar servidor de desenvolvimento
php artisan serve

# Em outro terminal, iniciar worker de filas
php artisan queue:work
```

### Produção

```bash
npm run build
php artisan serve --host=0.0.0.0 --port=80
php artisan queue:work --tries=3
```

### Adicionar Repositório

1. Acesse a página inicial
2. Clique em "Mapear Novo Repo"
3. Informe nome e URL do repositório Git
4. O Coddense clonará e analisará automaticamente

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
curl -X POST http://localhost:8000/api/repositories \
  -H "Content-Type: application/json" \
  -d '{"name": "Meu Projeto", "remote_url": "https://github.com/user/repo.git"}'

# Listar repositórios
curl http://localhost:8000/api/repositories

# Ver entidades mapeadas
curl http://localhost:8000/api/repositories/1/entities
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

## Licença

MIT
