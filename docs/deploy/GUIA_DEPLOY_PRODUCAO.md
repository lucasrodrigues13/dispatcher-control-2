# Guia de Deploy em Produção

## 📋 Checklist Pré-Deploy

### Informações Necessárias
- [ ] IP do servidor
- [ ] Usuário SSH (geralmente `root` ou um usuário sudo)
- [ ] Senha SSH ou chave privada
- [ ] Domínio configurado
- [ ] Acesso ao painel de DNS (para verificar/ajustar se necessário)

---

## 🔍 FASE 1: Conectar e Explorar o Servidor

### 1.1 Conectar via SSH

```bash
# Windows (PowerShell ou CMD)
ssh usuario@IP_DO_SERVIDOR

# Ou com chave
ssh -i caminho/para/chave.pem usuario@IP_DO_SERVIDOR

# Exemplo:
ssh root@192.168.1.100
```

**Nota:** Se for primeira conexão, digite `yes` para aceitar a fingerprint.

### 1.2 Verificar o Ambiente Atual

```bash
# Verificar sistema operacional
cat /etc/os-release

# Verificar se tem Docker instalado
docker --version
docker-compose --version

# Verificar se tem PHP instalado
php -v

# Verificar se tem MySQL/MariaDB
mysql --version
# ou
mariadb --version

# Verificar se tem Nginx ou Apache
nginx -v
# ou
apache2 -v
# ou
httpd -v

# Verificar portas em uso
netstat -tulpn | grep LISTEN
# ou
ss -tulpn | grep LISTEN

# Verificar onde está a aplicação antiga
find / -name "artisan" 2>/dev/null
ls -la /var/www/
ls -la /home/
```

### 1.3 Localizar e Analisar Aplicação Antiga

```bash
# Procurar diretórios comuns de aplicações web
ls -la /var/www/html/
ls -la /var/www/
ls -la /opt/
ls -la /home/

# Se encontrar a aplicação antiga, verificar estrutura
cd /caminho/da/aplicacao/antiga
ls -la
cat .env 2>/dev/null || echo "Arquivo .env não encontrado ou não acessível"
```

---

## 🗄️ FASE 2: Trabalhar com o Banco de Dados

### 2.1 Conectar ao MySQL/MariaDB

```bash
# Tentar conectar como root (pode pedir senha)
mysql -u root -p

# Se não souber a senha, tente sem senha:
mysql -u root
```

### 2.2 Listar Bancos de Dados Existentes

```sql
-- Dentro do MySQL
SHOW DATABASES;
```

### 2.3 Fazer Backup (Opcional - Recomendado)

```bash
# Fora do MySQL, fazer backup de TODOS os bancos
mysqldump -u root -p --all-databases > backup_completo_$(date +%Y%m%d_%H%M%S).sql

# Ou backup de um banco específico (se souber o nome)
mysqldump -u root -p nome_do_banco > backup_banco_$(date +%Y%m%d_%H%M%S).sql
```

### 2.4 Deletar Banco Antigo e Criar Novo

```sql
-- Dentro do MySQL
-- ATENÇÃO: Isso vai deletar TUDO! Certifique-se do banco correto
DROP DATABASE IF EXISTS nome_do_banco_antigo;

-- Criar novo banco de dados
CREATE DATABASE dispatcher_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Criar usuário para a aplicação (substitua 'senha_segura' por uma senha forte)
CREATE USER 'dispatcher_user'@'localhost' IDENTIFIED BY 'senha_segura_aqui';
GRANT ALL PRIVILEGES ON dispatcher_control.* TO 'dispatcher_user'@'localhost';
FLUSH PRIVILEGES;

-- Verificar se foi criado
SHOW DATABASES;

-- Sair do MySQL
EXIT;
```

**📝 Anote:**
- Nome do banco: `dispatcher_control`
- Usuário do banco: `dispatcher_user`
- Senha do banco: `[sua senha]`

---

## 🗑️ FASE 3: Remover Aplicação Antiga

### 3.1 Parar Serviços (se aplicável)

```bash
# Se usar Docker
cd /caminho/da/aplicacao/antiga
docker-compose down

# Se usar systemd/services
systemctl stop nginx
systemctl stop apache2
systemctl stop php-fpm
```

### 3.2 Remover Aplicação Antiga

```bash
# Fazer backup da aplicação antiga (opcional mas recomendado)
mkdir -p /backup
cp -r /caminho/da/aplicacao/antiga /backup/app_antiga_$(date +%Y%m%d)

# Remover aplicação antiga
rm -rf /caminho/da/aplicacao/antiga
# ou
rm -rf /var/www/html/*
```

### 3.3 Limpar Arquivos Temporários

```bash
# Limpar cache do Laravel (se existir)
rm -rf /var/www/html/storage/framework/cache/*
rm -rf /var/www/html/storage/framework/views/*
rm -rf /var/www/html/bootstrap/cache/*
```

---

## 📦 FASE 4: Preparar e Fazer Upload da Nova Aplicação

### 4.1 Preparar Aplicação Localmente (na sua máquina)

```bash
# Na sua máquina local, no diretório do projeto
cd dispatcher-control

# Criar arquivo .env de produção (copie do .env.example)
cp .env.example .env.production

# Editar .env.production com as informações corretas:
# - APP_ENV=production
# - APP_DEBUG=false
# - APP_URL=https://seu-dominio.com
# - DB_DATABASE=dispatcher_control
# - DB_USERNAME=dispatcher_user
# - DB_PASSWORD=[sua senha]
# - etc...
```

### 4.2 Comprimir Aplicação

```bash
# Excluir arquivos desnecessários
# Criar .deployignore ou usar o .gitignore existente

# Criar arquivo compactado
# Windows
tar -czf dispatcher-control.tar.gz --exclude='.git' --exclude='node_modules' --exclude='vendor' --exclude='.env' .

# Ou usar 7zip/WinRAR no Windows para criar .zip
```

### 4.3 Upload para o Servidor

**Opção A: Usando SCP (via PowerShell/CMD)**

```bash
# Na sua máquina (Windows PowerShell)
scp dispatcher-control.tar.gz usuario@IP_DO_SERVIDOR:/tmp/

# Ou usando WinSCP (interface gráfica) - mais fácil no Windows
```

**Opção B: Usando Git (recomendado)**

```bash
# No servidor
cd /var/www/html
git clone https://seu-repositorio.git dispatcher-control
cd dispatcher-control
```

### 4.4 Extrair e Configurar no Servidor

```bash
# Se fez upload via SCP
cd /var/www/html
tar -xzf /tmp/dispatcher-control.tar.gz
cd dispatcher-control

# Configurar .env
cp .env.example .env
nano .env  # ou vi .env
```

**Configurar .env com:**
```env
APP_NAME="Dispatcher Control"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://seu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dispatcher_control
DB_USERNAME=dispatcher_user
DB_PASSWORD=sua_senha_aqui

# Configurações de email (usar SMTP real em produção)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Stripe (se usar)
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
```

---

## 🚀 FASE 5: Instalar Dependências e Configurar

### 5.1 Instalar Dependências PHP

```bash
cd /var/www/html/dispatcher-control

# Instalar Composer (se não tiver)
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Instalar dependências
composer install --optimize-autoloader --no-dev

# Gerar chave da aplicação
php artisan key:generate
```

### 5.2 Configurar Permissões

```bash
cd /var/www/html/dispatcher-control

# Definir dono correto (ajuste 'www-data' se necessário)
chown -R www-data:www-data /var/www/html/dispatcher-control

# Dar permissões corretas
chmod -R 755 /var/www/html/dispatcher-control
chmod -R 775 storage bootstrap/cache
```

### 5.3 Rodar Migrations e Seeders

```bash
cd /var/www/html/dispatcher-control

# Rodar migrations
php artisan migrate --force

# Rodar seeders iniciais
php artisan db:seed --class=PlansSeeder
php artisan db:seed --class=RolesSeeder  # (criar depois)
php artisan db:seed --class=PermissionsSeeder  # (criar depois)

# Ou rodar todos os seeders
php artisan db:seed --force
```

### 5.4 Otimizar Laravel

```bash
# Cache de configuração
php artisan config:cache

# Cache de rotas
php artisan route:cache

# Cache de views
php artisan view:cache

# Limpar cache
php artisan cache:clear
```

---

## 🌐 FASE 6: Configurar Web Server

### 6.1 Nginx (Recomendado)

```bash
# Criar configuração do site
nano /etc/nginx/sites-available/dispatcher-control
```

**Conteúdo da configuração Nginx:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name seu-dominio.com www.seu-dominio.com;
    
    # Redirecionar HTTP para HTTPS (se tiver SSL)
    # return 301 https://$server_name$request_uri;

    root /var/www/html/dispatcher-control/public;
    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;  # Ajustar versão PHP
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 120M;
}
```

```bash
# Ativar site
ln -s /etc/nginx/sites-available/dispatcher-control /etc/nginx/sites-enabled/

# Testar configuração
nginx -t

# Reiniciar Nginx
systemctl restart nginx
```

### 6.2 Apache (Alternativa)

```bash
# Criar configuração do site
nano /etc/apache2/sites-available/dispatcher-control.conf
```

**Conteúdo da configuração Apache:**

```apache
<VirtualHost *:80>
    ServerName seu-dominio.com
    ServerAlias www.seu-dominio.com
    DocumentRoot /var/www/html/dispatcher-control/public

    <Directory /var/www/html/dispatcher-control/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/dispatcher-control-error.log
    CustomLog ${APACHE_LOG_DIR}/dispatcher-control-access.log combined
</VirtualHost>
```

```bash
# Ativar site
a2ensite dispatcher-control.conf

# Ativar mod_rewrite
a2enmod rewrite

# Reiniciar Apache
systemctl restart apache2
```

---

## 🔒 FASE 7: Configurar SSL/HTTPS (Recomendado)

```bash
# Instalar Certbot
apt-get update
apt-get install certbot python3-certbot-nginx

# Gerar certificado SSL (Nginx)
certbot --nginx -d seu-dominio.com -d www.seu-dominio.com

# Renovação automática (já configurado por padrão)
certbot renew --dry-run
```

---

## ✅ FASE 8: Verificações Finais

### 8.1 Verificar Aplicação

```bash
# Verificar se a aplicação está acessível
curl http://localhost
curl http://seu-dominio.com

# Verificar logs em caso de erro
tail -f /var/www/html/dispatcher-control/storage/logs/laravel.log
tail -f /var/log/nginx/error.log
# ou
tail -f /var/log/apache2/error.log
```

### 8.2 Testar Funcionalidades

- [ ] Acessar domínio no navegador
- [ ] Testar login/cadastro
- [ ] Testar criação de registros (loads, carriers, etc.)
- [ ] Verificar permissões de storage (uploads funcionando)
- [ ] Testar email (se configurado)

### 8.3 Configurar Crontab (Laravel Scheduler)

```bash
# Editar crontab
crontab -e

# Adicionar linha (ajustar caminho se necessário)
* * * * * cd /var/www/html/dispatcher-control && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🛠️ COMANDOS ÚTEIS PÓS-DEPLOY

```bash
# Ver logs
tail -f /var/www/html/dispatcher-control/storage/logs/laravel.log

# Limpar cache
cd /var/www/html/dispatcher-control
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recriar cache de otimização
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verificar status dos serviços
systemctl status nginx
systemctl status mysql
systemctl status php8.2-fpm

# Reiniciar serviços
systemctl restart nginx
systemctl restart mysql
systemctl restart php8.2-fpm
```

---

## ⚠️ TROUBLESHOOTING

### Problema: Erro 500
- Verificar permissões: `chmod -R 775 storage bootstrap/cache`
- Verificar logs: `tail -f storage/logs/laravel.log`
- Verificar .env: garantir que APP_KEY está configurada

### Problema: Erro de Conexão com Banco
- Verificar credenciais no .env
- Verificar se MySQL está rodando: `systemctl status mysql`
- Testar conexão: `mysql -u dispatcher_user -p dispatcher_control`

### Problema: Página em Branco
- Verificar `APP_DEBUG=true` temporariamente
- Verificar permissões do storage
- Verificar PHP errors: `tail -f /var/log/php8.2-fpm.log`

---

## 📝 NOTAS FINAIS

1. **SEMPRE faça backup antes de deletar**
2. **Teste em ambiente de staging primeiro** (se possível)
3. **Documente todas as senhas e credenciais** em local seguro
4. **Configure monitoramento** (opcional mas recomendado)
5. **Configure backups automáticos do banco**

---

## 🔄 PRÓXIMOS PASSOS

Após deploy bem-sucedido:
1. [ ] Criar usuário administrador inicial
2. [ ] Configurar seeders (Roles, Permissions, Plans)
3. [ ] Testar todas as funcionalidades críticas
4. [ ] Configurar backups automáticos
5. [ ] Documentar credenciais e acesso