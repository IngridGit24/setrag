# SETRAG - Plateforme de Transport Ferroviaire

Une plateforme complète pour la réservation de billets de train et la gestion des expéditions de colis pour SETRAG (Société d'Exploitation du Transgabonais).

## 🚀 Fonctionnalités

### Frontend (React + Tailwind CSS)
- **Design moderne** : Interface utilisateur responsive avec Tailwind CSS
- **Navigation intuitive** : Header avec boutons de navigation (Précédent/Accueil)
- **Footer fixe** : Footer toujours visible en bas de page
- **Bouton "Go to Top"** : Retour en haut de page lors du scroll
- **Thème SETRAG** : Couleurs et design personnalisés

### 🎫 Système de Réservation Avancé
- **Formulaire de recherche intelligent** :
  - Gare de départ/arrivée avec autocomplétion
  - Type de voyage (Aller simple/Aller-retour)
  - Sélection de date avec validation
  - Code promo optionnel
- **Sélection de siège interactive** : Grille visuelle des sièges disponibles
- **Calcul de prix en temps réel** : Prix de base + commission 5%
- **Flux de réservation optimisé** : Accessible aux utilisateurs connectés et non connectés

### 💳 Système de Paiement
- **Page de paiement complète** :
  - Informations passager (pour utilisateurs non connectés)
  - Choix du mode de paiement (Mobile Money/Carte bancaire)
  - Validation des données en temps réel
- **Options de paiement** :
  - **Mobile Money** : Airtel Money, Moov Money
  - **Carte bancaire** : Visa, Mastercard
- **Sécurité** : Validation des champs et gestion des erreurs

### 📄 Facturation et Confirmation
- **Page de succès professionnelle** :
  - Facture complète avec tous les détails
  - Référence PNR unique
  - Instructions d'embarquement
  - Informations de contact SETRAG
- **Téléchargement de facture** : Format texte (.txt) avec nom personnalisé
- **Design responsive** : Optimisé pour tous les appareils

### 🔐 Authentification Flexible
- **Réservation sans compte** : Possibilité de réserver sans s'inscrire
- **Avantages utilisateurs connectés** : Informations pré-remplies
- **Message d'encouragement** : Invitation friendly à s'inscrire pour les promotions

### Services Backend
- **Authentification** : Gestion des utilisateurs et JWT
- **Réservation** : Système complet de réservation de billets
- **Suivi temps réel** : Position des trains en direct
- **Expédition** : Gestion des colis et fret
- **Pricing** : Calcul des prix avec commission 5%

## 🛠️ Technologies

### Frontend
- **React 18** avec TypeScript
- **Tailwind CSS** pour le styling
- **React Router** pour la navigation
- **Leaflet** pour les cartes interactives

### Backend
- **Python FastAPI** pour les microservices
- **SQLite** pour la persistance locale
- **JWT** pour l'authentification
- **WebSocket** pour les mises à jour temps réel

## 📋 Prérequis

- Docker (optionnel, pour l'environnement complet)
- Python 3.8+
- Node.js 16+
- npm ou yarn

## 🚀 Démarrage rapide

### Option 1: Avec Docker (Recommandé)

```bash
# Cloner le projet
git clone <repository-url>
cd setrag

# Démarrer tous les services
docker-compose up -d

# Accéder à l'application
open http://localhost:5173
```

### Option 2: Développement local

```bash
# 1. Démarrer les services backend
./start.sh

# 2. Dans un nouveau terminal, démarrer le frontend
cd apps/web-b2c
npm install
npm run dev

# 3. Initialiser les données de test
./scripts/seed_data.sh
```

## 📊 Données de test

Le projet inclut un script d'initialisation qui crée automatiquement :

### 👥 Utilisateurs de test
- `admin@setrag.ga` / `admin123` (Admin SETRAG)
- `john.doe@example.com` / `password123` (John Doe)
- `marie.dupont@example.com` / `password123` (Marie Dupont)
- `pierre.martin@example.com` / `password123` (Pierre Martin)

### 🏢 Stations
- Libreville (LBV)
- Franceville (FRV)
- Moanda (MOA)
- Lambaréné (LAM)
- Port-Gentil (POG)

### 🚂 Voyages
- Libreville ↔ Franceville (25,000 FCFA)
- Libreville ↔ Moanda (15,000 FCFA)
- Libreville ↔ Owendo (5,000 FCFA)
- Plusieurs horaires par jour

### 📍 Positions de trains
- 3 trains en circulation avec positions GPS réelles

## 🧪 Tests End-to-End

Pour tester l'application complète :

1. **Démarrer les services** : `./start.sh`
2. **Initialiser les données** : `./scripts/seed_data.sh`
3. **Accéder au frontend** : `http://localhost:5173`
4. **Tester le nouveau flux de réservation** :
   - Aller sur `/book`
   - Remplir le formulaire de recherche (Libreville → Franceville)
   - Sélectionner un voyage et un siège
   - Cliquer "Procéder au paiement"
   - Remplir les informations passager (si non connecté)
   - Choisir le mode de paiement
   - Confirmer le paiement
   - Voir la page de succès avec facture
   - Télécharger la facture

## 💰 Structure des Prix

### Tarification
- **Libreville ↔ Franceville** : 25,000 FCFA
- **Libreville ↔ Moanda** : 15,000 FCFA
- **Libreville ↔ Owendo** : 5,000 FCFA

### Commission
- **Taux** : 5% du prix de base
- **Utilisation** : Entretien de l'application et paiement des opérateurs
- **Transparence** : Affichée clairement dans les détails

## 📁 Structure du projet

```
setrag/
├── apps/
│   └── web-b2c/                 # Frontend React
│       ├── src/
│       │   ├── components/      # Composants réutilisables
│       │   │   ├── Header.tsx   # En-tête de page avec navigation
│       │   │   └── GoToTop.tsx  # Bouton retour en haut
│       │   ├── routes/          # Pages de l'application
│       │   │   ├── Book.tsx     # Page de réservation avec formulaire
│       │   │   ├── Payment.tsx  # Page de paiement complète
│       │   │   ├── Success.tsx  # Page de succès avec facture
│       │   │   └── ...
│       │   └── ...
│       └── package.json
├── services/
│   ├── users/                   # Service d'authentification
│   ├── inventory-py/            # Gestion des voyages et sièges
│   ├── pricing-booking-py/      # Calcul des prix et réservations
│   ├── tracking/                # Suivi temps réel des trains
│   └── ai-agent/                # Agent IA (en développement)
├── scripts/
│   ├── seed_data.py             # Script d'initialisation des données
│   └── seed_data.sh             # Wrapper shell
├── infra/
│   └── docker/                  # Configuration Docker
├── docs/                        # Documentation API
└── README.md
```

## 🔧 Configuration

### Variables d'environnement

```bash
# Services backend
TRACKING_URL=http://localhost:8001
USERS_URL=http://localhost:8104
INVENTORY_URL=http://localhost:8105
PRICING_URL=http://localhost:8106

# Frontend
VITE_API_BASE_URL=http://localhost:8104
```

## 🚀 Déploiement

### Production avec Docker

```bash
# Build des images
docker-compose -f docker-compose.prod.yml build

# Déploiement
docker-compose -f docker-compose.prod.yml up -d
```

### Kubernetes (Futur)

```bash
kubectl apply -f k8s/
```

## 📈 Monitoring

- **Health checks** : `/health` sur chaque service
- **Logs** : Logs structurés JSON
- **Métriques** : Prometheus (en développement)

## 🎯 Fonctionnalités Récentes

### ✅ Améliorations UI/UX
- Migration de Material-UI vers Tailwind CSS
- Header de page avec navigation (Précédent/Accueil)
- Footer fixe avec message personnalisé
- Bouton "Go to Top" pour améliorer la navigation
- Design responsive et moderne

### ✅ Système de Réservation Optimisé
- Formulaire de recherche avancé
- Sélection de siège interactive
- Calcul de prix en temps réel
- Flux accessible aux utilisateurs non connectés

### ✅ Système de Paiement Complet
- Page de paiement professionnelle
- Support Mobile Money et Cartes bancaires
- Validation des données en temps réel
- Gestion des erreurs robuste

### ✅ Facturation Professionnelle
- Page de succès avec facture complète
- Téléchargement de facture au format texte
- Référence PNR unique
- Instructions d'embarquement

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👨‍💻 Développé avec amour par

**Ingrid** - This app has been build with love by Ingrid ❤️

---

🚂 **SETRAG** - Votre partenaire de confiance pour le transport ferroviaire au Gabon


