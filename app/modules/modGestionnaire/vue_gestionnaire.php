<?php
class VueGestionnaire {

    // ==================== PRODUITS ====================
    public function afficherFormulaireNouveauProduit($id_buvette, $token, $types) {
        ?>
        <div class="container mt-5 pt-5">
            <div class="card border-0 shadow-lg rounded-5 p-5">
                <h2 class="font-handwritten mb-4">Créer un nouvel article</h2>
                <form action="index.php?module=gestionnaire&action=valider_nouveau&id_buvette=<?= $id_buvette ?>"
                      method="POST" enctype="multipart/form-data">

                    <input type="hidden" name="csrf_token" value="<?= $token ?>">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Nom de l'article</label>
                            <input type="text" name="nom" class="form-control rounded-pill" placeholder="Ex: Cookie Géant" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Type de produit</label>
                            <select name="type_produit" class="form-select rounded-pill" required>
                                <option value="" selected disabled>Choisir un type...</option>
                                <?php foreach($types as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control rounded-4" rows="3" placeholder="Description courte du produit..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prix (€)</label>
                            <input type="number" name="prix" class="form-control rounded-pill"
                                   placeholder="0.00" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Image du produit</label>
                            <input type="file" name="image_file" class="form-control" accept="image/*" required>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-secondary rounded-pill px-4 me-2">ANNULER</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-5">CRÉER L'ARTICLE</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    public function afficherDetailsArticle($produit, $id_buvette, $token, $types) {
        ?>
        <div class="module-gestionnaire container mt-5 pt-5">
            <div class="bg-secondary-subtle rounded-5 p-5 shadow-lg">
                <form action="index.php?module=gestionnaire&action=modifier_article&id=<?= $produit['id_produit'] ?>&id_buvette=<?= $id_buvette ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $token ?>">

                    <div class="row g-5">
                        <div class="col-md-4 text-center">
                            <div class="bg-white rounded-4 p-4 mb-3 d-flex align-items-center justify-content-center" style="min-height: 300px;">
                                <img src="public/img/<?= htmlspecialchars($produit['image_produit']) ?>" class="img-fluid" style="max-height: 250px;">
                            </div>
                            <p class="text-muted small">ID Produit : #<?= $produit['id_produit'] ?></p>
                        </div>

                        <div class="col-md-8">
                            <h1 class="text-uppercase fw-bold mb-4"><?= htmlspecialchars($produit['nom_produit']) ?></h1>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Type d'article</label>
                                    <select name="type_produit" class="form-select border-dark rounded-pill">
                                        <?php foreach($types as $t): ?>
                                            <option value="<?= $t ?>" <?= ($produit['type_produit'] == $t) ? 'selected' : '' ?>><?= $t ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control border-dark rounded-4" rows="3"><?= htmlspecialchars($produit['description'] ?? '') ?></textarea>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Stock actuel</label>
                                    <input type="number" name="quantite" class="form-control border-dark text-center fw-bold" value="<?= $produit['quantite'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Prix (€)</label>
                                    <input type="number" name="prix_produit" placeholder="0.00" min="0" step="0.01" class="form-control border-dark text-center fw-bold" value="<?= floatval($produit['prix_produit']) ?>" required>
                                </div>
                            </div>

                            <div class="d-flex gap-3 mt-5">
                                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold">ENREGISTRER LES MODIFICATIONS</button>
                                <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill px-5">RETOUR</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    public function afficherGrilleGlobale($produits, $id_buvette, $stats) {
        $isAlerteActive = isset($_GET['alerte']);
        ?>
        <div class="module-gestionnaire container mt-5 pt-5">

            <div class="d-flex justify-content-between align-items-end mb-4">
                <h2 class="font-handwritten text-dark mb-0">Tableau de bord</h2>
                <span class="badge bg-secondary rounded-pill px-3">Buvette #<?= $id_buvette ?></span>
            </div>

            <div class="row g-3 mb-5 text-center">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-dark h-100">
                        <h6 class="text-uppercase small mb-1" style="color: rgba(255,255,255,0.7) !important;">Valeur du Stock</h6>
                        <h3 class="fw-bold mb-0" style="color: #ffffff !important;">
                            <?= number_format($stats['valeur_stock'] ?? 0, 2, ',', ' ') ?> €
                        </h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 <?= ($stats['alertes_count'] > 0) ? 'bg-danger' : 'bg-white' ?> h-100">
                        <h6 class="text-uppercase small mb-1" style="color: <?= ($stats['alertes_count'] > 0) ? 'rgba(255,255,255,0.8)' : '#6c757d' ?> !important;">Alertes Stock</h6>
                        <h3 class="fw-bold mb-0" style="color: <?= ($stats['alertes_count'] > 0) ? '#ffffff' : '#212529' ?> !important;"><?= $stats['alertes_count'] ?></h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 <?= ($stats['demandes_count'] > 0) ? 'bg-warning' : 'bg-white' ?> h-100">
                        <h6 class="text-uppercase small mb-1" style="color: <?= ($stats['demandes_count'] > 0) ? 'rgba(0,0,0,0.6)' : '#6c757d' ?> !important;">Demandes</h6>
                        <h3 class="fw-bold mb-0" style="color: <?= ($stats['demandes_count'] > 0) ? '#000' : '#212529' ?> !important;"><?= $stats['demandes_count'] ?></h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <h6 class="text-uppercase small mb-1" style="color: #6c757d !important;">Membres</h6>
                        <h3 class="fw-bold mb-0" style="color: #212529 !important;"><?= $stats['membres_count'] ?></h3>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-4 gap-3 flex-wrap">
                <div class="d-flex gap-2">
                    <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn <?= !$isAlerteActive ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill">Tous les articles</a>
                    <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>&alerte" class="btn <?= $isAlerteActive ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill">Alertes Stock</a>
                    <a href="index.php?module=gestionnaire&action=gerer_adhesions&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-warning rounded-pill">Gérer les Membres</a>
                </div>
                <div class="d-flex gap-2">
                    <a href="index.php?module=gestionnaire&action=fournisseurs&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-primary rounded-pill">
                        <i class="bi bi-truck"></i> Fournisseurs
                    </a>
                    <a href="index.php?module=gestionnaire&action=commandes_fournisseurs&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-info rounded-pill">
                        <i class="bi bi-box-seam"></i> Commandes
                    </a>
                    <a href="index.php?module=gestionnaire&action=tresorerie&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-success rounded-pill">
                        <i class="bi bi-cash-coin"></i> Trésorerie
                    </a>
                    <a href="index.php?module=gestionnaire&action=fidelite&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-warning rounded-pill">
                        <i class="bi bi-star-fill"></i> Fidélité
                    </a>
                </div>
            </div>

            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control border-start-0 border-end-0" placeholder="Rechercher un article...">
                    <select id="typeFilter" class="form-select border-start-0 rounded-end-pill" style="max-width: 200px;">
                        <option value="all">Tous les types</option>
                        <option value="Boisson Chaude">Boisson Chaude</option>
                        <option value="Boisson Froide">Boisson Froide</option>
                        <option value="Nourriture Chaude">Nourriture Chaude</option>
                        <option value="Nourriture Froide">Nourriture Froide</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <?php
                $types_uniques = [];
                foreach ($produits as $p) {
                    if (!in_array($p['type_produit'], $types_uniques)) {
                        $types_uniques[] = $p['type_produit'];
                    }
                }

                foreach ($types_uniques as $type):
                    ?>
                    <div class="col-12">
                        <h4 class="category-title fw-bold text-uppercase border-bottom pb-2" data-type-title="<?= $type ?>">
                            <?= $type ?>
                        </h4>
                    </div>
                    <?php
                    foreach ($produits as $p):
                        if ($p['type_produit'] !== $type) continue;
                        $isAlerte = ($p['quantite'] <= $p['seuil_alerte']);
                        ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 product-card" data-name="<?= strtolower($p['nom_produit']) ?>" data-type="<?= $p['type_produit'] ?>">
                            <a href="index.php?module=gestionnaire&action=details&id=<?= $p['id_produit'] ?>&id_buvette=<?= $id_buvette ?>" class="text-decoration-none">
                                <div class="card h-100 shadow-sm border-0 rounded-4 <?= $isAlerte ? 'border-danger border-3' : '' ?>" style="transition: transform 0.2s; position: relative;">
                                    <?php if ($isAlerte): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-danger rounded-pill">
                                                <i class="bi bi-exclamation-triangle-fill"></i> ALERTE
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body text-center">
                                        <img src="public/img/<?= htmlspecialchars($p['image_produit']) ?>" class="img-fluid mb-3" style="height: 100px; object-fit: contain;">
                                        <h6 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($p['nom_produit']) ?></h6>
                                        <p class="text-success fw-bold mb-2"><?= number_format($p['prix_produit'], 2, ',', ' ') ?> €</p>
                                        <div class="d-flex justify-content-around small">
                                            <span class="badge bg-secondary"><?= $p['quantite'] ?> en stock</span>
                                            <?php if ($isAlerte): ?>
                                                <span class="badge bg-danger">Seuil: <?= $p['seuil_alerte'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="index.php?module=gestionnaire&action=form_nouveau&id_buvette=<?= $id_buvette ?>" class="text-decoration-none">
                        <div class="card h-100 border-2 border-dashed rounded-4 d-flex align-items-center justify-content-center bg-light text-secondary" style="border-style: dashed !important; min-height: 180px;">
                            <div class="text-center">
                                <i class="bi bi-plus-lg display-6"></i>
                                <br><span class="fw-bold small">NOUVEAU</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <script>
            const searchInput = document.getElementById('searchInput');
            const typeFilter = document.getElementById('typeFilter');
            const cards = document.querySelectorAll('.product-card');
            const titles = document.querySelectorAll('.category-title');

            function filterProducts() {
                const textValue = searchInput.value.toLowerCase();
                const typeValue = typeFilter.value;

                cards.forEach(card => {
                    const productName = card.getAttribute('data-name');
                    const productType = card.getAttribute('data-type');
                    const matchesText = productName.includes(textValue);
                    const matchesType = (typeValue === 'all' || productType === typeValue);
                    card.style.display = (matchesText && matchesType) ? '' : 'none';
                });

                titles.forEach(title => {
                    const titleType = title.getAttribute('data-type-title');
                    if (typeValue === 'all') {
                        title.style.display = '';
                    } else if (titleType === typeValue) {
                        title.style.display = '';
                    } else {
                        title.style.display = 'none';
                    }
                });
            }
            searchInput.addEventListener('keyup', filterProducts);
            typeFilter.addEventListener('change', filterProducts);
        </script>
        <?php
    }

    // ==================== SÉLECTION BUVETTE ====================
    public function afficherSelectionBuvette($buvettes) {
        ?>
        <div class="container mt-5 pt-5 text-center">
            <h1 class="font-handwritten display-4 mb-5">Gestionnaire</h1>
            <p class="lead mb-4">Veuillez sélectionner une buvette à gérer :</p>
            <div class="d-flex justify-content-center gap-4 flex-wrap">
                <?php foreach ($buvettes as $b): ?>
                    <a href="index.php?module=gestionnaire&id_buvette=<?= $b['id_buvette'] ?>"
                       class="btn btn-dark btn-lg rounded-pill px-5 shadow">
                        <?= htmlspecialchars($b['nom']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    // ==================== ADHÉSIONS ====================
    public function afficherGestionAdhesions($id_buvette, $membres, $demandes, $token) {
        ?>
        <div class="container mt-5 pt-5">
            <h2 class="font-handwritten mb-4">Gestion de la Communauté</h2>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-warning bg-opacity-10 border-start border-warning border-4">
                <h5 class="fw-bold text-warning mb-3">DEMANDES D'ADHÉSION EN ATTENTE (<?= count($demandes) ?>)</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Utilisateur</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach($demandes as $d): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($d['nom']." ".$d['prenom']) ?></strong> (<?= htmlspecialchars($d['email']) ?>)</td>
                                <td class="text-end">
                                    <a href="index.php?module=gestionnaire&action=accepter_demande&id_utilisateur=<?= $d['id_utilisateur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-success btn-sm rounded-pill px-3">Accepter</a>
                                    <a href="index.php?module=gestionnaire&action=refuser_demande&id_utilisateur=<?= $d['id_utilisateur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3">Refuser</a>
                                </td>
                            </tr>
                        <?php endforeach; if(empty($demandes)) echo "<tr><td colspan='2' class='text-muted text-center'>Aucune demande.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm p-4">
                <h5 class="fw-bold mb-4">MEMBRES OFFICIELS (RÔLE CLIENT)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Depuis le</th>
                            <th>Expire le</th> <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($membres as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['nom']." ".$m['prenom']) ?></td>
                                <td><?= htmlspecialchars($m['email']) ?></td>
                                <td><?= date('d/m/Y', strtotime($m['date_debut'])) ?></td>
                                <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    <?= isset($m['date_fin']) ? date('d/m/Y', strtotime($m['date_fin'])) : 'N/A' ?>
                                </span>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?module=gestionnaire&action=supprimer_membre&id_utilisateur=<?= $m['id_utilisateur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Révoquer ce client ?');">Révoquer</a>
                                </td>
                            </tr>
                        <?php endforeach; if(empty($membres)) echo "<tr><td colspan='5' class='text-muted text-center'>Aucun membre actif.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4"><a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour</a></div>
        </div>
        <?php
    }

    // ==================== FOURNISSEURS ====================
    public function afficherListeFournisseurs($fournisseurs, $id_buvette, $token) {
        ?>
        <div class="container mt-5 pt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="font-handwritten mb-0">Gestion des Fournisseurs</h2>
                <a href="index.php?module=gestionnaire&action=form_nouveau_fournisseur&id_buvette=<?= $id_buvette ?>" class="btn btn-primary rounded-pill">
                    <i class="bi bi-plus-circle"></i> Nouveau Fournisseur
                </a>
            </div>

            <div class="row g-4">
                <?php foreach($fournisseurs as $f): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($f['nom_fournisseur']) ?></h5>
                                    <span class="badge bg-success rounded-pill">Actif</span>
                                </div>
                                <p class="text-muted small mb-2"><i class="bi bi-envelope"></i> <?= htmlspecialchars($f['email']) ?></p>
                                <p class="text-muted small mb-2"><i class="bi bi-telephone"></i> <?= htmlspecialchars($f['telephone']) ?></p>
                                <p class="text-muted small mb-3"><i class="bi bi-clock"></i> Livraison: <?= $f['delai_livraison_jours'] ?> jours</p>

                                <div class="d-flex gap-2">
                                    <a href="index.php?module=gestionnaire&action=details_fournisseur&id_fournisseur=<?= $f['id_fournisseur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-sm btn-outline-dark rounded-pill">Détails</a>
                                    <a href="index.php?module=gestionnaire&action=commander&id_fournisseur=<?= $f['id_fournisseur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-sm btn-primary rounded-pill">Commander</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4">
                <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour au tableau de bord</a>
            </div>
        </div>
        <?php
    }

    public function afficherDetailsFournisseur($fournisseur, $produits, $id_buvette) {
        ?>
        <div class="container mt-5 pt-5">
            <div class="card border-0 shadow-lg rounded-5 p-5 mb-4">
                <h2 class="font-handwritten mb-4"><?= htmlspecialchars($fournisseur['nom_fournisseur']) ?></h2>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <?= htmlspecialchars($fournisseur['email']) ?></p>
                        <p><strong>Téléphone:</strong> <?= htmlspecialchars($fournisseur['telephone']) ?></p>
                        <p><strong>Adresse:</strong> <?= htmlspecialchars($fournisseur['adresse']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>SIRET:</strong> <?= htmlspecialchars($fournisseur['siret']) ?></p>
                        <p><strong>Délai de livraison:</strong> <?= $fournisseur['delai_livraison_jours'] ?> jours</p>
                    </div>
                </div>
            </div>

            <h4 class="fw-bold mb-3">Catalogue Produits</h4>
            <div class="row g-3">
                <?php foreach($produits as $p): ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body text-center">
                                <img src="public/img/<?= htmlspecialchars($p['image_produit']) ?>" class="img-fluid mb-3" style="height: 80px; object-fit: contain;">
                                <h6 class="fw-bold"><?= htmlspecialchars($p['nom_produit']) ?></h6>
                                <p class="text-success fw-bold mb-1">Prix d'achat: <?= number_format($p['prix_achat'], 2) ?> €</p>
                                <p class="text-muted small">Quantité min: <?= $p['quantite_minimum'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 d-flex gap-2">
                <a href="index.php?module=gestionnaire&action=commander&id_fournisseur=<?= $fournisseur['id_fournisseur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-primary rounded-pill">Passer une commande</a>
                <a href="index.php?module=gestionnaire&action=fournisseurs&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour</a>
            </div>
        </div>
        <?php
    }

    public function afficherFormulaireNouveauFournisseur($id_buvette, $token) {
        ?>
        <div class="container mt-5 pt-5">
            <div class="card border-0 shadow-lg rounded-5 p-5">
                <h2 class="font-handwritten mb-4">Ajouter un Fournisseur</h2>
                <form action="index.php?module=gestionnaire&action=creer_fournisseur&id_buvette=<?= $id_buvette ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $token ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nom du fournisseur</label>
                            <input type="text" name="nom_fournisseur" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control rounded-pill" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Téléphone</label>
                            <input type="text" name="telephone" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">SIRET</label>
                            <input type="text" name="siret" class="form-control rounded-pill" maxlength="14">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Adresse complète</label>
                        <textarea name="adresse" class="form-control rounded-4" rows="2" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Délai de livraison (en jours)</label>
                        <input type="number" name="delai_livraison" class="form-control rounded-pill" value="3" min="1" required>
                    </div>

                    <div class="text-end">
                        <a href="index.php?module=gestionnaire&action=fournisseurs&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-secondary rounded-pill px-4 me-2">Annuler</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5">Créer</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    // ==================== COMMANDES FOURNISSEURS ====================
    public function afficherFormulaireCommande($fournisseur, $produits, $id_buvette, $token) {
        ?>
        <div class="container mt-5 pt-5">
            <h2 class="font-handwritten mb-4">Commander chez <?= htmlspecialchars($fournisseur['nom_fournisseur']) ?></h2>

            <form action="index.php?module=gestionnaire&action=valider_commande&id_buvette=<?= $id_buvette ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $token ?>">
                <input type="hidden" name="id_fournisseur" value="<?= $fournisseur['id_fournisseur'] ?>">

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3">Sélectionnez les produits</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Commander</th>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Qté min</th>
                                <th>Quantité</th>
                                <th class="text-end">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($produits as $p): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="produits[<?= $p['id_produit'] ?>][commander]" class="form-check-input product-checkbox" data-id="<?= $p['id_produit'] ?>">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['nom_produit']) ?></strong>
                                        <input type="hidden" name="produits[<?= $p['id_produit'] ?>][prix_achat]" value="<?= $p['prix_achat'] ?>">
                                    </td>
                                    <td><?= number_format($p['prix_achat'], 2) ?> €</td>
                                    <td><span class="badge bg-secondary"><?= $p['quantite_minimum'] ?></span></td>
                                    <td>
                                        <input type="number" name="produits[<?= $p['id_produit'] ?>][quantite]" class="form-control product-quantity" data-id="<?= $p['id_produit'] ?>" data-prix="<?= $p['prix_achat'] ?>" min="<?= $p['quantite_minimum'] ?>" value="<?= $p['quantite_minimum'] ?>" style="width: 100px;">
                                    </td>
                                    <td class="text-end fw-bold product-total" data-id="<?= $p['id_produit'] ?>">0.00 €</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr class="table-dark">
                                <td colspan="5" class="text-end fw-bold">TOTAL COMMANDE:</td>
                                <td class="text-end fw-bold" id="totalCommande">0.00 €</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <label class="form-label fw-bold">Notes / Instructions spéciales</label>
                    <textarea name="notes" class="form-control rounded-4" rows="3" placeholder="Ex: Livraison à partir de 14h..."></textarea>
                </div>

                <div class="text-end">
                    <a href="index.php?module=gestionnaire&action=fournisseurs&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-secondary rounded-pill px-4 me-2">Annuler</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Valider la commande</button>
                </div>
            </form>
        </div>

        <script>
            function calculerTotal() {
                let total = 0;
                document.querySelectorAll('.product-checkbox').forEach(cb => {
                    if (cb.checked) {
                        const id = cb.getAttribute('data-id');
                        const qtyInput = document.querySelector(`.product-quantity[data-id="${id}"]`);
                        const prix = parseFloat(qtyInput.getAttribute('data-prix'));
                        const qty = parseInt(qtyInput.value) || 0;
                        const sousTotal = prix * qty;

                        document.querySelector(`.product-total[data-id="${id}"]`).textContent = sousTotal.toFixed(2) + ' €';
                        total += sousTotal;
                    } else {
                        const id = cb.getAttribute('data-id');
                        document.querySelector(`.product-total[data-id="${id}"]`).textContent = '0.00 €';
                    }
                });
                document.getElementById('totalCommande').textContent = total.toFixed(2) + ' €';
            }

            document.querySelectorAll('.product-checkbox, .product-quantity').forEach(elem => {
                elem.addEventListener('change', calculerTotal);
                elem.addEventListener('input', calculerTotal);
            });

            calculerTotal();
        </script>
        <?php
    }

    public function afficherListeCommandesFournisseurs($commandes, $id_buvette, $statut_filtre) {
        ?>
        <div class="container mt-5 pt-5">
            <h2 class="font-handwritten mb-4">Commandes Fournisseurs</h2>

            <div class="mb-4 d-flex gap-2">
                <a href="index.php?module=gestionnaire&action=commandes_fournisseurs&id_buvette=<?= $id_buvette ?>" class="btn <?= !$statut_filtre ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill">Toutes</a>
                <a href="index.php?module=gestionnaire&action=commandes_fournisseurs&id_buvette=<?= $id_buvette ?>&statut=En attente" class="btn <?= $statut_filtre == 'En attente' ? 'btn-warning' : 'btn-outline-warning' ?> rounded-pill">En attente</a>
                <a href="index.php?module=gestionnaire&action=commandes_fournisseurs&id_buvette=<?= $id_buvette ?>&statut=Validée" class="btn <?= $statut_filtre == 'Validée' ? 'btn-info' : 'btn-outline-info' ?> rounded-pill">Validées</a>
                <a href="index.php?module=gestionnaire&action=commandes_fournisseurs&id_buvette=<?= $id_buvette ?>&statut=Expédiée" class="btn <?= $statut_filtre == 'Expédiée' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill">Expédiées</a>
                <a href="index.php?module=gestionnaire&action=commandes_fournisseurs&id_buvette=<?= $id_buvette ?>&statut=Livrée" class="btn <?= $statut_filtre == 'Livrée' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill">Livrées</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                    <tr>
                        <th>N° Commande</th>
                        <th>Fournisseur</th>
                        <th>Date</th>
                        <th>Livraison prévue</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($commandes)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-1 d-block mb-3"></i>
                                <h5>Aucune commande pour le moment</h5>
                                <p>Commencez par passer une commande auprès d'un fournisseur</p>
                                <a href="index.php?module=gestionnaire&action=fournisseurs&id_buvette=<?= $id_buvette ?>" class="btn btn-primary rounded-pill mt-2">
                                    <i class="bi bi-truck"></i> Voir les fournisseurs
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($commandes as $c):
                            $badgeClass = match($c['statut']) {
                                'En attente' => 'bg-warning',
                                'Validée' => 'bg-info',
                                'Expédiée' => 'bg-primary',
                                'Livrée' => 'bg-success',
                                'Annulée' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($c['numero_commande']) ?></strong></td>
                                <td><?= htmlspecialchars($c['nom_fournisseur']) ?></td>
                                <td><?= date('d/m/Y', strtotime($c['date_commande'])) ?></td>
                                <td><?= $c['date_livraison_prevue'] ? date('d/m/Y', strtotime($c['date_livraison_prevue'])) : '-' ?></td>
                                <td class="fw-bold"><?= number_format($c['montant_total'], 2) ?> €</td>
                                <td><span class="badge <?= $badgeClass ?> rounded-pill"><?= $c['statut'] ?></span></td>
                                <td>
                                    <a href="index.php?module=gestionnaire&action=details_commande_fournisseur&id_commande=<?= $c['id_commande_fournisseur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-sm btn-outline-dark rounded-pill">Détails</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour au tableau de bord</a>
            </div>
        </div>
        <?php
    }

    public function afficherDetailsCommandeFournisseur($commande, $lignes, $id_buvette, $token) {
        $badgeClass = match($commande['statut']) {
            'En attente' => 'bg-warning',
            'Validée' => 'bg-info',
            'Expédiée' => 'bg-primary',
            'Livrée' => 'bg-success',
            'Annulée' => 'bg-danger',
            default => 'bg-secondary'
        };
        ?>
        <div class="container mt-5 pt-5">
            <div class="card border-0 shadow-lg rounded-5 p-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="font-handwritten mb-0">Commande <?= htmlspecialchars($commande['numero_commande']) ?></h2>
                    <span class="badge <?= $badgeClass ?> rounded-pill px-4 py-2"><?= $commande['statut'] ?></span>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Fournisseur:</strong> <?= htmlspecialchars($commande['nom_fournisseur']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($commande['email']) ?></p>
                        <p><strong>Téléphone:</strong> <?= htmlspecialchars($commande['telephone']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date de commande:</strong> <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></p>
                        <p><strong>Livraison prévue:</strong> <?= date('d/m/Y', strtotime($commande['date_livraison_prevue'])) ?></p>
                        <?php if ($commande['date_livraison_reelle']): ?>
                            <p><strong>Livraison réelle:</strong> <?= date('d/m/Y', strtotime($commande['date_livraison_reelle'])) ?></p>
                        <?php endif; ?>
                        <p><strong>Commandé par:</strong> <?= htmlspecialchars($commande['nom'].' '.$commande['prenom']) ?></p>
                    </div>
                </div>

                <?php if ($commande['notes']): ?>
                    <div class="alert alert-info rounded-4">
                        <strong>Notes:</strong> <?= htmlspecialchars($commande['notes']) ?>
                    </div>
                <?php endif; ?>

                <h5 class="fw-bold mb-3">Détails de la commande</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th class="text-end">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($lignes as $l): ?>
                            <tr>
                                <td>
                                    <img src="public/img/<?= htmlspecialchars($l['image_produit']) ?>" style="height: 40px; object-fit: contain;" class="me-2">
                                    <strong><?= htmlspecialchars($l['nom_produit']) ?></strong>
                                </td>
                                <td><?= $l['quantite'] ?></td>
                                <td><?= number_format($l['prix_unitaire'], 2) ?> €</td>
                                <td class="text-end fw-bold"><?= number_format($l['quantite'] * $l['prix_unitaire'], 2) ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr class="table-dark">
                            <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                            <td class="text-end fw-bold"><?= number_format($commande['montant_total'], 2) ?> €</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <?php if ($commande['statut'] === 'Expédiée'): ?>
                        <form action="index.php?module=gestionnaire&action=valider_livraison&id_buvette=<?= $id_buvette ?>" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            <input type="hidden" name="id_commande" value="<?= $commande['id_commande_fournisseur'] ?>">
                            <button type="submit" class="btn btn-success rounded-pill" onclick="return confirm('Confirmer la réception ? Les stocks seront mis à jour.');">
                                <i class="bi bi-check-circle"></i> Valider la réception
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($commande['statut'] === 'En attente'): ?>
                        <a href="index.php?module=gestionnaire&action=changer_statut_commande&id_commande=<?= $commande['id_commande_fournisseur'] ?>&statut=Validée&id_buvette=<?= $id_buvette ?>" class="btn btn-info rounded-pill">Valider</a>
                        <a href="index.php?module=gestionnaire&action=changer_statut_commande&id_commande=<?= $commande['id_commande_fournisseur'] ?>&statut=Annulée&id_buvette=<?= $id_buvette ?>" class="btn btn-danger rounded-pill" onclick="return confirm('Annuler cette commande ?');">Annuler</a>
                    <?php endif; ?>

                    <?php if ($commande['statut'] === 'Validée'): ?>
                        <a href="index.php?module=gestionnaire&action=changer_statut_commande&id_commande=<?= $commande['id_commande_fournisseur'] ?>&statut=Expédiée&id_buvette=<?= $id_buvette ?>" class="btn btn-primary rounded-pill">Marquer comme expédiée</a>
                    <?php endif; ?>

                    <a href="index.php?module=gestionnaire&action=commandes_fournisseurs&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour</a>
                </div>
            </div>
        </div>
        <?php
    }

    // ==================== TRÉSORERIE ====================
    public function afficherTresorerie($tresorerie, $mouvements, $stats, $id_buvette, $token) {
        ?>
        <div class="container mt-5 pt-5">
            <h2 class="font-handwritten mb-4">Trésorerie de la Buvette</h2>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card border-0 shadow-lg rounded-4 p-4 bg-success text-white">
                        <h6 class="text-uppercase small mb-1" style="opacity: 0.8;">Solde Actuel</h6>
                        <h2 class="fw-bold mb-0"><?= number_format($tresorerie['solde_actuel'], 2) ?> €</h2>
                        <small style="opacity: 0.8;">Mis à jour: <?= date('d/m/Y H:i', strtotime($tresorerie['derniere_maj'])) ?></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h6 class="text-uppercase small text-muted mb-1">Entrées (30 jours)</h6>
                        <h3 class="fw-bold text-success mb-0">+<?= number_format($stats['total_entrees'] ?? 0, 2) ?> €</h3>
                        <small class="text-muted"><?= $stats['nb_mouvements'] ?? 0 ?> mouvements</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h6 class="text-uppercase small text-muted mb-1">Sorties (30 jours)</h6>
                        <h3 class="fw-bold text-danger mb-0">-<?= number_format($stats['total_sorties'] ?? 0, 2) ?> €</h3>
                        <small class="text-muted">Achats & Frais</small>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Ajouter un mouvement manuel</h5>
                </div>

                <form action="index.php?module=gestionnaire&action=ajouter_mouvement&id_buvette=<?= $id_buvette ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $token ?>">

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Type</label>
                            <select name="type_mouvement" class="form-select rounded-pill" required>
                                <option value="Entrée">Entrée (+)</option>
                                <option value="Sortie">Sortie (-)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Montant (€)</label>
                            <input type="number" name="montant" class="form-control rounded-pill" step="0.01" min="0.01" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Catégorie</label>
                            <select name="categorie" class="form-select rounded-pill" required>
                                <option value="Vente">Vente</option>
                                <option value="Achat fournisseur">Achat fournisseur</option>
                                <option value="Frais divers">Frais divers</option>
                                <option value="Remboursement">Remboursement</option>
                                <option value="Apport">Apport</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary rounded-pill w-100">Ajouter</button>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold">Description</label>
                        <input type="text" name="description" class="form-control rounded-pill" placeholder="Ex: Vente événement annuel" required>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-4">Historique des mouvements</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Catégorie</th>
                            <th>Description</th>
                            <th>Par</th>
                            <th class="text-end">Montant</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($mouvements as $m):
                            $isEntree = ($m['type_mouvement'] === 'Entrée');
                            $textClass = $isEntree ? 'text-success' : 'text-danger';
                            $signe = $isEntree ? '+' : '-';
                            ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($m['date_mouvement'])) ?></td>
                                <td>
                                        <span class="badge <?= $isEntree ? 'bg-success' : 'bg-danger' ?> rounded-pill">
                                            <?= $m['type_mouvement'] ?>
                                        </span>
                                </td>
                                <td><?= htmlspecialchars($m['categorie']) ?></td>
                                <td><?= htmlspecialchars($m['description']) ?></td>
                                <td><?= htmlspecialchars($m['nom'].' '.$m['prenom']) ?></td>
                                <td class="text-end fw-bold <?= $textClass ?>">
                                    <?= $signe ?><?= number_format($m['montant'], 2) ?> €
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour au tableau de bord</a>
            </div>
        </div>
        <?php
    }

    // ==================== NOTIFICATIONS ====================
    public function afficherNotification($message, $type = 'success') {
        $bgClass = ($type === 'success') ? 'bg-success' : 'bg-danger';
        ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast align-items-center text-white <?= $bgClass ?> border-0 shadow-lg rounded-4" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($message) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toastElement = document.getElementById('liveToast');
                const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                toast.show();
            });
        </script>
        <?php
    }

    // ==================== FIDÉLITÉ ====================
    public function afficherGestionFidelite($clients, $id_buvette) {
        ?>
        <div class="container mt-5 pt-5">
            <h2 class="font-handwritten mb-4">Programme de Fidélité</h2>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-warning bg-opacity-10">
                        <h6 class="text-uppercase small fw-bold">🥉 BRONZE</h6>
                        <p class="mb-0 small">0-499 points<br>Aucun avantage</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%);">
                        <h6 class="text-uppercase small fw-bold">🥈 ARGENT</h6>
                        <p class="mb-0 small">500-1499 points<br><strong>5% de réduction</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #FFD700 0%, #FFF8DC 100%);">
                        <h6 class="text-uppercase small fw-bold">🥇 OR</h6>
                        <p class="mb-0 small">1500+ points<br><strong>10% de réduction</strong></p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3">Liste des Clients</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Palier</th>
                            <th>Points actuels</th>
                            <th>Total cumulé</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($clients as $c):
                            $badgeClass = match($c['palier']) {
                                'Bronze' => 'bg-warning',
                                'Argent' => 'bg-secondary',
                                'Or' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            $icon = match($c['palier']) {
                                'Bronze' => '🥉',
                                'Argent' => '🥈',
                                'Or' => '🥇',
                                default => ''
                            };
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?></strong></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><span class="badge <?= $badgeClass ?> rounded-pill"><?= $icon ?> <?= $c['palier'] ?></span></td>
                                <td><strong><?= $c['points_actuels'] ?></strong> pts</td>
                                <td><?= $c['points_total_cumules'] ?> pts</td>
                                <td class="text-center">
                                    <a href="index.php?module=gestionnaire&action=details_fidelite&id_client=<?= $c['id_utilisateur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-sm btn-outline-dark rounded-pill">Détails</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour au tableau de bord</a>
            </div>
        </div>
        <?php
    }

    public function afficherDetailsFidelite($client, $points, $historique, $id_buvette, $token) {
        $badgeClass = match($points['palier']) {
            'Bronze' => 'bg-warning',
            'Argent' => 'bg-secondary',
            'Or' => 'bg-warning text-dark',
            default => 'bg-secondary'
        };
        $icon = match($points['palier']) {
            'Bronze' => '🥉',
            'Argent' => '🥈',
            'Or' => '🥇',
            default => ''
        };
        ?>
        <div class="container mt-5 pt-5">
            <div class="card border-0 shadow-lg rounded-5 p-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="font-handwritten mb-1"><?= htmlspecialchars($client['nom'].' '.$client['prenom']) ?></h2>
                        <p class="text-muted mb-0"><?= htmlspecialchars($client['email']) ?></p>
                    </div>
                    <span class="badge <?= $badgeClass ?> rounded-pill px-4 py-2" style="font-size: 1.2rem;">
                        <?= $icon ?> <?= $points['palier'] ?>
                    </span>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-success bg-opacity-10 p-4 text-center">
                            <h6 class="text-uppercase small text-muted mb-1">Points Actuels</h6>
                            <h2 class="fw-bold text-success mb-0"><?= $points['points_actuels'] ?></h2>
                            <small class="text-muted">Utilisables : <?= floor($points['points_actuels'] / 100) * 10 ?> € de réduction</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-primary bg-opacity-10 p-4 text-center">
                            <h6 class="text-uppercase small text-muted mb-1">Total Cumulé</h6>
                            <h2 class="fw-bold text-primary mb-0"><?= $points['points_total_cumules'] ?></h2>
                            <small class="text-muted">
                                <?php if ($points['palier'] === 'Bronze'): ?>
                                    Encore <?= 500 - $points['points_total_cumules'] ?> pts pour Argent
                                <?php elseif ($points['palier'] === 'Argent'): ?>
                                    Encore <?= 1500 - $points['points_total_cumules'] ?> pts pour Or
                                <?php else: ?>
                                    Palier maximum atteint !
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 bg-light p-4 mb-4">
                    <h6 class="fw-bold mb-3">Ajuster les points manuellement</h6>
                    <form action="index.php?module=gestionnaire&action=ajuster_points&id_buvette=<?= $id_buvette ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <input type="hidden" name="id_client" value="<?= $client['id_utilisateur'] ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Points</label>
                                <input type="number" name="points" class="form-control" placeholder="+100 ou -50" required>
                                <small class="text-muted">Positif = ajout, Négatif = retrait</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Raison</label>
                                <input type="text" name="description" class="form-control" placeholder="Ex: Bonus anniversaire" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Valider</button>
                            </div>
                        </div>
                    </form>
                </div>

                <h5 class="fw-bold mb-3">Historique des Points</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Points</th>
                            <th>Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($historique as $h):
                            $isGain = ($h['type_mouvement'] === 'Gain');
                            $textClass = $isGain ? 'text-success' : 'text-danger';
                            $signe = $isGain ? '+' : '';
                            ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($h['date_mouvement'])) ?></td>
                                <td>
                                        <span class="badge <?= $isGain ? 'bg-success' : 'bg-danger' ?> rounded-pill">
                                            <?= $h['type_mouvement'] ?>
                                        </span>
                                </td>
                                <td class="fw-bold <?= $textClass ?>"><?= $signe ?><?= $h['points'] ?></td>
                                <td><?= htmlspecialchars($h['description']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <a href="index.php?module=gestionnaire&action=fidelite&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour à la liste</a>
            </div>
        </div>
        <?php
    }
}