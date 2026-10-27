# LISEZ-MOI - Projet SEAAL

## 📌 Installation Rapide

Ce projet est configuré pour fonctionner **immédiatement** avec XAMPP par défaut (sans mot de passe MySQL).

### Installation en 3 étapes :

1. **Installez XAMPP** (si pas déjà fait)
   - Téléchargez : https://www.apachefriends.org/
   - Installez avec les paramètres par défaut

2. **Démarrez les services**
   - Ouvrez le panneau XAMPP
   - Démarrez Apache et MySQL

3. **Créez la base de données**
   - Allez à : `http://localhost/phpmyadmin`
   - Créez une base nommée : `usersdb`

4. **Copiez ce dossier**
   - Placez le dossier `SEAAL` dans `C:\xampp\htdocs\`

5. **Initialisez les tables**
   - Visitez : `http://localhost/SEAAL/SEAAL/install_tables.php`
   - Visitez : `http://localhost/SEAAL/SEAAL/fix_picture_column.php`

6. **Testez l'application**
   - Connexion : `http://localhost/SEAAL/SEAAL/login.php`

---

## 📖 Documentation complète

Pour des instructions détaillées, consultez :
- **INSTALLATION_UNIVERSELLE.md** - Guide complet d'installation

---

## ⚙️ Configuration actuelle

- **Serveur MySQL :** localhost
- **Utilisateur :** root
- **Mot de passe :** *(vide - XAMPP par défaut)*
- **Base de données :** usersdb

---

## 🔧 Fichier de configuration

Le fichier `config.php` contient les paramètres de connexion à la base de données.

Si vous avez un mot de passe MySQL différent, modifiez la ligne 4 :
```php
$password = ""; // Remplacez par votre mot de passe si nécessaire
```

---

## 📁 Structure du projet

```
SEAAL/
├── config.php                    # Configuration de la base de données
├── login.php                     # Page de connexion
├── signup.php                    # Page d'inscription
├── acceuil.php                   # Tableau de bord admin
├── acceuiluser.php              # Tableau de bord utilisateur
├── install_tables.php           # Script d'initialisation des tables
├── fix_picture_column.php       # Script de correction de la colonne picture
├── backups/                     # Dossier des sauvegardes
├── imagess/                     # Dossier des images
└── [autres fichiers PHP]
```

---

## ✅ Checklist de vérification

Avant de commencer :
- [ ] XAMPP installé
- [ ] Apache démarré (vert dans XAMPP)
- [ ] MySQL démarré (vert dans XAMPP)
- [ ] Base de données `usersdb` créée
- [ ] Fichiers dans `C:\xampp\htdocs\SEAAL\`
- [ ] Tables initialisées
- [ ] Colonne picture corrigée

---

## 🎯 Accès rapide

| Page | URL |
|------|-----|
| Connexion | http://localhost/SEAAL/SEAAL/login.php |
| Inscription | http://localhost/SEAAL/SEAAL/signup.php |
| phpMyAdmin | http://localhost/phpmyadmin |

---

## 🆘 Problèmes courants

**"Connection failed"**
→ Vérifiez que MySQL est démarré dans XAMPP

**"Unknown database 'usersdb'"**
→ Créez la base de données dans phpMyAdmin

**"Access denied"**
→ Vérifiez que le mot de passe MySQL est vide (par défaut XAMPP)

---

## 📞 Support

Pour plus d'aide, consultez :
- Logs Apache : `C:\xampp\apache\logs\error.log`
- Logs MySQL : `C:\xampp\mysql\data\mysql_error.log`

---

**Version :** Universelle (XAMPP par défaut)  
**Dernière mise à jour :** Décembre 2025
