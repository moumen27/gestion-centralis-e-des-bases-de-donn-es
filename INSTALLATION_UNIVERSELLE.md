# Configuration Universelle - Projet SEAAL

## 🎯 Objectif

Cette version du projet est configurée pour fonctionner **sur tous les PCs** avec une installation XAMPP par défaut, **sans avoir besoin de configurer un mot de passe MySQL**.

---

## ✅ Changements effectués

### Fichier modifié : `config.php`

**Avant :**
```php
$password = "776A3kXss"; // Mot de passe personnalisé
```

**Après :**
```php
$password = ""; // Mot de passe vide (XAMPP par défaut)
```

---

## 📦 Installation simplifiée

### Prérequis
- XAMPP installé (version 7.4 ou supérieure)

### Étapes d'installation

#### 1. Installer XAMPP
- Téléchargez depuis : https://www.apachefriends.org/
- Installez avec les paramètres par défaut
- **Ne modifiez PAS le mot de passe MySQL** (laissez-le vide)

#### 2. Démarrer les services
- Ouvrez le panneau de contrôle XAMPP
- Démarrez **Apache** et **MySQL**

#### 3. Créer la base de données

**Option A : Via phpMyAdmin (Interface graphique)**
1. Allez à : `http://localhost/phpmyadmin`
2. Cliquez sur "Nouvelle base de données"
3. Nom : `usersdb`
4. Interclassement : `utf8mb4_general_ci`
5. Cliquez sur "Créer"

**Option B : Via SQL (Rapide)**
1. Allez à : `http://localhost/phpmyadmin`
2. Cliquez sur l'onglet "SQL"
3. Collez cette commande :
   ```sql
   CREATE DATABASE IF NOT EXISTS usersdb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```
4. Cliquez sur "Exécuter"

#### 4. Copier les fichiers du projet
- Copiez le dossier `SEAAL` dans `C:\xampp\htdocs\`
- Structure finale : `C:\xampp\htdocs\SEAAL\`

#### 5. Initialiser les tables
- Dans votre navigateur : `http://localhost/SEAAL/SEAAL/install_tables.php`
- Ce script créera automatiquement les tables nécessaires

#### 6. Corriger la colonne picture (Important !)
- Dans votre navigateur : `http://localhost/SEAAL/SEAAL/fix_picture_column.php`
- Ce script modifie la colonne `picture` pour accepter les images

#### 7. Tester l'application
- Page de connexion : `http://localhost/SEAAL/SEAAL/login.php`
- Page d'inscription : `http://localhost/SEAAL/SEAAL/signup.php`

---

## 🚀 Accès rapide

| Page | URL |
|------|-----|
| Connexion | `http://localhost/SEAAL/SEAAL/login.php` |
| Inscription | `http://localhost/SEAAL/SEAAL/signup.php` |
| Accueil Admin | `http://localhost/SEAAL/SEAAL/acceuil.php` |
| Accueil User | `http://localhost/SEAAL/SEAAL/acceuiluser.php` |
| phpMyAdmin | `http://localhost/phpmyadmin` |

---

## 🔧 Dépannage

### Problème : "Connection failed"

**Cause possible :** MySQL n'est pas démarré

**Solution :**
1. Ouvrez le panneau XAMPP
2. Vérifiez que MySQL est en état "Running" (vert)
3. Si non, cliquez sur "Start" pour MySQL

---

### Problème : "Access denied for user 'root'@'localhost'"

**Cause possible :** Vous avez un mot de passe MySQL configuré

**Solution :**

**Option 1 : Réinitialiser le mot de passe MySQL (Recommandé)**
1. Arrêtez MySQL dans le panneau XAMPP
2. Éditez : `C:\xampp\mysql\bin\my.ini`
3. Ajoutez sous `[mysqld]` :
   ```
   skip-grant-tables
   ```
4. Redémarrez MySQL
5. Allez à : `http://localhost/phpmyadmin`
6. Cliquez sur "Comptes utilisateurs"
7. Modifiez l'utilisateur `root` avec l'hôte `localhost`
8. Définissez le mot de passe comme **vide**
9. Retirez `skip-grant-tables` du fichier `my.ini`
10. Redémarrez MySQL

**Option 2 : Modifier config.php avec votre mot de passe**
1. Ouvrez : `C:\xampp\htdocs\SEAAL\SEAAL\config.php`
2. Modifiez la ligne :
   ```php
   $password = ""; // Remplacez par votre mot de passe
   ```
3. Sauvegardez le fichier

---

### Problème : "Unknown database 'usersdb'"

**Solution :**
1. Allez à : `http://localhost/phpmyadmin`
2. Créez la base de données `usersdb` (voir Étape 3)

---

## 📋 Checklist d'installation

- [ ] XAMPP installé
- [ ] Apache démarré (vert)
- [ ] MySQL démarré (vert)
- [ ] Mot de passe MySQL vide (par défaut)
- [ ] Base de données `usersdb` créée
- [ ] Fichiers copiés dans `C:\xampp\htdocs\SEAAL\`
- [ ] Tables initialisées via `install_tables.php`
- [ ] Colonne picture corrigée via `fix_picture_column.php`
- [ ] Page de connexion accessible
- [ ] Test de création de compte réussi

---

## 💡 Avantages de cette configuration

✅ **Plug & Play** : Fonctionne immédiatement après installation de XAMPP  
✅ **Universel** : Compatible avec tous les PCs ayant XAMPP par défaut  
✅ **Simple** : Pas de configuration MySQL complexe  
✅ **Rapide** : Installation en moins de 5 minutes  

---

## ⚠️ Note de sécurité

> [!WARNING]
> Cette configuration est **uniquement pour le développement local**. 
> 
> Pour un environnement de production :
> - Définissez un mot de passe MySQL fort
> - Modifiez `config.php` en conséquence
> - Activez les mesures de sécurité appropriées

---

## 📞 Support

En cas de problème :
1. Vérifiez que Apache et MySQL sont démarrés
2. Vérifiez les logs : `C:\xampp\apache\logs\error.log`
3. Vérifiez que la base `usersdb` existe dans phpMyAdmin
4. Assurez-vous que `config.php` a le mot de passe vide

---

**Version :** Universelle (sans mot de passe)  
**Date :** Décembre 2025  
**Compatibilité :** XAMPP 7.4+ avec configuration par défaut
