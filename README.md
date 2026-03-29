# My Cinema - Gestion de Cinéma

Ce projet est une application de gestion de cinéma (Films, Salles et Séances) développée en PHP (architecture MVC simplifiée) et JavaScript Vanilla.

## Installation

1. **Cloner le projet** :
   ```bash
   git clone [URL_DE_TON_REPO]

2. **Structure des dossiers** : Assurez-vous que les dossiers **backend/public/uploads* possèdent les droits d'écriture pour l'upload des affiches de films.

## Configuration de la base de données

Pour des raisons de sécurité, le fichier contenant les identifiants réels de la base de données n'est pas inclus dans le dépôt.

1. **Importer la base** : Utilisez le fichier database.sql (ou le nom de ton export) dans votre interface **phpMyAdmin** pour créer les tables.

2. Configurer la connexion :

- Rendez-vous dans le dossier backend/config/.

- Copiez le fichier database.php.example et renommez-le en database.php.

- Ouvrez database.php et modifiez les variables $user et $pass selon votre configuration locale (ex: root / root pour MAMP).

## Lancement du projet

1. Lancez votre serveur local (WAMP, MAMP ou XAMPP).

2. Placez le projet dans votre dossier www ou htdocs.

3. Ouvrez votre navigateur à l'adresse suivante : http://localhost/my_cinema/frontend

## Fonctionnalités

1. Gestion CRUD des films (avec upload d'affiches).

2. Gestion des salles.

3. Planning des séances avec détection automatique de conflits d'horaires.

4. Suppression logique (Soft Delete) pour les séances.