#!/bin/bash
set -e

PROJECT_DIR="/opt/lampp/htdocs/apprh"
cd "$PROJECT_DIR"

echo "=== 1. Vérification des clés Twilio dans .env ==="
if grep -q "^TWILIO_SID=" .env && grep -q "^TWILIO_AUTH_TOKEN=" .env && grep -q "^TWILIO_FROM=" .env; then
    echo "✅ Les clés TWILIO_SID / TWILIO_AUTH_TOKEN / TWILIO_FROM sont présentes dans .env"
else
    echo "⚠️  Clés Twilio manquantes dans .env — ajoute-les avant de continuer :"
    echo ""
    echo "TWILIO_SID=ton_account_sid"
    echo "TWILIO_AUTH_TOKEN=ton_auth_token"
    echo "TWILIO_FROM=ton_numero_twilio"
    echo ""
    read -p "Appuie sur Entrée une fois que c'est fait (ou Ctrl+C pour annuler)..."
fi

echo ""
echo "=== 2. Nettoyage des caches Laravel ==="
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo ""
echo "=== 3. Vérification que les fichiers SMS sont bien en place ==="
for f in "app/Services/SmsService.php" "app/Http/Controllers/TabdepotController.php" "config/services.php"; do
    if [ -f "$f" ]; then
        echo "✅ $f trouvé"
    else
        echo "❌ $f MANQUANT — télécharge-le et place-le à cet emplacement avant de continuer."
    fi
done

echo ""
echo "=== 4. Lancement du serveur ==="
php artisan serve
