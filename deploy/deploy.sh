#!/usr/bin/env bash
# Deploy do Invexa Frete — roda no VPS, dentro da pasta do projeto.
#
# Cria uma tag git antes de puxar código novo: um ponto de restauração
# barato pra CÓDIGO. Isso NÃO cobre rollback de banco de dados — se o
# deploy incluir migrations, revisar/reverter dado é manual (ver
# rollback.sh e a seção de restauração de backup no ROADMAP.md).
set -euo pipefail

TAG="deploy-$(date +%Y%m%d-%H%M%S)"
echo "==> Marcando ponto de restauração: $TAG"
git tag "$TAG"

echo "==> Puxando código novo"
git pull

echo "==> Instalando dependências"
composer install --optimize-autoloader --no-dev

echo "==> Reconstruindo cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Rodando migrations"
php artisan migrate --force

echo "==> Ajustando permissões"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "==> Reiniciando worker de fila"
php artisan queue:restart

echo ""
echo "==> Deploy concluído. Ponto de restauração salvo em: $TAG"
echo "    Para reverter o código (não o banco), rode: ./deploy/rollback.sh $TAG"
