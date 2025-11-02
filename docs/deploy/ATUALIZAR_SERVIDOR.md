# Como Atualizar o Servidor Após Commits Locais

Este guia mostra como atualizar a aplicação no servidor após fazer commits no seu repositório local.

---

## 🔄 Processo de Atualização

### Opção 1: Git Pull (Recomendado - Atualiza sem perder mudanças locais)

```bash
cd /var/www/dispatcher-control

# Verificar status atual
git status

# Fazer pull das atualizações
git pull origin main

# OU se sua branch padrão for master:
# git pull origin master

# Atualizar dependências PHP (se composer.json mudou)
composer install --no-dev --optimize-autoloader

# Compilar assets (se package.json mudou)
npm install
npm run build

# Rodar migrations (se houver novas)
php artisan migrate --force

# Limpar e recriar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar Apache (se necessário)
systemctl reload apache2
```

---

### Opção 2: Git Reset (Força alinhamento com main - Perde mudanças locais)

**⚠️ CUIDADO:** Isso vai descartar qualquer mudança local no servidor!

```bash
cd /var/www/dispatcher-control

# Verificar status atual
git status

# Fazer fetch das atualizações
git fetch origin

# Resetar para o estado da main (PERDE mudanças locais)
git reset --hard origin/main

# OU se sua branch padrão for master:
# git reset --hard origin/master

# Limpar arquivos não rastreados (opcional - cuidado!)
# git clean -fd

# Atualizar dependências PHP
composer install --no-dev --optimize-autoloader

# Compilar assets
npm install
npm run build

# Rodar migrations
php artisan migrate --force

# Limpar e recriar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar Apache
systemctl reload apache2
```

---

### Opção 3: Script de Atualização Automatizado

Crie um script para facilitar:

```bash
# Criar script de atualização
nano /root/update-dispatcher.sh
```

**Cole este conteúdo:**

```bash
#!/bin/bash

APP_DIR="/var/www/dispatcher-control"
BRANCH="main"  # ou "master" se for sua branch padrão

echo "========================================"
echo "Atualizando Dispatcher Control"
echo "========================================"

cd "$APP_DIR" || exit 1

# Verificar se há mudanças locais não commitadas
if ! git diff-index --quiet HEAD --; then
    echo "⚠️  AVISO: Existem mudanças locais não commitadas!"
    echo "Deseja descartar essas mudanças? (s/n)"
    read -r response
    if [[ "$response" == "s" ]]; then
        git reset --hard HEAD
        git clean -fd
    else
        echo "Abortando atualização..."
        exit 1
    fi
fi

# Fazer fetch das atualizações
echo "1. Buscando atualizações..."
git fetch origin

# Resetar para o estado da main
echo "2. Atualizando código..."
git reset --hard "origin/$BRANCH"

# Atualizar dependências PHP
echo "3. Atualizando dependências PHP..."
composer install --no-dev --optimize-autoloader --no-interaction

# Compilar assets
echo "4. Compilando assets..."
npm install --silent
npm run build

# Rodar migrations
echo "5. Executando migrations..."
php artisan migrate --force

# Limpar e recriar cache
echo "6. Otimizando cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajustar permissões
echo "7. Ajustando permissões..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 storage bootstrap/cache

# Reiniciar Apache
echo "8. Reiniciando Apache..."
systemctl reload apache2

echo ""
echo "========================================"
echo "✅ Atualização concluída com sucesso!"
echo "========================================"
```

**Dar permissão de execução:**

```bash
chmod +x /root/update-dispatcher.sh
```

**Usar o script:**

```bash
/root/update-dispatcher.sh
```

---

## 🐛 Resolver Problemas com Git Pull

### Erro: "Your local changes would be overwritten"

**Solução 1: Descartar mudanças locais (se não forem importantes)**

```bash
cd /var/www/dispatcher-control
git reset --hard HEAD
git pull origin main
```

**Solução 2: Fazer stash das mudanças (guardar temporariamente)**

```bash
cd /var/www/dispatcher-control
git stash
git pull origin main
git stash pop  # Restaura mudanças depois (se quiser)
```

**Solução 3: Fazer commit das mudanças locais primeiro**

```bash
cd /var/www/dispatcher-control
git add .
git commit -m "Mudanças locais no servidor"
git pull origin main
# Resolver conflitos se houver
```

---

### Erro: "fatal: refusing to merge unrelated histories"

```bash
cd /var/www/dispatcher-control
git pull origin main --allow-unrelated-histories
```

---

### Erro: Conflitos de merge

```bash
cd /var/www/dispatcher-control

# Ver arquivos em conflito
git status

# Se quiser manter apenas o que vem do GitHub:
git reset --hard origin/main

# OU resolver conflitos manualmente:
# git mergetool
# Resolver conflitos nos arquivos
# git add .
# git commit
```

---

## 📋 Checklist de Atualização

Após fazer `git pull` ou `git reset`:

- [ ] Código atualizado (`git pull` ou `git reset`)
- [ ] Dependências PHP atualizadas (`composer install`)
- [ ] Assets compilados (`npm run build`)
- [ ] Migrations rodadas (`php artisan migrate`)
- [ ] Cache reconstruído (`config:cache`, `route:cache`, `view:cache`)
- [ ] Permissões corretas (`chown`, `chmod`)
- [ ] Apache reiniciado (`systemctl reload apache2`)
- [ ] Testado no navegador

---

## 🚀 Workflow Recomendado

### No seu computador local:

```bash
# Fazer suas mudanças
git add .
git commit -m "Minha alteração"
git push origin main
```

### No servidor:

```bash
cd /var/www/dispatcher-control
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build  # Se mudou assets
php artisan migrate --force  # Se houver novas migrations
php artisan config:cache
php artisan route:cache
php artisan view:cache
systemctl reload apache2
```

---

## 🔄 Para o Seu Caso Agora (Reset)

Execute estes comandos para alinhar com a main:

```bash
cd /var/www/dispatcher-control

# Verificar branch atual
git branch

# Buscar atualizações
git fetch origin

# Resetar para main (descartar mudanças locais)
git reset --hard origin/main

# Limpar arquivos não rastreados (opcional)
# git clean -fd

# Verificar se está alinhado
git status

# Atualizar dependências e cache
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar Apache
systemctl reload apache2
```

---

## 💡 Dica: Criar Alias para Facilitar

Adicione ao seu `.bashrc`:

```bash
nano ~/.bashrc

# Adicionar no final:
alias update-dispatcher='cd /var/www/dispatcher-control && git fetch origin && git reset --hard origin/main && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && systemctl reload apache2'

# Recarregar
source ~/.bashrc

# Usar depois:
update-dispatcher
```

---

**Execute o reset agora se quiser alinhar com a main!**

