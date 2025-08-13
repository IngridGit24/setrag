#!/bin/bash

echo "🚂 SETRAG - Initialisation des données de test"
echo "=============================================="

# Vérifier que Python est installé
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Vérifier que les services sont démarrés
echo "⏳ Vérification des services..."

# Attendre que les services soient prêts
sleep 5

# Exécuter le script Python
echo "📊 Lancement de l'initialisation des données..."
python3 scripts/seed_data.py

echo ""
echo "✅ Script terminé !"
echo "🎯 Vous pouvez maintenant tester l'application complète."
