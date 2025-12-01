#!/bin/bash

# Script de Diagnóstico para Erros no Laravel
# Execute no servidor: bash SCRIPT_DIAGNOSTICO.sh

echo "🔍 DIAGNÓSTICO DE ERROS LARAVEL"
echo "================================"
echo ""

# 1. Verificar permissões
echo "1️⃣ Verificando permissões..."
ls -la storage/ | head -5
ls -la storage/framework/ 2>/dev/null || echo "❌ storage/framework não existe"
ls -la storage/framework/sessions/ 2>/dev/null || echo "❌ storage/framework/sessions não existe"
ls -la storage/framework/views/ 2>/dev/null || echo "❌ storage/framework/views não existe"
echo ""

# 2. Verificar último erro completo
echo "2️⃣ Último erro completo do Laravel:"
echo "-----------------------------------"
tail -n 200 storage/logs/laravel.log | grep -A 50 "local.ERROR\|Exception\|Fatal" | tail -n 80
echo ""

# 3. Verificar configuração de sessão
echo "3️⃣ Configuração de sessão (.env):"
echo "-----------------------------------"
grep -E "SESSION_|APP_URL|APP_ENV" .env | head -10
echo ""

# 4. Verificar se storage existe e tem permissões corretas
echo "4️⃣ Verificando estrutura de storage:"
echo "-----------------------------------"
if [ ! -d "storage/framework/sessions" ]; then
    echo "⚠️ Criando storage/framework/sessions..."
    mkdir -p storage/framework/sessions
fi

if [ ! -d "storage/framework/views" ]; then
    echo "⚠️ Criando storage/framework/views..."
    mkdir -p storage/framework/views
fi

if [ ! -d "storage/framework/cache" ]; then
    echo "⚠️ Criando storage/framework/cache..."
    mkdir -p storage/framework/cache
fi

# 5. Verificar permissões
echo ""
echo "5️⃣ Ajustando permissões..."
chmod -R 775 storage bootstrap/cache 2>/dev/null
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null
echo "✅ Permissões ajustadas"
echo ""

# 6. Limpar caches
echo "6️⃣ Limpando caches..."
php artisan config:clear 2>/dev/null && echo "✅ Config cache limpo" || echo "❌ Erro ao limpar config cache"
php artisan cache:clear 2>/dev/null && echo "✅ Cache limpo" || echo "❌ Erro ao limpar cache"
php artisan view:clear 2>/dev/null && echo "✅ View cache limpo" || echo "❌ Erro ao limpar view cache"
php artisan route:clear 2>/dev/null && echo "✅ Route cache limpo" || echo "❌ Erro ao limpar route cache"
echo ""

# 7. Verificar PHP errors
echo "7️⃣ Verificando erros do PHP..."
tail -n 50 /var/log/apache2/error.log 2>/dev/null | tail -n 10 || echo "⚠️ Não foi possível acessar logs do Apache"
echo ""

# 8. Verificar se .env está correto
echo "8️⃣ Verificando variáveis críticas do .env:"
echo "-----------------------------------"
if grep -q "APP_ENV=production" .env; then
    echo "✅ APP_ENV=production"
else
    echo "⚠️ APP_ENV não está definido como production"
fi

if grep -q "APP_DEBUG=false" .env; then
    echo "✅ APP_DEBUG=false"
else
    echo "⚠️ APP_DEBUG pode estar como true (não recomendado em produção)"
fi

if grep -q "SESSION_DRIVER" .env; then
    echo "✅ SESSION_DRIVER configurado"
else
    echo "⚠️ SESSION_DRIVER não configurado"
fi
echo ""

# 9. Testar conexão com banco
echo "9️⃣ Testando conexão com banco de dados..."
php artisan tinker --execute="echo 'Conexão OK';" 2>/dev/null && echo "✅ Banco de dados OK" || echo "❌ Erro na conexão com banco"
echo ""

echo "✅ Diagnóstico concluído!"
echo ""
echo "📋 PRÓXIMOS PASSOS:"
echo "1. Copie o erro completo mostrado acima"
echo "2. Verifique se todas as permissões estão corretas"
echo "3. Se o erro persistir, execute: php artisan config:cache"

