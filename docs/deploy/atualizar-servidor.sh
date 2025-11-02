#!/bin/bash
# Script para atualizar e limpar caches no servidor

cd /var/www/dispatcher-control

echo "=========================================="
echo "ATUALIZANDO E LIMPANDO CACHES"
echo "=========================================="
echo ""

# 1. Fazer pull das mudanças
echo "[1/6] Fazendo git pull..."
git pull origin main
echo ""

# 2. Instalar dependências se necessário
echo "[2/6] Verificando dependências..."
composer install --no-dev --optimize-autoloader --quiet
echo ""

# 3. Limpar TODOS os caches
echo "[3/6] Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
echo ""

# 4. Recriar cache de rotas (importante!)
echo "[4/6] Recriando cache de rotas..."
php artisan route:cache
echo ""

# 5. Otimizar aplicação
echo "[5/6] Otimizando aplicação..."
php artisan config:cache
php artisan optimize
echo ""

# 6. Verificar permissões
echo "[6/6] Verificando permissões..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo ""

echo "=========================================="
echo "CONCLUÍDO!"
echo "=========================================="
echo ""
echo "Verificar se há erros nos logs:"
echo "tail -n 50 storage/logs/laravel.log | grep -i error"

