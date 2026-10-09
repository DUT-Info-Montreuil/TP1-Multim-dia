# CLAUDE.md

## Projet
**A LA COOL** : application web de gestion de buvettes et de files d'attente (SAE S3.01, BUT2 Informatique, IUT Paris 8).
Clients : parcourir les buvettes, commander via panier, suivre leurs commandes, gérer leur solde.
Staff : serveur (dashboard commandes, vente au comptoir), gestionnaire de buvette, super admin.
Interface et code en français.

## Stack
- PHP natif (8.0+), **sans framework ni Composer**, architecture MVC maison
- MySQL via PDO (requêtes préparées, `PDO::ERRMODE_EXCEPTION`)
- Bootstrap 5.3, Bootstrap Icons, Font Awesome (CDN), AJAX pour recherches/mises à jour
- Aucun test automatisé, aucun linter, aucun fichier SQL de schéma dans le dépôt

## Dossiers importants
- `index.php` : routeur front-controller (`?module=xxx&action=yyy`), `switch` sur le module
- `template.php` : gabarit HTML global ; le contenu du module est capturé via `ob_start()` dans `$tampon`
- `connexion.php` : classe `Connexion` (singleton PDO, `Connexion::getBdd()`)
- `csrf.php` : classe `csrf` (token de session, expiration 20 min, `validate()`)
- `app/modules/mod<Nom>/` : un dossier par fonctionnalité (Accueil, Buvettes, Compte, Connexion, CreationBuvette, DetailsCommande, Galerie, Gestionnaire, Histoire, Menu, Panier, Serveur, Solde, SuperAdmin)
- `app/composants/` : fragments réutilisables (`nav.php`, `info_authentification.php`)
- `public/css/style.css`, `public/img/{buvettes,galerie,histoire,produits}/`

## Installation et lancement
```bash
# Pas de dépendances à installer. Prérequis : PHP 8+ avec pdo_mysql, accès à une base MySQL
# 1. Copier connexion.example.php en connexion.php et adapter les identifiants BDD
# 2. Lancer (serveur intégré PHP) :
php -S localhost:8000          # puis http://localhost:8000/
# ou placer le dossier dans WAMP/XAMPP/Laragon
```
Vérification syntaxique rapide : `find . -name '*.php' -exec php -l {} \;`

## Tests
Aucun test automatisé : validation manuelle dans le navigateur (parcours client, serveur, admin).
Vérification effectuée (PHP 8.3.6, 59 fichiers `.php`) :
- Dépendances : aucune à installer (ni `composer.json`, ni `package.json`) → pas de `composer install` / `npm install`
- Tests : aucun (`phpunit.xml`, `tests/` absents) → rien à lancer
- `for f in $(find . -name '*.php'); do php -l "$f"; done` → 0 erreur de syntaxe
- Limite : sans base MySQL accessible, les pages ne peuvent pas être testées de bout en bout
- Config : `connexion.example.php` est le modèle de `connexion.php` (même structure, valeurs fictives) ; `connexion.php` contient encore des identifiants en dur

## Conventions observées
- Chaque module contient 4 fichiers : `mod_x.php` (point d'entrée), `cont_x.php` (contrôleur, `exec()` qui route selon `$_GET['action']`, défaut `afficher`), `modele_x.php` (accès BDD), `vue_x.php` (affichage HTML)
- Classes : `ModX`, `ContX`, `ModeleX`, `VueX` ; fichiers en snake_case ; dossiers `modX` en CamelCase
- `mod_x.php` instancie le contrôleur dans son constructeur ; les fichiers sont chargés par `require_once`
- Nouveau module : créer le dossier + ajouter un `case` dans `index.php` (et dans `$modulesPublics` si public)
- Modules publics : accueil, connexion, histoire, galerie ; tout le reste exige `$_SESSION['user']`
- État utilisateur/buvette conservé en session (`$_SESSION['id_buvette']`, `nom_buvette`…)
- Noms de tables/colonnes et commentaires en français (`produit`, `contient`, `concerner`, `id_buvette`…)
- Toujours utiliser requêtes préparées (`bindValue`) et le token CSRF sur les formulaires POST

## Points d'attention
- `connexion.php` contient des identifiants BDD en clair : ne pas en ajouter d'autres, préférer variables d'environnement
- Ne pas committer d'identifiants réels

## Règles d'intervention
- Un seul périmètre à la fois : un module `app/modules/modX/` OU une couche (présentation : `vue_*`, `template.php`, `public/` ; logique : `cont_*`, `modele_*`, `connexion.php`, `csrf.php`)
- Ne jamais committer en secret : une branche et une PR dédiée par modification
- Lancer les tests automatisés avant de proposer une PR (aucun à ce jour : le signaler, exécuter au minimum `php -l`)
- Accompagner tout correctif de sécurité d'un test
- Expliquer tout changement de dépendance
- Toujours répondre en français

## Restrictions de lecture
- Ne jamais lire `vendor/`, `node_modules/` ni les dépendances installées
- Ne jamais lire `composer.lock`, `package-lock.json` ni autres fichiers de verrouillage
- Ne jamais lire les données, logs (`*.log`) ou fichiers générés
- Ne pas lire les images de `public/img/`

## Contrat vue / logique serveur
- Le contrat (routes, paramètres, JSON AJAX, droits) doit être décrit dans `docs/API.md`
- Hors du périmètre courant, s'appuyer sur ce document plutôt que lire le code des autres modules
- Toute modification d'une route, d'un paramètre ou d'une réponse JSON met à jour `docs/API.md` dans la même PR
- Si `docs/API.md` est absent ou incomplet : le créer/compléter dans une PR dédiée avant de continuer
