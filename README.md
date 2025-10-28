# MyRoadbook

## Description
MyRoadbook est une application web développée dans le cadre d’un travail de fin d’études en développement web à l’Institut Saint-Laurent.  
Elle permet de créer, organiser et partager des voyages en ligne, en centralisant toutes les informations utiles : étapes, activités et checklists.  
L’objectif est de simplifier la planification des voyages tout en offrant une interface claire, moderne et agréable à utiliser.

---

## Stack technique
- **Backend :** Laravel 11
- **Frontend :** Vue.js 3 avec Inertia.js
- **CSS Framework :** Tailwind CSS
- **Authentification :** Laravel Fortify
- **Paiements (préintégré) :** Laravel Cashier (Stripe, non actif dans la version actuelle)
- **Base de données :** MySQL
- **Cartographie :** Mapbox API
- **Hébergement prévu :** OVHcloud

---

## Installation

### 1️⃣ Prérequis
Avant de commencer, assurez-vous d’avoir installé :
- PHP 8.2 ou supérieur
- Composer
- Node.js et NPM
- MySQL ou MariaDB

### 2️⃣ Cloner le projet
git clone https://github.com/votre-utilisateur/myroadbook.git  
cd myroadbook

### 3️⃣ Installer les dépendances
composer install  
npm install

### 4️⃣ Configurer l’environnement
Créer un fichier `.env` à partir du modèle fourni :  
cp .env.example .env

Configurer les accès à la base de données et les clés API :
```
DB_DATABASE=myroadbook
DB_USERNAME=root
DB_PASSWORD=
MAPBOX_KEY=your_mapbox_api_key
STRIPE_KEY=your_stripe_public_key
STRIPE_SECRET=your_stripe_secret_key
```

### 5️⃣ Générer la clé d’application
php artisan key:generate

### 6️⃣ Lancer les migrations
php artisan migrate --seed

### 7️⃣ Lancer le serveur local
php artisan serve  
npm run dev

Accédez ensuite au site sur :  
👉 [http://127.0.0.1:8000](http://127.0.0.1:8000)
