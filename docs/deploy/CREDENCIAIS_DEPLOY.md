# Credenciais e Informações do Deploy

**Data do Deploy:** _______________

## 🖥️ Servidor

- **IP:** vmi2795941 (já configurado)
- **Sistema:** Ubuntu 24.04.3 LTS
- **Web Server:** Apache 2.4.58
- **PHP:** 8.3.6
- **MySQL:** 8.0.43

## 🌐 Domínio

- **URL:** https://app.abbrtransportandshipping.com
- **DocumentRoot:** `/var/www/projeto-xambra/public` → **Mudar para:** `/var/www/dispatcher-control/public`

## 🗄️ Banco de Dados MySQL

### Credenciais de Acesso Admin
- **Usuário:** `debian-sys-maint`
- **Senha:** `GeNSDwMkZZguaTAm`
- **Arquivo:** `/etc/mysql/debian.cnf`

### Banco de Dados Antigo
- **Nome:** `controle_de_cargas`
- **Status:** ❌ DELETADO
- **Backup:** `/root/backup_controle_de_cargas_20251101_165903.sql` (166KB)

### Banco de Dados Novo
- **Nome:** `dispatcher_control`
- **Usuário:** `dispatcher_user`
- **Senha:** `Disp@tch3R9182` 
- **Host:** `127.0.0.1`
- **Porta:** `3306`

## 📁 Diretórios

### Aplicação Antiga
- **Caminho:** `/var/www/projeto-xambra`
- **Status:** ❌ Remover/renomear após deploy

### Aplicação Nova
- **Caminho:** `/var/www/dispatcher-control`
- **DocumentRoot:** `/var/www/dispatcher-control/public`

## 🔐 Credenciais Aplicação (.env)

```
APP_NAME="Dispatcher Control"
APP_ENV=production
APP_KEY=_______________ ← Gerar com: php artisan key:generate
APP_DEBUG=false
APP_URL=https://app.abbrtransportandshipping.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dispatcher_control
DB_USERNAME=dispatcher_user
DB_PASSWORD=Disp@tch3R9182 ← Mesma senha do banco acima

MAIL_MAILER=smtp
MAIL_HOST=_______________
MAIL_PORT=587
MAIL_USERNAME=_______________
MAIL_PASSWORD=_______________
MAIL_FROM_ADDRESS=_______________

STRIPE_KEY=_______________
STRIPE_SECRET=_______________
```

## 📝 Comandos Úteis

### MySQL
```bash
# Acessar MySQL
mysql -u debian-sys-maint -pGeNSDwMkZZguaTAm

# Listar bancos
mysql -u debian-sys-maint -pGeNSDwMkZZguaTAm -e "SHOW DATABASES;"
```

### Apache
```bash
# Reiniciar Apache
systemctl restart apache2

# Ver logs
tail -f /var/log/apache2/error.log
```

### Laravel
```bash
# Gerar chave
php artisan key:generate

# Rodar migrations
php artisan migrate --force

# Rodar seeders
php artisan db:seed --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## ✅ Checklist Deploy

- [ ] Backup banco antigo feito
- [ ] Banco antigo deletado
- [ ] Novo banco criado
- [ ] Usuário MySQL criado
- [ ] Aplicação antiga removida/renomeada
- [ ] Nova aplicação no servidor
- [ ] .env configurado
- [ ] Dependências instaladas (composer install)
- [ ] Permissões configuradas (storage, bootstrap/cache)
- [ ] Migrations rodadas
- [ ] Seeders rodados
- [ ] Apache configurado/atualizado
- [ ] SSL funcionando
- [ ] Testado no navegador

## 📞 Contatos/Notas

- **Cliente:** _______________
- **Observações:** 
  - _______________
  - _______________

