#!/usr/bin/env bash
# Reverte o CÓDIGO do Invexa Frete pra uma tag de deploy anterior (criada
# por deploy.sh). NÃO desfaz migrations automaticamente — reverter
# migration em produção às cegas é mais arriscado do que não reverter
# nada. Se o deploy problemático rodou migrations, o caminho seguro é
# restaurar um backup de banco de antes do deploy (ver ROADMAP.md, seção
# Backup) em vez de tentar desfazer a migration.
set -euo pipefail

if [ -z "${1:-}" ]; then
  echo "Uso: ./rollback.sh <tag-de-deploy>"
  echo ""
  echo "Tags disponíveis (mais recentes primeiro):"
  git tag --list "deploy-*" --sort=-creatordate | head -10
  exit 1
fi

TAG="$1"

echo "==> Migrations que rodaram desde $TAG (revise antes de continuar):"
git diff "$TAG" HEAD --name-only -- database/migrations || true
echo ""

read -r -p "Continuar com o rollback de código? [y/N] " CONFIRMA
if [ "$CONFIRMA" != "y" ] && [ "$CONFIRMA" != "Y" ]; then
  echo "Cancelado."
  exit 1
fi

echo "==> Revertendo código para $TAG"
git checkout "$TAG"

echo "==> Reinstalando dependências da versão anterior"
composer install --optimize-autoloader --no-dev

echo "==> Reconstruindo cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Ajustando permissões"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "==> Reiniciando worker de fila"
php artisan queue:restart

echo ""
echo "==> Rollback de código concluído. Você está num estado 'detached HEAD'"
echo "    (rodando a tag $TAG, não o branch main)."
echo "    Se precisar reverter dado também, use o procedimento de restauração"
echo "    de backup documentado no ROADMAP.md — não tente desfazer migration."
echo "    Para voltar ao branch main mais tarde: git checkout main"
