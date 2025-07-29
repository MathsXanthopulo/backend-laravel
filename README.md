# Sistema de Redirects - Laravel

Um sistema completo de redirecionamento de URLs com estatísticas de acesso, desenvolvido em Laravel.

## Funcionalidades

- ✅ **CRUD de Redirects**: Criação, listagem, atualização e exclusão de redirects
- ✅ **Proteção de IDs**: Uso do Hashids para ocultar IDs na API
- ✅ **Redirecionamento**: Sistema de redirecionamento com merge de query params
- ✅ **Estatísticas**: Estatísticas detalhadas de acesso
- ✅ **Logs**: Registro completo de acessos
- ✅ **Validação**: Validação robusta de URLs com verificação de status HTTP
- ✅ **Testes**: Testes unitários e de integração completos

## Requisitos

- PHP 8.1+
- Laravel 9.x
- MySQL/PostgreSQL
- Extensões PHP: bcmath, gmp, mbstring, xml, dom, json, tokenizer

## Instalação

1. **Clone o repositório**
```bash
git clone <repository-url>
cd backend-test
```

2. **Instale as dependências**
```bash
composer install
```

3. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure o banco de dados no .env**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=redirects_db
DB_USERNAME=root
DB_PASSWORD=
```

5. **Execute as migrations**
```bash
php artisan migrate
```

6. **Instale extensões PHP necessárias (Ubuntu/Debian)**
```bash
sudo apt-get install php8.2-bcmath php8.2-gmp php8.2-mbstring php8.2-xml php8.2-dom php8.2-json php8.2-tokenizer
```

7. **Execute os seeders (opcional)**
```bash
php artisan db:seed --class=RedirectSeeder
```

## Uso da API

### 1. Criar um Redirect

```bash
curl -X POST http://localhost:8000/api/redirects \
  -H "Content-Type: application/json" \
  -d '{"destination_url": "https://google.com"}'
```

**Resposta:**
```json
{
  "message": "Redirect criado com sucesso",
  "data": {
    "code": "ABC123",
    "destination_url": "https://google.com",
    "query_params": null,
    "is_active": true
  }
}
```

### 2. Listar Redirects

```bash
curl http://localhost:8000/api/redirects
```

**Resposta:**
```json
{
  "data": [
    {
      "code": "ABC123",
      "status": "ativo",
      "destination_url": "https://google.com",
      "last_accessed_at": "2024-01-15T10:30:00.000000Z",
      "created_at": "2024-01-15T10:00:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  ]
}
```

### 3. Redirecionamento

```bash
curl -I http://localhost:8000/r/ABC123
```

### 4. Estatísticas

```bash
curl http://localhost:8000/api/redirects/ABC123/stats
```

**Resposta:**
```json
{
  "data": {
    "total_accesses": 150,
    "unique_accesses": 45,
    "top_referrers": [
      {
        "referer": "https://google.com",
        "count": 25
      }
    ],
    "last_10_days": [
      {
        "date": "2024-01-15",
        "total": 15,
        "unique": 8
      }
    ]
  }
}
```

### 5. Logs de Acesso

```bash
curl http://localhost:8000/api/redirects/ABC123/logs
```

## Estrutura do Banco de Dados

### Tabela `redirects`
- `id` - ID único (auto-incremento)
- `destination_url` - URL de destino
- `query_params` - Parâmetros de query (opcional)
- `is_active` - Status ativo/inativo
- `last_accessed_at` - Último acesso
- `created_at` - Data de criação
- `updated_at` - Data de atualização
- `deleted_at` - Soft delete

### Tabela `redirect_logs`
- `id` - ID único (auto-incremento)
- `redirect_id` - Chave estrangeira para redirects
- `ip_address` - IP do usuário
- `user_agent` - User agent do navegador
- `referer` - URL de referência
- `query_params` - Parâmetros da requisição
- `created_at` - Data do acesso

## Validações

### URL de Destino
- ✅ Deve ser uma URL válida
- ✅ Deve usar HTTPS
- ✅ Não pode apontar para a própria aplicação
- ✅ Deve retornar status 200 ou 201
- ✅ Deve ser acessível

### Query Parameters
- ✅ Merge automático de parâmetros
- ✅ Prioridade para parâmetros da requisição
- ✅ Ignora valores vazios
- ✅ Preserva parâmetros do redirect quando não conflitam

## Testes

Execute os testes com:

```bash
php artisan test
```

### Cobertura de Testes
- ✅ Criação de redirects
- ✅ Validação de URLs
- ✅ Redirecionamento
- ✅ Estatísticas
- ✅ Logs de acesso
- ✅ Merge de query parameters

## Rotas da API

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/redirects` | Listar redirects |
| POST | `/api/redirects` | Criar redirect |
| GET | `/api/redirects/{code}` | Mostrar redirect |
| PUT | `/api/redirects/{code}` | Atualizar redirect |
| DELETE | `/api/redirects/{code}` | Deletar redirect |
| GET | `/api/redirects/{code}/stats` | Estatísticas |
| GET | `/api/redirects/{code}/logs` | Logs de acesso |
| GET | `/r/{code}` | Redirecionamento |

## Características Técnicas

### Hashids
- Proteção de IDs usando Hashids
- Códigos únicos e seguros
- Reversível para busca interna

### Soft Delete
- Exclusão lógica de redirects
- Preserva histórico
- Desativa automaticamente

### Performance
- Índices otimizados
- Queries eficientes
- Paginação em logs

### Segurança
- Validação robusta
- Sanitização de dados
- Proteção contra URLs maliciosas


