# Ajuda do Coddense

## O que é o Coddense?

Coddense é uma ferramenta de mapeamento de código que analisa repositórios e extrai informações sobre classes, funções, traits, interfaces e mais de múltiplas linguagens usando AST e parsing.

---

## Começando

### 1. Mapeando um Repositório Git

1. Na página inicial, clique em **"Mapear Novo Repo"**
2. Selecione **"Repositório Git"**
3. Informe um nome para o repositório
4. Cole a URL do repositório (ex: `https://github.com/user/projeto.git`)
5. Clique em **"Mapear"**

O Coddense irá:
- Clonar o repositório
- Analisar arquivos de todas as linguagens suportadas
- Extrair classes, funções, traits, interfaces, tipos, enums
- Armazenar os dados no banco

### 2. Mapeando uma Pasta Local

1. Na página inicial, clique em **"Mapear Novo Repo"**
2. Selecione **"Pasta Local"**
3. Informe um nome para o repositório
4. Digite o caminho absoluto da pasta (ex: `/home/user/projeto/src`)
5. Clique em **"Mapear"**

**Nota:** O caminho deve ser absoluto e a pasta deve existir.

---

## Linguagens Suportadas

| Linguagem | Extensões | O que é extraído |
|-----------|------------|------------------|
| PHP | .php | classes, interfaces, traits, funções |
| JavaScript | .js, .jsx, .mjs, .cjs | classes, funções |
| TypeScript | .ts, .tsx | classes, interfaces, types, enums, funções |
| Python | .py | classes, funções |

---

## Entendendo os Status

| Status | Significado |
|--------|--------------|
| 🔴 Pendente | Repositório criado,aguardando processamento |
| 🟡 Processando |正在 analisando arquivos PHP |
| 🟢 Concluído | Mapeamento finalizado com sucesso |
| 🔴 Falhou | Erro durante o processamento |

---

## Visualizando o Mapeamento

Após o processamento ser concluído:

1. Clique em **"Ver Mapa"** no card do repositório
2. Use o painel lateral para navegar pelas entidades
3. Clique em uma entidade para ver detalhes:
   - Namespace
   - Métodos (com visibilidade e parâmetros)
   - Propriedades
   - Arquivo de origem

---

## Filtrando Entidades

Na página de visualização:

- **Barra de busca**: Filtre por nome da classe/função
- **Filtros por tipo**: Clique em `class`, `interface`, `trait`, `function` para mostrar/ocultar tipos

---

## API REST

### Endpoints Disponíveis

```bash
# Listar repositórios
GET /api/repositories

# Criar repositório
POST /api/repositories
# Body: {"name": "Meu Projeto", "remote_url": "https://github.com/user/repo.git"}

# Ver detalhes
GET /api/repositories/{id}

# Ver entidades mapeadas
GET /api/repositories/{id}/entities

# Deletar repositório
DELETE /api/repositories/{id}
```

### Exemplo com cURL

```bash
# Criar repositório
curl -X POST http://localhost:8000/api/repositories \
  -H "Content-Type: application/json" \
  -d '{"name": "Meu Projeto", "remote_url": "https://github.com/laravel/laravel"}'

# Listar entidades
curl http://localhost:8000/api/repositories/1/entities
```

---

## Solução de Problemas

### Repositório ficou em "Falhou"

1. Verifique se a URL do repositório está correta
2. Para pastas locais, verifique se o caminho existe
3. Verifique os logs em `storage/logs/laravel.log`

### Processo demorado demais

- Repositórios grandes levam mais tempo
- O processamento é feito em segundo plano
- Você pode monitorar o status na interface

### Quantidade de entidades pequena

- Verifique se o repositório tem arquivos PHP
- Alguns repositórios podem ter código em outras linguagens

---

## Comandos Úteis

### Desenvolvimento Local

```bash
# Iniciar servidor
php artisan serve

# Iniciar worker de filas
php artisan queue:work

# Ver logs
tail -f storage/logs/laravel.log
```

### Docker

```bash
# Subir serviços
docker compose up -d

# Ver logs
docker compose logs -f

# Parar serviços
docker compose down
```

---

## Perguntas Frequentes

**P: Preciso de internet para usar?**
R: Para repositórios Git, sim. Para pastas locais, não.

**P: Quais tipos de arquivos são analisados?**
R: Arquivos .php, .js, .jsx, .ts, .tsx, .py e suas variantes.

**P: Posso mapear qualquer repositório?**
R: Sim, qualquer repositório público ou URL de Git válida, ou pasta local.

**P: Os dados são salvos?**
R: Sim, no banco de dados (SQLite em desenvolvimento, PostgreSQL em produção).

**P: Como vejo as linguagens detectadas?**
R: Na página de visualização do repositório, os badges de linguagem aparecem abaixo do nome.

---

## Keyboard Shortcuts (Visualização)

| Tecla | Ação |
|-------|------|
| `Esc` | Voltar para dashboard |

---

Para mais informações, consulte o README.md ou AGENTS.md