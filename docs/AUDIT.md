# Audit de sécurité — A LA COOL

**Dépôt** : DUT-Info-Montreuil/TP1-Multim-dia
**Branche / commit audité** : `main` @ `e4118e3`
**Nature** : exercice universitaire (SAE S3.01, BUT2 Informatique, IUT Paris 8)

## 1. Méthodologie

### 1.1 Périmètre
L'audit suit le périmètre et les contraintes de `CLAUDE.md` : application PHP natif sans
framework, architecture MVC maison (`mod_x`/`cont_x`/`modele_x`/`vue_x`), MySQL via PDO,
aucun test automatisé préexistant, aucun fichier de schéma SQL dans le dépôt. Les fichiers
suivants n'ont pas été lus, conformément aux restrictions de lecture de `CLAUDE.md` :
`vendor/`, `node_modules/`, fichiers de verrouillage, logs, fichiers générés,
`public/img/*` (seuls les noms de fichiers y ont été listés, jamais leur contenu).

### 1.2 Démarche
L'audit a procédé en quatre temps :
1. **Cartographie** de la surface d'attaque (routes, entrées utilisateur, authentification,
   accès base de données, fichiers, sorties HTML/JS, secrets, dépendances, CORS/en-têtes).
2. **Audits ciblés** par catégorie : injections (SQL, commandes, templates), XSS,
   authentification et sessions, contrôle d'accès (IDOR), configuration/secrets/CORS,
   gestion des fichiers.
3. **Vérification critique** : relecture du code exact du dépôt pour chaque hypothèse
   retenue, recherche active des protections existantes avant de qualifier un constat,
   exécution de tests locaux chaque fois que possible.
4. **Synthèse** (ce document), qui ne retient que les constats relus sur le commit audité.

### 1.3 Outils employés
- Lecture directe du code source (`Read`, `Grep`) sur une copie locale synchronisée avec
  `origin/main`.
- Un script PHP (tokenizer natif `token_get_all`) recensant tous les appels
  `PDO::prepare/query/exec` du dépôt et signalant ceux contenant une variable ou une
  concaténation, pour un contrôle exhaustif plutôt qu'un sondage.
- Un script PHP de rendu qui instancie les classes `Vue*` réelles du dépôt avec des
  données de test contenant un marqueur HTML inerte, pour vérifier si la sortie échappe
  ou non ce marqueur.
- Navigateur Chromium (Playwright) piloté localement, réseau externe bloqué, pour vérifier
  le comportement réel du DOM face à des données simulées, sans exécuter de charge utile.
- Serveur intégré de PHP (`php -S`) pour des tests isolés d'en-têtes HTTP et de
  comportement de session, sur des scripts minimalistes reproduisant un motif de code
  précis — **jamais sur l'application complète**, faute de base MySQL disponible dans
  l'environnement d'audit.
- Une requête en lecture seule à l'API GitHub pour confirmer la visibilité publique du
  dépôt.
- Une recherche web pour vérifier l'existence de CVE connues sur les bibliothèques
  front-end chargées par CDN.

### 1.4 Limites de l'analyse
- **Aucune base de données MySQL n'a été disponible** pendant l'audit. Tous les constats
  d'autorisation et d'intégrité métier sont donc établis par **lecture directe et
  déterministe du code** (absence de contrôle dans le chemin d'exécution), et non par
  exploitation démontrée sur une instance vivante de l'application. Ceci est précisé
  fiche par fiche.
- Le schéma de base n'étant pas dans le dépôt, certaines hypothèses dépendant du contenu
  réel des tables restent qualifiées « non confirmées ».
- La configuration du serveur de production (PHP, Apache/Nginx, `.htaccess`) est inconnue
  et conditionne l'impact réel de plusieurs constats (exécution de scripts déposés,
  en-têtes, cookies). Ceci est signalé explicitement.
- Aucun identifiant réel de la base de production n'a été utilisé ni testé.

### 1.5 Légende des statuts
- **Confirmé** : comportement établi par lecture directe et déterministe du code (absence
  avérée d'un contrôle sur un chemin d'exécution réel) et/ou par un test exécuté dont la
  sortie est reproduite ci-dessous.
- **Non confirmé** : hypothèse plausible, mais dépendant de données, d'une configuration
  serveur ou d'un test non exécuté faute de moyens (base de données absente).
- **Faux positif** : une protection ou la logique du code invalide l'hypothèse initiale.

---

## 2. Constats

### Authentification et gestion des sessions

#### AUDIT-001 — Absence de renouvellement de l'identifiant de session à la connexion
**Statut** : Confirmé (défaut de code) · **Criticité** : Moyenne

- **Localisation** : `app/modules/modConnexion/cont_connexion.php:22-39`. Recherche
  exhaustive de `session_regenerate_id` sur tout le dépôt : 0 occurrence.
- **Extrait** :
  ```php
  case 'verifie_connexion':
      $user = $this->modele->verifierConnexion($email, $password);
      if ($user) {
          $_SESSION['user'] = [ 'id_utilisateur' => $user['id_utilisateur'], ...,
                                 'role' => $user['nom_role'] ?? 'Client' ];
          header('Location: index.php?module=accueil'); exit();
  ```
- **Description** : l'identifiant de session n'est jamais régénéré après une connexion
  réussie. Combiné à une configuration serveur en mode non strict (`session.use_strict_mode=0`,
  valeur par défaut de PHP), cela permettrait à un attaquant capable d'imposer un cookie à
  une victime d'obtenir une session authentifiée après qu'elle se connecte (fixation de
  session).
- **Scénario d'illustration** : un attaquant partage un lien contenant un identifiant de
  session choisi par lui ; si la victime se connecte avec ce lien ouvert, l'attaquant, en
  réutilisant le même identifiant, se retrouve authentifié comme elle.
- **Conséquences** : prise de contrôle d'une session authentifiée (confidentialité,
  intégrité des actions effectuées sous cette identité).
- **Protections existantes et limites** : aucune dans le code applicatif. La protection
  effective dépend entièrement de `session.use_strict_mode` côté serveur, hors du dépôt.
- **Preuve observée** :
  - *Test* : script isolé (hors dépôt) reproduisant le seul motif `session_start()` suivi
    de l'écriture de `$_SESSION['user']`, servi par `php -S` (PHP 8.3.6, configuration par
    défaut de l'environnement d'audit).
  - *Commande* : requête avec `Cookie: PHPSESSID=<valeur choisie>` contre ce script.
  - *Résultat réel obtenu* : le serveur adopte l'identifiant fourni par le client et ne
    renvoie aucun nouveau cookie.
  - **Limite explicite** : ce test a porté sur un script **reproduisant le motif**, pas sur
    l'application réelle (pas de base disponible), et sur la configuration PHP de
    l'environnement d'audit, pas sur le serveur de production. Il démontre le mécanisme
    général, pas l'exploitation de ce dépôt précis.
- **Auteur de la découverte** : IA (analyse statique + test isolé).

#### AUDIT-002 — Cookie de session non configuré (HttpOnly / Secure / SameSite)
**Statut** : Non confirmé (dépend du serveur de déploiement) · **Criticité** : Moyenne

- **Localisation** : `index.php:3` (`session_start()` sans option). Aucun
  `session_set_cookie_params`, `setcookie` ni fichier `.htaccess`/`php.ini` dans le dépôt.
- **Description** : les attributs du cookie de session ne sont fixés nulle part dans le
  code. Un test local (serveur intégré, configuration par défaut) a montré un cookie émis
  avec `path=/` et sans `HttpOnly`, `Secure` ni `SameSite` — mais cette configuration par
  défaut ne reflète pas nécessairement le serveur réel de déploiement.
- **Conséquences potentielles** : lisibilité du cookie par du JavaScript, transmission en
  clair, absence de défense `SameSite` contre les requêtes inter-sites, si le serveur ne
  les définit pas lui-même.
- **Protections existantes et limites** : aucune dans le code. Dépend entièrement de la
  configuration PHP/serveur du déploiement réel, non auditée ici.
- **Preuve observée** : test exécuté sur serveur intégré local avec configuration par
  défaut (`/etc/php/8.3/cli/php.ini`) — non représentatif du serveur de production.
- **Auteur de la découverte** : IA.

#### AUDIT-003 — Absence de limitation de débit sur la connexion et l'inscription
**Statut** : Confirmé (absence du mécanisme) · **Criticité** : Moyenne à haute

- **Localisation** : `app/modules/modConnexion/cont_connexion.php:22-66`. Recherche
  exhaustive de verrouillage, délai, captcha ou limitation de débit sur tout le dépôt :
  0 occurrence pertinente.
- **Description** : aucun compteur d'essais, aucun verrouillage de compte, aucun délai
  croissant. Seule la politique de mot de passe à l'inscription (11 caractères avec classes
  de caractères, `cont_connexion.php:84-101`) limite la facilité de certains essais.
- **Scénario d'illustration** : un script peut soumettre un nombre illimité de couples
  email/mot de passe sans ralentissement ni blocage.
- **Conséquences** : essais de mots de passe en masse (confidentialité des comptes, qui
  portent un solde monétaire), création de comptes en masse (disponibilité, intégrité des
  données).
- **Protections existantes et limites** : aucune.
- **Preuve observée** : absence confirmée par recherche exhaustive du code (fait négatif
  vérifiable par lecture complète, pas par test dynamique).
- **Auteur de la découverte** : IA.

#### AUDIT-004 — Changement de mot de passe : politique non réappliquée, sessions non invalidées
**Statut** : Confirmé · **Criticité** : Moyenne

- **Localisation** : `app/modules/modCompte/cont_compte.php:120-147`.
- **Extrait** :
  ```php
  if ($newMdp !== $confirmMdp) { ... return; }
  $hashActuel = $this->modele->getHashMdp($idUser);
  if (password_verify($oldMdp, $hashActuel)) {
      $newHash = password_hash($newMdp, PASSWORD_DEFAULT);
      $this->modele->updateMdp($idUser, $newHash);
  ```
- **Description** : la fonction `motDePasseValideRgpd()` (politique de mot de passe,
  définie dans `cont_connexion.php`) n'est **pas** appelée ici : un nouveau mot de passe
  faible est accepté tant qu'il est confirmé deux fois. Aucune invalidation des autres
  sessions actives du compte n'existe dans le dépôt (aucun mécanisme de révocation).
- **Conséquences** : affaiblissement du mot de passe après un changement volontaire ;
  une session déjà volée reste valide après un changement de mot de passe destiné à la
  neutraliser.
- **Protections existantes** : l'ancien mot de passe est bien demandé et vérifié
  (`password_verify`), ce qui est correct.
- **Preuve observée** : confirmé par lecture directe (absence d'appel à la fonction de
  validation, absence de toute fonction d'invalidation de session dans le dépôt).
- **Auteur de la découverte** : IA.

#### AUDIT-005 — Détermination non déterministe du rôle à la connexion
**Statut** : Non confirmé (dépend des données réelles) · **Criticité** : Moyenne

- **Localisation** : `app/modules/modConnexion/modele_connexion.php:12-20`.
- **Extrait** :
  ```sql
  SELECT u.*, r.nom_role FROM utilisateur u
  LEFT JOIN affecter a ON u.id_utilisateur = a.id_utilisateur
      AND (a.date_fin IS NULL OR a.date_fin >= CURDATE())
  LEFT JOIN role_utilisateur r ON a.id_role = r.id_role
  WHERE u.email = ? LIMIT 1
  ```
- **Description** : sans `ORDER BY`, si un utilisateur possède plusieurs lignes actives
  dans `affecter` (par exemple une adhésion « Client » à une buvette et un rôle de
  gestion), la ligne retournée par `LIMIT 1` n'est pas garantie. Le rôle stocké en session
  (`cont_connexion.php:34`) en dépend directement.
- **Conséquences possibles** : affichage et contrôle d'accès (module super admin) basés
  sur un rôle potentiellement incorrect d'une connexion à l'autre.
- **Pourquoi non confirmé** : dépend du contenu réel de la table `affecter`, non observable
  sans base de données.
- **Auteur de la découverte** : IA.

---

### Contrôle d'accès et IDOR

#### AUDIT-006 — Module `gestionnaire` accessible à tout compte authentifié, sans vérification de rôle ni d'appartenance
**Statut** : Confirmé · **Criticité** : Haute

- **Localisation** : `app/modules/modGestionnaire/cont_gestionnaire.php:19-39`.
- **Extrait** :
  ```php
  public function exec() {
      if (!isset($_SESSION['user'])) { header('Location: index.php?module=connexion'); exit(); }
      $id_user = $_SESSION['user']['id_utilisateur'];
      $id_buvette = $_GET['id_buvette'] ?? null;
      ...
      if (!$id_buvette) {
          $buvettes = $this->modele->getBuvettesAutorisees($id_user);   // seule utilisation du contrôle de rôle
          $this->vue->afficherSelectionBuvette($buvettes);
          return;
      }
      switch($action) { /* exécuté pour tout $id_buvette non vide */ }
  ```
- **Description** : `getBuvettesAutorisees($id_user)` — qui filtre par
  `nom_role = 'Gestionnaire'` — n'est appelée que pour construire la liste de sélection
  quand aucun `id_buvette` n'est fourni. Dès qu'un `id_buvette` est présent dans l'URL, le
  `switch` sur l'action s'exécute **sans aucun rappel** à cette vérification ni à une
  autre. Aucun intergiciel transversal de contrôle d'accès n'existe dans le dépôt ; seul
  `mod_super_admin.php:12` applique correctement un contrôle de rôle.
- **Scénario d'illustration** : un compte « Client » authentifié, sans aucun rôle de
  gestion, appelle `index.php?module=gestionnaire&id_buvette=<ID_D_UNE_AUTRE_BUVETTE>&action=liste`
  et obtient la page de gestion de cette buvette (stocks, trésorerie, fidélité, adhésions,
  profil, ouverture/fermeture) au lieu d'un refus.
- **Conséquences** : exposition et modification de données de gestion et financières de
  n'importe quelle buvette par n'importe quel compte connecté.
- **Protections existantes et limites** : jeton CSRF présent sur les actions d'écriture
  (`$_POST['csrf_token']`), ce qui bloque les requêtes forgées depuis un site tiers mais
  **ne bloque pas** un utilisateur légitimement connecté qui navigue lui-même vers ces
  URL — le jeton lui est remis par la page.
- **Preuve observée** : constat établi de façon déterministe par lecture complète du
  chemin de contrôle (aucune branche de vérification de rôle n'existe entre
  l'authentification et l'exécution des actions). Un test d'intégration a été proposé
  (deux comptes de test, une base jetable) mais **non exécuté**, faute de base de données
  disponible dans l'environnement d'audit.
- **Limites de la vérification** : l'impact réel (exposition effective de données
  métier) suppose l'existence d'au moins deux buvettes distinctes en base — hypothèse
  cohérente avec le modèle de données, non vérifiée sur une instance réelle.
- **Auteur de la découverte** : IA.

#### AUDIT-007 — Module `serveur` : périmètre choisi par l'URL, sans rôle ni appartenance
**Statut** : Confirmé · **Criticité** : Haute

- **Localisation** : `app/modules/modServeur/cont_serveur.php:14-21`.
- **Extrait** :
  ```php
  public function exec() {
      $action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
      if (isset($_GET['id_buvette'])) { $_SESSION['id_buvette'] = (int)$_GET['id_buvette']; }
      $idBuvette = isset($_SESSION['id_buvette']) ? $_SESSION['id_buvette'] : 0;
  ```
- **Description** : aucun test de `$_SESSION['user']` ni de rôle dans ce fichier ; le
  contrôle d'`index.php:11-14` n'exige qu'une session ouverte, quel que soit le rôle.
- **Scénario d'illustration** : un compte client accède au tableau de bord serveur
  (`?module=serveur&id_buvette=<B>`), consulte la recherche de clients avec leur solde, et
  peut faire progresser ou annuler des commandes d'une buvette dont il n'est ni client ni
  personnel.
- **Conséquences** : lecture de données personnelles et financières de clients, altération
  de statuts de commandes hors périmètre.
- **Protections existantes et limites** : aucune.
- **Preuve observée** : constat déterministe par lecture du code, pas de test dynamique
  exécuté (base absente).
- **Auteur de la découverte** : IA.

#### AUDIT-008 — Opérations financières du point de vente pilotées par des valeurs fournies par le client
**Statut** : Confirmé (absence de recalcul serveur) · **Criticité** : Haute

- **Localisation** : `app/modules/modServeur/cont_serveur.php:32-40, 43-63, 84-89`.
- **Extrait** :
  ```php
  case 'valider_vente':
      $data = json_decode(file_get_contents('php://input'), true);
      $res = $this->modele->enregistrerVenteComptoir($data['user_id'], $idBuvette, $data['panier'], $data['total']);
  ...
  case 'cycle_statut':
      $actuel = $_GET['actuel'];                 // état déclaré par le client, jamais relu en base
      if ($actuel == 'Prêt' || $actuel == 'Arrivé') { $next = 'Parti'; }
      if ($next !== $actuel) {
          $this->modele->changerStatut($_GET['id'], $next);
          if ($next === 'Parti') { $this->modele->debiterCommandeSiNonPayee($_GET['id']); }
      }
  ...
  case 'confirmer_commande':
      if (isset($_POST['id_commande'])) { $this->modele->changerStatut($_POST['id_commande'], 'Payé'); }
  ```
- **Description** : le prix et le total d'une vente au comptoir proviennent du corps JSON
  du client et ne sont jamais recalculés depuis la table `produit`. L'état « actuel » de la
  commande utilisé pour décider de la transition est celui **déclaré par le client**, pas
  celui lu en base. `confirmer_commande` marque une commande « Payé » sans aucune
  vérification de paiement.
- **Scénario d'illustration** : un appel à `valider_vente` avec un `total` inférieur à la
  somme réelle des prix du panier enregistre la vente au montant déclaré.
- **Conséquences** : fraude sur les montants encaissés, débit de solde incohérent avec le
  prix catalogue, marquage de commandes comme payées sans paiement.
- **Protections existantes et limites** : le solde du client est vérifié contre `total`
  (`modele_serveur.php`), mais `total` lui-même n'est jamais validé contre le panier. Cette
  faiblesse s'ajoute à l'absence de contrôle de rôle d'AUDIT-007 : elle est donc atteignable
  par tout compte connecté.
- **Preuve observée** : constat établi par lecture directe (absence de recalcul,
  absence de relecture d'état). Aucun test de bout en bout exécuté (base absente).
- **Auteur de la découverte** : IA.

#### AUDIT-009 — Produits modifiables par identifiant sans lien avec la buvette du gestionnaire
**Statut** : Confirmé · **Criticité** : Moyenne à haute

- **Localisation** : `app/modules/modGestionnaire/cont_gestionnaire.php:77-95` ;
  `app/modules/modGestionnaire/modele_gestionnaire.php:20-29, 530-538`.
- **Extrait** :
  ```php
  public function modifierProduitSansStock($id_produit, $nouveau_prix, $nouvelle_description, $nouveau_type) {
      $req = $bdd->prepare("UPDATE produit SET prix_produit = ?, description = ?, type_produit = ? WHERE id_produit = ?");
  ```
- **Description** : la table `produit` est partagée entre buvettes
  (`getTousLesProduits()` lit toute la table). Ni la lecture (`getDetailsProduit`) ni
  l'écriture ne vérifient que le produit appartient à la buvette du gestionnaire courant.
- **Conséquences** : modification de prix et de descriptions de produits appartenant à
  d'autres buvettes.
- **Protections existantes** : aucune jointure avec `contient`/`concerner` avant écriture.
- **Preuve observée** : constat par lecture directe.
- **Auteur de la découverte** : IA.

#### AUDIT-010 — Adhésions gérées en GET, sans vérification de la demande ni de l'appelant
**Statut** : Confirmé · **Criticité** : Moyenne à haute

- **Localisation** : `cont_gestionnaire.php:103-125` ; `modele_gestionnaire.php:125-158`.
- **Extrait** :
  ```php
  case 'accepter_demande':
      $id_target = $_GET['id_utilisateur'] ?? null;
      if ($id_target && $id_buvette) { $this->modele->accepterDemande($id_target, $id_buvette); }
  ```
- **Description** : `accepterDemande` insère directement une ligne `affecter` sans
  vérifier qu'une ligne `adhesion` correspondante existe, ni que l'appelant gère la
  buvette ciblée. Ces actions d'écriture s'exécutent en **GET**, sans jeton CSRF.
- **Conséquences** : attribution ou retrait arbitraire de l'adhésion d'un utilisateur à
  une buvette.
- **Preuve observée** : constat par lecture directe.
- **Auteur de la découverte** : IA.

#### AUDIT-011 — Recharge de solde sans vérification d'adhésion ni de paiement réel
**Statut** : Confirmé · **Criticité** : Moyenne à haute

- **Localisation** : `app/modules/modSolde/cont_solde.php:46-51` ;
  `app/modules/modSolde/modele_solde.php:14-48`.
- **Extrait** :
  ```php
  $idBuvette = (int) $_POST['id_buvette']; $montant = (float) $_POST['montant'];
  if ($montant > 0) { $this->modele->ajouterSolde($idUser, $idBuvette, $montant); ... }
  ```
- **Description** : aucune vérification que la buvette existe ni que l'utilisateur en est
  membre, aucun moyen de paiement réel, aucun jeton CSRF. La trésorerie de la buvette
  ciblée est créditée en parallèle.
- **Conséquences** : création de solde fictif sans contrepartie (intégrité financière).
  Ce point peut correspondre à une fonctionnalité pédagogique volontaire (simulation sans
  paiement réel) — à confirmer avec l'énoncé de l'exercice, ce qui n'est pas du ressort de
  cet audit technique.
- **Preuve observée** : constat par lecture directe.
- **Auteur de la découverte** : IA.

#### AUDIT-012 — Justificatifs d'identité servis sans contrôle d'accès applicatif
**Statut** : Confirmé (absence de contrôleur d'accès) · **Criticité** : Haute

- **Localisation** : `app/modules/modCreationBuvette/cont_creation_buvette.php:58` ;
  `app/modules/modSuperAdmin/vue_super_admin.php:225, 232, 239`. Aucun `.htaccess` dans le
  dépôt.
- **Extrait** :
  ```php
  <a href="public/uploads/justificatifs/<?= htmlspecialchars($demande['fichier_cnid']) ?>" target="_blank">
  ```
  Les documents (statuts, PV d'AG, pièces d'identité) sont servis comme **fichiers
  statiques**, sans contrôleur qui vérifie le rôle administrateur au moment de la
  consultation — seule la page qui liste les liens est protégée.
- **Conséquences** : confidentialité de pièces d'identité si le serveur sert ce dossier
  sans authentification (ce qui est le comportement par défaut d'un serveur de fichiers
  statiques, sauf configuration contraire).
- **Preuve observée** : constat par lecture directe du code et du dépôt (absence de
  contrôleur, absence de `.htaccess`). Le comportement du serveur de production n'a pas
  été testé.
- **Auteur de la découverte** : IA.

---

### Secrets, configuration, mode debug, CORS

#### AUDIT-013 — Identifiants de base de données committés dans un dépôt public
**Statut** : Confirmé · **Criticité** : Haute

- **Localisation** : `connexion.php:12-14` (valeurs volontairement non reproduites dans ce
  rapport).
- **Description** : `connexion.php` est **suivi par git**
  (`git ls-files --error-unmatch connexion.php` → suivi) et **non ignoré**
  (`git check-ignore -q connexion.php` → non ignoré, alors que `.env*` le sont dans
  `.gitignore:31-34`). Le dépôt `DUT-Info-Montreuil/TP1-Multim-dia` est **public**
  (confirmé par une requête en lecture seule à l'API GitHub :
  `"private": false, "visibility": "public"`). Le fichier contient un hôte, un compte et un
  mot de passe non factices, et un second jeu d'identifiants en commentaire. L'historique
  git du fichier comporte 20 commits, avec au moins 3 valeurs distinctes de mot de passe
  actif au fil du temps (comparaison par empreinte cryptographique, sans lecture des
  valeurs).
- **Conséquences** : tout secret present dans ce fichier, actuel ou passé, doit être
  considéré comme compromis, puisque lisible par quiconque consulte le dépôt public ou
  son historique.
- **Protections existantes et limites** : `connexion.example.php` existe avec des valeurs
  fictives, ce qui est une bonne pratique — mais elle n'a pas empêché le fichier réel
  d'être committé.
- **Preuve observée** : `git ls-files`, `git check-ignore`, `git log --format=%h -- connexion.php`
  et comparaison d'empreintes SHA-256 des valeurs successives (commandes exécutées,
  résultats réels, valeurs elles-mêmes jamais affichées) ; requête `search_repositories`
  de l'API GitHub confirmant la visibilité publique.
- **Limites** : la validité actuelle des identifiants contre le serveur réel n'a **pas**
  été testée (ce serait agir sur un système tiers sans autorisation pour ce test).
- **Auteur de la découverte** : IA.

#### AUDIT-014 — Mécanisme de variables d'environnement inopérant
**Statut** : Confirmé · **Criticité** : Moyenne

- **Localisation** : `.gitignore:31-34` (règles `.env*` présentes) ; recherche exhaustive
  de `getenv`/`$_ENV` sur tout le dépôt : 0 occurrence.
- **Description** : la protection du `.gitignore` sur les fichiers `.env` est inopérante
  car l'application ne lit **aucune** variable d'environnement : la configuration est
  toujours lue directement dans `connexion.php`. `README.md:51-52` demande explicitement
  de modifier ce fichier avec les vrais identifiants, ce qui encourage leur commit
  (cause racine d'AUDIT-013).
- **Conséquences** : la bonne pratique affichée (`.gitignore`) ne protège rien en
  pratique.
- **Auteur de la découverte** : IA.

#### AUDIT-015 — Fichiers justificatifs de test suivis par git dans un dépôt public
**Statut** : Confirmé (présence) / Non confirmé (sensibilité du contenu) · **Criticité** : Basse à haute selon contenu

- **Localisation** : `public/uploads/justificatifs/697a36e118091_rapport.pdf`,
  `…180_rapport.pdf`, `…219_rapport.pdf` (suivis par git, dossier non ignoré par
  `.gitignore`).
- **Description** : conformément à `CLAUDE.md` (interdiction de lire les données et
  fichiers générés), le contenu de ces PDF n'a **pas** été ouvert pendant cet audit.
- **Conséquences possibles** : si ces fichiers contiennent des données personnelles
  réelles (pièces d'identité, par exemple), elles sont exposées dans un dépôt public.
- **Pourquoi non confirmé** : le contenu n'a pas été examiné ; seule l'équipe projet peut
  trancher s'il s'agit de données de test ou réelles.
- **Auteur de la découverte** : IA (pour la présence du fichier) ; la qualification du
  contenu reste **à faire par l'équipe projet**, aucune analyse humaine n'a été fournie à
  cette session sur ce point.

#### AUDIT-016 — Messages d'erreur et journaux de débogage exposant des détails techniques
**Statut** : Confirmé · **Criticité** : Basse à moyenne

- **Localisation** : `connexion.php:19-21` (`die("Erreur : " . $e->getMessage() . "<br>")`) ;
  `app/modules/modServeur/modele_serveur.php:145` (message d'exception renvoyé dans la
  réponse JSON) ; `app/modules/modSuperAdmin/modele_super_admin.php:233, 248, 254, 275,
  281, 333, 339, 352` (journaux préfixés `DEBUG:` via `error_log`, non visibles côté
  client mais révélateurs si les logs sont exposés).
- **Description** : en cas d'échec de connexion à la base, le message d'exception PDO
  complet (potentiellement hôte, structure) est affiché directement au visiteur.
- **Conséquences** : divulgation d'informations techniques facilitant une reconnaissance
  ultérieure.
- **Protections existantes et limites** : `PDO::ERRMODE_EXCEPTION` est correctement
  configuré (bonne pratique), mais la gestion de l'exception expose son contenu.
- **Auteur de la découverte** : IA.

#### AUDIT-017 — Absence d'en-têtes de sécurité HTTP
**Statut** : Confirmé (absence) · **Criticité** : Moyenne

- **Localisation** : recherche exhaustive de `Content-Security-Policy`,
  `X-Content-Type-Options`, `X-Frame-Options`, `Strict-Transport-Security`,
  `Referrer-Policy`, `Permissions-Policy` sur tout le dépôt : 0 occurrence. Aucun fichier
  de configuration serveur (`.htaccess`, Nginx, Docker) dans le dépôt.
- **Conséquences** : absence de seconde ligne de défense face aux constats XSS
  (AUDIT-023 à AUDIT-027), pas de protection contre l'affichage en iframe, pas
  d'imposition du HTTPS.
- **Auteur de la découverte** : IA.

#### AUDIT-018 — Partage de ressources entre origines (CORS)
**Statut** : Faux positif (aucun risque constaté) · **Criticité** : Sans objet

- **Localisation** : recherche exhaustive d'`Access-Control-*` sur tout le dépôt :
  0 occurrence.
- **Description** : aucune configuration CORS n'existe dans le code. C'est le
  comportement **le plus sûr par défaut** : la politique du même domaine du navigateur
  s'applique intégralement aux points d'entrée JSON (`rechercher_user`, `valider_vente`).
- **Pourquoi faux positif** : l'absence de configuration CORS n'est pas une
  vulnérabilité ; elle le deviendrait seulement si une configuration future ouvrait
  `Access-Control-Allow-Origin: *` en présence de cookies, ce qui n'est pas le cas ici.
- **Auteur de la découverte** : IA.

#### AUDIT-019 — Dépendances front-end chargées par CDN, sans vérification d'intégrité
**Statut** : Non confirmé (aucune CVE identifiée pour ces versions précises) · **Criticité** : Basse

- **Localisation** : `template.php:10, 12, 13, 14, 45` (Bootstrap 5.3.3, Bootstrap Icons
  1.11.3, Font Awesome 6.5.1, Google Fonts), `vue_serveur.php:156` (image d'un CDN
  d'icônes).
- **Description** : le projet ne possède ni `composer.json` ni `package.json`, donc aucun
  outil d'audit de dépendances (`composer audit`, `npm audit`) n'est applicable — seules
  des bibliothèques front-end en CDN, à versions figées, sont utilisées. Aucune n'a
  d'attribut `integrity` ni `crossorigin`.
- **Recherche effectuée** : une recherche web a été menée pour vérifier l'existence de CVE
  connues affectant précisément ces versions. Aucune CVE n'a été trouvée visant Bootstrap
  5.3.3, Bootstrap Icons 1.11.3 ou Font Awesome 6.5.1 ; les CVE Bootstrap documentées
  (XSS historiques) concernent les lignes 3.x/4.x, antérieures à la version utilisée ici.
  Deux CVE plus récentes (CVE-2025-1647, CVE-2024-6485) existent sur des composants
  Bootstrap, sans confirmation de la plage de versions affectée trouvée dans cette
  recherche.
  Sources : [Version 5.3.3 — FOSSA](https://observer.fossa.com/package/bootstrap),
  [Bootstrap vulnerabilities — Snyk](https://security.snyk.io/package/npm/bootstrap),
  [cve-dev.imfht.com — Bootstrap](https://cve-dev.imfht.com/vendor/Bootstrap?lang=en),
  [GitLab Advisory CVE-2024-6531](https://advisories.gitlab.com/pkg/nuget/bootstrap.sass/CVE-2024-6531).
- **Conséquences si un CDN était compromis** : exécution de code arbitraire dans
  l'application (chaîne d'approvisionnement) ; absence d'`integrity` aggraverait ce risque.
- **Pourquoi non confirmé plutôt que faux positif** : la recherche n'a pas couvert
  Font Awesome et Bootstrap Icons de façon exhaustive (aucune base de CVE dédiée
  consultée directement) ; une vérification complémentaire sur la base NVD/GitHub
  Advisory Database pour ces deux paquets précis est recommandée avant de clore ce point.
- **Auteur de la découverte** : IA.

---

### Téléversement de fichiers et chemins

#### AUDIT-020 — Envoi d'image de produit sans liste blanche d'extension ni de type
**Statut** : Confirmé · **Criticité** : Moyenne à haute

- **Localisation** : `app/modules/modGestionnaire/cont_gestionnaire.php:64-69`.
- **Extrait** :
  ```php
  $nom_image = "default.jpg";
  if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
      $extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
      $nom_image = uniqid('prod_') . "." . $extension;
      move_uploaded_file($_FILES['image_file']['tmp_name'], 'public/img/produits/' . $nom_image);
  }
  ```
- **Description** : contrairement au flux de profil de buvette (lignes 290-297, qui
  applique une liste blanche), ce flux ne filtre **aucune** extension et ne vérifie jamais
  le contenu réel du fichier (pas de `finfo`/`getimagesize`). Le résultat de
  `move_uploaded_file` est ignoré.
- **Preuve observée** : test exécuté localement (script PHP autonome, hors dépôt)
  reproduisant la logique d'extension des trois flux d'upload du projet sur 16 noms de
  fichiers factices (`photo.png`, `x.php`, `x.phtml`, `x.pdf.php`, `x.php%00.png`, etc.).
  **Résultat réel obtenu** : la colonne « image produit » de ce test accepte **toutes**
  les extensions testées, y compris `.php`, `.phtml`, `.html`, alors que les deux autres
  flux (justificatifs, image de buvette) en refusent la majorité grâce à leur liste
  blanche.
- **Conséquences** : si le serveur de déploiement exécute les scripts PHP déposés sous
  `public/img/produits/` (comportement par défaut d'Apache/PHP sans configuration
  contraire), un fichier `.php` déposé y serait exécutable par le serveur web.
- **Complément de preuve** : un test local distinct, sur un dossier temporaire **hors
  dépôt**, a confirmé que le serveur intégré de PHP exécute effectivement un fichier
  `.php` placé dans un dossier qui ne contient que des images, et sert un `.jpg` avec le
  bon type MIME. **Limite explicite** : ce test valide le **principe général** (un
  fichier `.php` sous une racine servie par PHP s'exécute), pas la configuration réelle du
  serveur de production, qui pourrait désactiver l'exécution de scripts dans ce dossier.
- **Auteur de la découverte** : IA.

#### AUDIT-021 — Chemin de l'image de buvette construit à partir d'une valeur d'URL non validée
**Statut** : Confirmé (défaut de construction de chemin, démontré) · **Criticité** : Moyenne

- **Localisation** : `cont_gestionnaire.php:26, 299-304`.
- **Extrait** :
  ```php
  $id_buvette = $_GET['id_buvette'] ?? null;          // ligne 26, jamais casté ensuite
  ...
  $nom_image = 'buvette_' . $id_buvette . '_' . time() . '.' . $extension;
  $chemin_destination = __DIR__ . '/../../../public/img/buvettes/' . $nom_image;
  if (!file_exists(dirname($chemin_destination))) { mkdir(dirname($chemin_destination), 0755, true); }
  ```
- **Description** : `$id_buvette` entre directement dans le nom de fichier final, sans
  conversion en entier ni validation de format, alors que la liste blanche d'extension
  (lignes 290-297) est, elle, correcte.
- **Preuve observée** : **test réellement exécuté** reproduisant fidèlement ces lignes
  (fonction identique, `move_uploaded_file` remplacé par `copy()` d'un PNG factice, car la
  première exige un contexte HTTP réel) dans un arbre de répertoires temporaire **hors
  dépôt**. Résultats réels obtenus :
  - avec `id_buvette = '7'` : fichier écrit dans `public/img/buvettes/buvette_7_<horodatage>.png`
    (comportement attendu) ;
  - avec `id_buvette = '../../x'` : fichier écrit dans `public/img/buvettes/x_<horodatage>.png`
    (le préfixe `../../` a été absorbé silencieusement par la construction de chemin, sans
    erreur) ;
  - avec `id_buvette = '../../../../x'` : fichier écrit **en dehors** du dossier prévu,
    directement dans `public/x_<horodatage>.png`, confirmant une traversée de répertoire
    effective dans la construction du chemin.
- **Conséquences** : écriture de fichiers hors du dossier prévu si `id_buvette` pouvait
  contenir une valeur non numérique — ce qui suppose un point d'entrée permettant de
  passer une chaîne à ce paramètre plutôt qu'un entier.
- **Limites de la vérification** : ce test démontre le **défaut de construction de
  chemin de façon déterministe et reproductible**, indépendamment de toute base de
  données. Il ne démontre pas qu'un attaquant peut effectivement soumettre une valeur non
  numérique à ce paramètre précis dans le flux HTTP réel (le formulaire HTML ne propose
  qu'un champ cohérent avec un entier, mais `$_GET['id_buvette']` n'est jamais revalidé
  côté serveur à cet endroit précis du code) ; ceci reste donc à vérifier par un test HTTP
  réel sur l'application complète.
- **Auteur de la découverte** : IA.

#### AUDIT-022 — Justificatifs : nom de fichier d'origine conservé
**Statut** : Confirmé · **Criticité** : Basse

- **Localisation** : `cont_creation_buvette.php:89-108` (nom : ligne 100).
- **Extrait** :
  ```php
  $nouveauNom = uniqid() . '_' . basename($file['name']);
  $cheminFinal = $dossier . $nouveauNom;
  if (move_uploaded_file($file['tmp_name'], $cheminFinal)) { return $nouveauNom; }
  ```
- **Description** : `basename()` retire correctement les séparateurs de répertoire, mais
  conserve espaces et caractères spéciaux du nom fourni par le client.
- **Preuve observée** : test exécuté (même script que AUDIT-020) : un nom client
  `../../a.pdf` devient `<uniqid>_a.pdf` (traversée neutralisée), mais `c d#e?.pdf`
  devient `<uniqid>_c d#e?.pdf` (caractères spéciaux conservés), ce qui produira un lien
  cassé dans `vue_super_admin.php:225-239` faute d'encodage d'URL.
- **Conséquences** : dysfonctionnement (lien cassé), pas de risque de sécurité direct
  grâce à `basename()` et à la liste blanche d'extension déjà en place.
- **Auteur de la découverte** : IA.

#### AUDIT-023 — Aucun contrôle du type réel du contenu des fichiers envoyés
**Statut** : Confirmé (absence) · **Criticité** : Basse à moyenne

- **Localisation** : recherche exhaustive de `finfo_*`, `mime_content_type`,
  `getimagesize` sur tout le dépôt : 0 occurrence dans les flux d'upload.
- **Description** : seule l'extension déclarée par le client est contrôlée (quand elle
  l'est, cf. AUDIT-020) ; le contenu réel du fichier n'est jamais vérifié.
- **Auteur de la découverte** : IA.

---

### Injections SQL, de commandes système et de templates

#### AUDIT-024 — Absence d'injection SQL exploitable
**Statut** : Faux positif (sur l'ensemble du dépôt) · **Criticité** : Sans objet

- **Localisation** : les 194 appels `PDO::prepare/query/exec` du dépôt.
- **Description** : un script d'analyse statique (tokenizer PHP natif) a recensé
  **exhaustivement** tous les appels à ces trois méthodes et signalé ceux dont l'argument
  contient une variable ou une concaténation.
- **Preuve observée** :
  - *Test* : script PHP utilisant `token_get_all()` pour parcourir les 59 fichiers `.php`
    du dépôt et classer chaque appel `->prepare()/->query()/->exec()`.
  - *Commande* : `php sqlscan.php /chemin/vers/le/dépôt`
  - *Résultat réel obtenu* : « TOTAL appels prepare/query/exec analysés: 194 ; à examiner
    (variable ou concaténation dans l'argument): 26 ». Les 26 signalés ont été relus un
    par un : tous contiennent soit des fragments SQL **constants** ajoutés conditionnellement
    (`$sql .= " AND ..."`, avec paramètres liés ensuite par `execute()`/`bindValue()`),
    soit des variables **castées en entier** avant interpolation (`$limit = (int)$limit`),
    soit un opérateur restreint à deux valeurs constantes (`+`/`-`). Aucune concaténation
    directe d'une donnée utilisateur dans une requête SQL n'a été trouvée.
- **Pourquoi faux positif** : les requêtes préparées avec paramètres liés sont
  systématiques ; les rares interpolations restantes sont neutralisées par un typage
  explicite ou une liste fermée de valeurs.
- **Auteur de la découverte** : IA.

#### AUDIT-025 — Absence d'exécution de commandes système
**Statut** : Faux positif · **Criticité** : Sans objet

- **Description** : recherche exhaustive de `shell_exec`, `system`, `passthru`,
  `popen`, `proc_open`, `pcntl_exec`, de l'opérateur « backtick », `eval`, `assert`,
  `create_function`, `unserialize`, `extract`, `parse_str` sur tout le dépôt :
  0 occurrence. Les seuls `exec` trouvés sont la méthode applicative
  `$controleur->exec()` (convention MVC du projet) et des `PDO::exec("SET
  FOREIGN_KEY_CHECKS = …")` statiques, sans donnée utilisateur.
- **Auteur de la découverte** : IA.

#### AUDIT-026 — Absence d'injection de template côté serveur (SSTI)
**Statut** : Faux positif · **Criticité** : Sans objet

- **Description** : le projet ne possède aucun moteur de templates (ni `composer.json`
  ni `vendor/`). `template.php:42` n'affiche que du HTML déjà produit par les vues
  (`$tampon`), jamais une chaîne évaluée. Les `require_once` d'`index.php` ont des
  chemins constants ; `$_GET['module']` n'alimente qu'un `switch`.
- **Auteur de la découverte** : IA.

#### AUDIT-027 — Injection d'en-tête HTTP via `header("Location: …$id_buvette")`
**Statut** : Faux positif (sur PHP 8.3.6) · **Criticité** : Sans objet

- **Localisation** : `cont_gestionnaire.php:74, 94, 108, 116, 124, 162, 192, 227, 266,
  285, 295, 308, 319, 333`.
- **Extrait** : `header("Location: index.php?module=gestionnaire&id_buvette=$id_buvette");`
- **Description** : `$id_buvette` est interpolé directement dans un en-tête HTTP sans
  encodage. L'hypothèse initiale était une injection d'en-tête (CRLF) permettant, par
  exemple, d'ajouter un `Set-Cookie` arbitraire.
- **Preuve observée** :
  - *Test* : script isolé (hors dépôt) reproduisant exactement ce motif, servi par
    `php -S` (PHP 8.3.6).
  - *Commande* : requête avec `id_buvette=1%0d%0aSet-Cookie:%20pwn=1`.
  - *Résultat réel obtenu* : la réponse HTTP (`curl -i`) ne contient **aucun** en-tête
    `Set-Cookie: pwn=1` ni `Location` altéré ; une requête normale (`id_buvette=1`) produit
    bien un `Location: index.php?module=gestionnaire&id_buvette=1` propre.
- **Pourquoi faux positif** : PHP (depuis la version 5.1.2) rejette nativement les sauts
  de ligne dans `header()`. `CLAUDE.md` impose PHP 8.0+, versions toutes couvertes par
  cette protection native.
- **Auteur de la découverte** : IA.

---

### Cross-Site Scripting (XSS)

#### AUDIT-028 — `$id_buvette` non échappé dans les attributs HTML de `vue_gestionnaire.php`
**Statut** : Confirmé · **Criticité** : Haute

- **Localisation** : `app/modules/modGestionnaire/vue_gestionnaire.php` (33 occurrences
  de `<?= $id_buvette ?>`, dont les lignes 9, 48, 61, 98, 111, 129, 170-184, 228, 256) ;
  origine : `cont_gestionnaire.php:26` (`$id_buvette = $_GET['id_buvette'] ?? null;`,
  jamais casté ni échappé ensuite).
- **Extrait** :
  ```php
  <form action="index.php?module=gestionnaire&action=valider_nouveau&id_buvette=<?= $id_buvette ?>" ...>
  <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" ...>ANNULER</a>
  ```
- **Scénario d'illustration** : un compte authentifié ouvre un lien contenant une valeur
  piégée dans le paramètre `id_buvette` ; cette valeur est réinjectée telle quelle dans
  les attributs HTML de la page de gestion, sans échappement.
- **Protections existantes et limites** : `htmlspecialchars` est utilisé ailleurs dans la
  même vue (par exemple pour les noms de produits en texte), mais jamais sur cette
  variable précise.
- **Preuve observée** :
  - *Test* : script PHP autonome instanciant directement la classe réelle
    `VueGestionnaire` (`require_once` du fichier du dépôt) et appelant ses méthodes
    publiques avec un marqueur de test inoffensif (`zz"><u id="zz">zz`) à la place de
    `$id_buvette`, puis comptage des occurrences brutes et échappées dans le HTML produit.
  - *Commande* : `php rendu.php /chemin/vers/le/dépôt`
  - *Résultat réel obtenu* : « T1 gestionnaire/grille : $id_buvette — marqueur BRUT: 9 |
    ÉCHAPPÉ: 0 ⇒ NON ÉCHAPPÉ » ; « T1b gestionnaire/nouveau produit : $id_buvette —
    marqueur BRUT: 2 | ÉCHAPPÉ: 0 ⇒ NON ÉCHAPPÉ ». Un témoin sur la même vue, où le nom de
    produit passe par `htmlspecialchars`, a montré un comptage « BRUT: 1 | ÉCHAPPÉ: 1 »,
    confirmant que le scanner distingue correctement du code échappé.
- **Conditions d'exposition** : nécessite qu'un utilisateur **authentifié** (tout rôle,
  cf. AUDIT-006) ouvre un lien piégé ; aucune action supplémentaire requise.
- **Limites** : la vue a été rendue isolément avec des données de test ; le flux HTTP
  complet (requête → routage → vue) n'a pas été rejoué faute de base de données.
- **Auteur de la découverte** : IA.

#### AUDIT-029 — `$_SESSION['id_buvette']` non échappé dans la barre de navigation
**Statut** : Confirmé · **Criticité** : Haute

- **Localisation** : `app/composants/nav.php:20` ; écriture de la valeur :
  `app/modules/modMenu/cont_menu.php:32` (`$_SESSION['id_buvette'] = $_GET['id_buvette'];`,
  sans cast, contrairement à `cont_serveur.php:19` qui, lui, caste en `(int)`).
- **Extrait** :
  ```php
  <a class="nav-link text-white mx-3" href="index.php?module=menu&action=afficher&id_buvette=<?= $_SESSION['id_buvette'] ?>">
  ```
- **Description** : cette ligne est incluse dans **toutes** les pages via
  `template.php:12`, donc la donnée piégée persiste en session et se réaffiche à chaque
  navigation suivante jusqu'à ce qu'une autre buvette soit sélectionnée.
- **Preuve observée** : script PHP reproduisant une copie du fichier réel `nav.php` (avec
  une classe `Connexion` de substitution pour éviter toute tentative de connexion
  réseau), session positionnée avec le marqueur de test. **Résultat réel obtenu** :
  « T4 nav.php:20 $_SESSION['id_buvette'] : marqueur BRUT: 1 | ÉCHAPPÉ: 0 ».
- **Limites** : test sur une copie du fichier (l'inclusion de `connexion.php` a été
  retirée et remplacée par une souche locale, car aucune base n'est disponible) ; le
  fichier réel du dépôt est identique hormis cette seule ligne de substitution.
- **Auteur de la découverte** : IA.

#### AUDIT-030 — Nom et type de produit non échappés dans des attributs `data-*`
**Statut** : Confirmé · **Criticité** : Moyenne à haute

- **Localisation** : `app/modules/modGestionnaire/vue_gestionnaire.php:218-219, 227`.
- **Extrait** :
  ```php
  <h4 ... data-type-title="<?= $type ?>"><?= $type ?></h4>
  <div class="... product-card" data-name="<?= strtolower($p['nom_produit']) ?>" data-type="<?= $p['type_produit'] ?>">
  ```
- **Description** : `nom_produit` et `type_produit` proviennent de `$_POST['nom']` et
  `$_POST['type_produit']` (`cont_gestionnaire.php:71`), stockés sans validation puis
  relus en base. Le même `nom_produit` est échappé ailleurs dans la même vue (ligne 240,
  en texte), mais pas ici (en attribut).
- **Preuve observée** : même méthode que AUDIT-028. **Résultats réels obtenus** :
  « T2 gestionnaire/grille : nom_produit (data-name) — marqueur BRUT: 1 | ÉCHAPPÉ: 1 »
  (sur une donnée transformée par `strtolower` avant affichage, d'où la présence des deux
  formes) ; « T2b gestionnaire/grille : type_produit (data-type, titre) — marqueur BRUT: 3
  | ÉCHAPPÉ: 0 ⇒ NON ÉCHAPPÉ ».
- **Conditions d'exposition** : XSS **stocké** — écriture possible par tout compte
  authentifié (cf. AUDIT-006), lecture par quiconque ouvre la grille de cette buvette.
- **Auteur de la découverte** : IA.

#### AUDIT-031 — Nom de produit inséré dans un attribut `onclick` malgré `addslashes`
**Statut** : Confirmé · **Criticité** : Moyenne à haute

- **Localisation** : `app/modules/modServeur/vue_serveur.php:95`.
- **Extrait** :
  ```php
  onclick="ajouterAuPanier(<?= $p['id_produit'] ?>, '<?= addslashes($p['nom_produit']) ?>', <?= $p['prix_produit'] ?>)"
  ```
- **Description** : `addslashes()` protège un contexte **chaîne JavaScript**, pas un
  **attribut HTML** délimité par des guillemets doubles : un guillemet double dans le nom
  du produit termine l'attribut avant que l'antislash ne soit interprété par le
  navigateur, qui analyse le HTML avant le JavaScript.
- **Preuve observée** :
  - *Test* : rendu réel de `VueServeur::afficherDashboard()` avec un produit dont le nom
    contient `"` suivi d'un attribut de test inoffensif (`data-zz="1"`), chargé dans
    Chromium (Playwright) avec tout accès réseau externe bloqué.
  - **Résultat réel obtenu** : « T3-navigateur (nom_produit avec guillemet,
    onclick/addslashes): {"boutonsAvecAttributInjecte":1,"boutonsTotal":2} » — l'attribut
    de test s'est retrouvé injecté sur le bouton du produit piégé, et absent du bouton
    témoin.
- **Conditions d'exposition** : écriture par tout compte authentifié (produit créé via
  `cont_gestionnaire.php`, visible pour toutes les buvettes car `getTousLesProduits()` lit
  toute la table) ; lecture par le personnel ouvrant le tableau de bord serveur.
- **Limites** : le test a validé le rendu HTML/DOM, sans exécuter de script actif —
  seul un attribut inerte a été utilisé comme marqueur.
- **Auteur de la découverte** : IA.

#### AUDIT-032 — Construction de HTML par concaténation puis `innerHTML` dans la recherche de clients du point de vente
**Statut** : Confirmé · **Criticité** : Haute

- **Localisation** : `app/modules/modServeur/vue_serveur.php:300-301, 326, 329`.
- **Extrait** :
  ```js
  data.forEach(u => { html += `<button ... onclick='selectionnerClient(${JSON.stringify(u)})'>
      <div class="fw-bold">${u.nom} ${u.prenom}</div>...`; });
  resDiv.innerHTML = html;
  ```
- **Description** : `nom` et `prenom` proviennent du formulaire d'inscription public
  (`cont_connexion.php:60`), sans validation de format, puis transitent par
  `modele_serveur.php:96-105` jusqu'à une réponse JSON insérée directement dans le DOM via
  `innerHTML`, sans échappement. `JSON.stringify()` n'échappe ni `<` ni les apostrophes
  pour un contexte HTML.
- **Preuve observée** :
  - *Test* : la page réelle (`VueServeur::afficherDashboard()`) a été rendue puis chargée
    dans Chromium (réseau externe bloqué), `fetch` remplacé par une réponse simulée, et la
    **fonction JavaScript réelle** `rechercherClient()` du fichier a été appelée avec des
    données de test contenant des marqueurs inoffensifs (une balise `<u>` inerte, puis un
    attribut `data-zz` via une apostrophe).
  - **Résultat réel obtenu** :
    ```json
    {
      "A_nom_prenom_en_innerHTML":  { "elementInjecte": true,  "attributInjecte": false },
      "B_apostrophe_dans_onclick":  { "elementInjecte": false, "attributInjecte": true },
      "C_temoin_texte_simple":      { "elementInjecte": false, "attributInjecte": false }
    }
    ```
    Les deux marqueurs inoffensifs ont été interprétés comme du HTML/attribut actif,
    contre un résultat négatif pour la donnée témoin.
- **Conditions d'exposition** : la donnée source (`nom`/`prenom`) est modifiable par
  **n'importe quel inscrit**, sans authentification privilégiée ; elle est consultée par
  le personnel de service (profil le plus exposé aux conséquences, car vue réservée au
  rôle le plus actif opérationnellement).
- **Limites** : seule la fonction JavaScript isolée a été testée avec une réponse
  simulée ; le flux HTTP complet (recherche réelle contre une base) n'a pas été rejoué.
- **Auteur de la découverte** : IA.

---

## 3. Synthèse chiffrée

| Statut | Nombre |
|---|---|
| Confirmé | 23 |
| Non confirmé | 4 (AUDIT-002, AUDIT-005, AUDIT-015, AUDIT-019) |
| Faux positif | 4 (AUDIT-018, AUDIT-024, AUDIT-025, AUDIT-026) + AUDIT-027 |

**Criticité des constats confirmés** : 8 de criticité **haute**
(AUDIT-006, 007, 008, 012, 013, 028, 029, 032), 10 de criticité **moyenne** ou
**moyenne à haute**, le reste en **basse**.

Au regard des exigences de l'exercice (au moins 5 vulnérabilités confirmées, localisées
et démontrables, dont au moins 2 de gravité haute ou critique) : **l'objectif minimal est
atteint et largement dépassé**, avec 23 constats confirmés dont 8 de criticité haute.
Aucun des constats de criticité haute ne repose sur une simple supposition : chacun est
établi soit par lecture déterministe du chemin de contrôle (absence vérifiable d'un
contrôle), soit par un test réellement exécuté et reproduit dans ce document.

## 4. Retour critique

### 4.1 Faux positifs relevés par l'IA et motifs techniques de leur rejet
- **Injection SQL (AUDIT-024)** : écartée après analyse exhaustive (194 appels recensés
  par un scanner basé sur le tokenizer PHP, pas un simple sondage par mots-clés) montrant
  que les requêtes préparées avec paramètres liés sont systématiques, et que les rares
  interpolations restantes sont des fragments SQL constants ou des valeurs castées en
  entier.
- **Exécution de commandes système et SSTI (AUDIT-025, AUDIT-026)** : écartées car
  l'application n'invoque aucun processus externe et ne possède aucun moteur de
  templates — ce sont des absences factuelles, pas des hypothèses neutralisées par une
  protection active.
- **CORS (AUDIT-018)** : écarté car l'absence totale de configuration est précisément le
  comportement sûr recherché (politique du même domaine par défaut du navigateur).
- **Injection d'en-tête HTTP (AUDIT-027)** : hypothèse initiale plausible au vu du code
  (interpolation directe d'une variable utilisateur dans `header()`), mais invalidée par
  un test réel démontrant que PHP 8.3.6 (version conforme au prérequis `8.0+` de
  `CLAUDE.md`) bloque nativement l'injection de sauts de ligne dans les en-têtes HTTP.
