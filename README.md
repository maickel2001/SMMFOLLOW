# SMM Pro - Site Web de Services de Marketing sur les Réseaux Sociaux

Un site web moderne et professionnel pour vendre des services SMM (Social Media Marketing) incluant des followers, likes et vues pour Instagram, TikTok, YouTube et Facebook.

## 🚀 Fonctionnalités

### Frontend
- **Page d'accueil moderne** avec design sombre et accents verts
- **Navigation responsive** avec menu hamburger pour mobile
- **Section services** présentant les différentes catégories
- **Formulaire de commande** dynamique avec calcul automatique des prix
- **Page de paiement** avec instructions Mobile Money et upload de preuve
- **Design mobile-first** optimisé pour tous les appareils

### Backend
- **Base de données MySQL** complète avec relations
- **Système d'authentification admin** sécurisé
- **Dashboard administrateur** avec statistiques
- **Gestion des commandes** avec filtres et actions
- **Gestion des services** (ajout, modification, suppression)
- **Gestion des catégories** avec icônes Font Awesome
- **Upload sécurisé** des preuves de paiement

### Sécurité
- **Protection SQL injection** avec requêtes préparées
- **Validation des entrées** utilisateur
- **Sessions sécurisées** pour l'administration
- **Mots de passe hashés** avec bcrypt
- **Filtrage des fichiers** uploadés

## 🛠️ Technologies Utilisées

- **PHP 7.4+** - Backend et logique métier
- **MySQL 5.7+** - Base de données
- **Bootstrap 5.3** - Framework CSS responsive
- **Font Awesome 6.4** - Icônes et symboles
- **Google Fonts (Poppins)** - Typographie moderne
- **JavaScript ES6+** - Interactions côté client

## 📁 Structure du Projet

```
smm-website/
├── admin/                 # Interface d'administration
│   ├── login.php         # Connexion admin
│   ├── dashboard.php     # Tableau de bord principal
│   ├── orders.php        # Gestion des commandes
│   ├── order-details.php # Détails d'une commande
│   ├── services.php      # Gestion des services
│   ├── categories.php    # Gestion des catégories
│   └── logout.php        # Déconnexion
├── assets/
│   └── css/
│       └── style.css     # Styles personnalisés
├── config/
│   └── database.php      # Configuration base de données
├── database/
│   └── schema.sql        # Schéma et données initiales
├── includes/
│   └── functions.php     # Fonctions utilitaires
├── uploads/              # Dossier des preuves de paiement
├── index.php             # Page d'accueil
├── commander.php         # Page de commande
├── paiement.php          # Page de paiement
└── README.md             # Documentation
```

## 🚀 Installation

### Prérequis
- Serveur web (Apache/Nginx) avec PHP 7.4+
- MySQL 5.7+ ou MariaDB 10.2+
- Extension PHP : PDO, PDO_MySQL, GD (pour l'upload d'images)

### Étapes d'installation

1. **Cloner ou télécharger** le projet dans votre dossier web
   ```bash
   git clone [url-du-repo] smm-website
   cd smm-website
   ```

2. **Créer la base de données**
   ```sql
   CREATE DATABASE smm_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Importer le schéma**
   ```bash
   mysql -u root -p smm_website < database/schema.sql
   ```

4. **Configurer la base de données**
   - Éditer `config/database.php`
   - Modifier les constantes selon votre configuration :
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'smm_website');
     define('DB_USER', 'votre_utilisateur');
     define('DB_PASS', 'votre_mot_de_passe');
     ```

5. **Créer le dossier uploads**
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

6. **Configurer les permissions**
   ```bash
   chmod 644 config/database.php
   chmod 644 includes/functions.php
   ```

### Compte administrateur par défaut
- **Email** : admin@smm.com
- **Mot de passe** : admin123

⚠️ **Important** : Changez le mot de passe après la première connexion !

## 🔧 Configuration

### Personnalisation des services
1. Se connecter à l'interface admin (`/admin/login.php`)
2. Aller dans "Services" pour ajouter/modifier des services
3. Aller dans "Catégories" pour gérer les catégories

### Personnalisation des informations de contact
Modifier les informations de contact dans `index.php` :
- Numéro WhatsApp
- Email de contact
- Instructions de paiement

### Personnalisation du design
Le thème peut être personnalisé en modifiant les variables CSS dans `assets/css/style.css` :
```css
:root {
    --primary-color: #00ff88;      /* Couleur principale */
    --secondary-color: #00cc6a;    /* Couleur secondaire */
    --dark-bg: #0a0a0a;           /* Arrière-plan sombre */
    /* ... autres variables */
}
```

## 📱 Utilisation

### Pour les clients
1. **Parcourir les services** sur la page d'accueil
2. **Sélectionner un service** et cliquer sur "Commander"
3. **Remplir le formulaire** avec les informations requises
4. **Choisir la méthode de paiement** (MTN Money ou Moov Money)
5. **Effectuer le paiement** selon les instructions
6. **Uploader la preuve** de paiement
7. **Attendre la confirmation** et le traitement

### Pour l'administrateur
1. **Se connecter** à l'interface admin
2. **Consulter le dashboard** pour voir les statistiques
3. **Gérer les commandes** :
   - Voir les détails
   - Mettre à jour les statuts
   - Ajouter des notes
   - Annuler si nécessaire
4. **Gérer les services** :
   - Ajouter de nouveaux services
   - Modifier les prix et descriptions
   - Activer/désactiver des services
5. **Gérer les catégories** :
   - Créer de nouvelles catégories
   - Modifier les icônes et descriptions

## 🔒 Sécurité

### Bonnes pratiques implémentées
- Requêtes préparées pour éviter les injections SQL
- Validation et nettoyage des entrées utilisateur
- Sessions sécurisées avec vérification d'authentification
- Hashage des mots de passe avec bcrypt
- Filtrage des types de fichiers uploadés
- Protection contre l'accès direct aux fichiers sensibles

### Recommandations supplémentaires
- Utiliser HTTPS en production
- Configurer un pare-feu applicatif (WAF)
- Mettre en place une sauvegarde automatique de la base
- Surveiller les logs d'erreur
- Maintenir PHP et MySQL à jour

## 🚀 Déploiement en Production

### Optimisations recommandées
1. **Activer le cache PHP** (OPcache)
2. **Configurer la compression GZIP**
3. **Optimiser les images** et utiliser WebP
4. **Mettre en place un CDN** pour les assets statiques
5. **Configurer la mise en cache** des pages

### Configuration serveur
```apache
# .htaccess pour Apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Sécurité
<Files "config/*">
    Order allow,deny
    Deny from all
</Files>
```

## 🐛 Dépannage

### Problèmes courants

**Erreur de connexion à la base de données**
- Vérifier les paramètres dans `config/database.php`
- S'assurer que MySQL est démarré
- Vérifier les permissions de l'utilisateur

**Problème d'upload d'images**
- Vérifier les permissions du dossier `uploads/`
- Vérifier la configuration PHP (upload_max_filesize, post_max_size)
- S'assurer que l'extension GD est activée

**Erreur 500**
- Vérifier les logs d'erreur du serveur
- Vérifier la syntaxe PHP
- S'assurer que toutes les extensions requises sont activées

## 📞 Support

Pour toute question ou problème :
- Consulter la documentation
- Vérifier les logs d'erreur
- Tester sur un environnement de développement

## 📄 Licence

Ce projet est fourni à des fins éducatives et commerciales. Libre d'utilisation et de modification.

## 🔄 Mises à jour

### Version 1.0.0
- Interface complète client et admin
- Gestion des commandes et services
- Système de paiement Mobile Money
- Design responsive moderne
- Sécurité de base implémentée

---

**Développé avec ❤️ pour les entrepreneurs SMM**