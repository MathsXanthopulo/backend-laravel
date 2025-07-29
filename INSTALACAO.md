# Instruções de Instalação - Sistema de Redirects

## Problemas Comuns e Soluções

### 1. Erro: "Missing BC Math or GMP extension"

Este erro ocorre porque o Hashids precisa de uma das extensões BC Math ou GMP para funcionar.

**Solução para Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install php8.2-bcmath php8.2-gmp
```

**Solução para CentOS/RHEL:**
```bash
sudo yum install php-bcmath php-gmp
```

**Solução para macOS (com Homebrew):**
```bash
brew install php@8.2
# As extensões já vêm incluídas
```

### 2. Erro: "mbstring extension is not available"

**Solução para Ubuntu/Debian:**
```bash
sudo apt-get install php8.2-mbstring
```

**Solução para CentOS/RHEL:**
```bash
sudo yum install php-mbstring
```

### 3. Verificar extensões instaladas

```bash
php -m | grep -E "(bcmath|gmp|mbstring|xml|dom|json|tokenizer)"
```

### 4. Reiniciar serviços após instalação

**Apache:**
```bash
sudo systemctl restart apache2
```

**Nginx + PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

**Laravel Sail (Docker):**
```bash
./vendor/bin/sail restart
```

### 5. Configuração do PHP

Verifique se as extensões estão habilitadas no php.ini:

```bash
php --ini
```

Procure por linhas como:
```ini
extension=bcmath
extension=gmp
extension=mbstring
```

### 6. Teste de funcionamento

Após instalar as extensões, teste se tudo está funcionando:

```bash
# Teste do Hashids
php -r "echo 'Hashids funcionando: ' . (extension_loaded('bcmath') || extension_loaded('gmp') ? 'SIM' : 'NAO') . PHP_EOL;"

# Teste das outras extensões
php -r "echo 'mbstring: ' . (extension_loaded('mbstring') ? 'OK' : 'FALTA') . PHP_EOL;"
php -r "echo 'xml: ' . (extension_loaded('xml') ? 'OK' : 'FALTA') . PHP_EOL;"
php -r "echo 'dom: ' . (extension_loaded('dom') ? 'OK' : 'FALTA') . PHP_EOL;"
```

### 7. Executar testes

Após resolver os problemas de extensões:

```bash
php artisan test
```

### 8. Teste da API

```bash
# Criar um redirect de teste
curl -X POST http://localhost:8000/api/redirects \
  -H "Content-Type: application/json" \
  -d '{"destination_url": "https://httpbin.org/status/200"}'

# Listar redirects
curl http://localhost:8000/api/redirects
```

## Troubleshooting

### Se ainda houver problemas:

1. **Verifique a versão do PHP:**
```bash
php --version
```

2. **Verifique todas as extensões:**
```bash
php -m
```

3. **Verifique o arquivo de configuração:**
```bash
php --ini
```

4. **Teste com um script simples:**
```php
<?php
// test.php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "BCMath: " . (extension_loaded('bcmath') ? 'OK' : 'MISSING') . "\n";
echo "GMP: " . (extension_loaded('gmp') ? 'OK' : 'MISSING') . "\n";
echo "mbstring: " . (extension_loaded('mbstring') ? 'OK' : 'MISSING') . "\n";
```

Execute com:
```bash
php test.php
```

### Contato

Se ainda houver problemas, verifique:
- A versão do PHP (deve ser 8.1+)
- As permissões de instalação de pacotes
- O repositório de pacotes do sistema
- Os logs de erro do PHP.