<?php
class VueGestionnaire {

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
                            <input type="number" name="prix_produit" class="form-control rounded-pill"
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
                                    <label class="form-label fw-bold">Prix</label>
                                    <input type="number" name="prix_produit" placeholder="0.00" min="0" step="0.01" class="form-control border-dark text-center fw-bold" value="<?= $produit['prix_produit'] ?>" required>
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
        // Déterminer si le filtre alerte est actif
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
                            <?= number_format(($stats['valeur_stock'] ?? 0) / 100, 2, ',', ' ') ?> €
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
                        <h6 class="text-uppercase small mb-1" style="color: #212529 !important;">Demandes Adhésion</h6>
                        <h3 class="fw-bold mb-0" style="color: #212529 !important;"><?= $stats['demandes_count'] ?></h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <h6 class="text-uppercase small mb-1" style="color: #6c757d !important;">Membres Actifs</h6>
                        <h3 class="fw-bold mb-0" style="color: #212529 !important;"><?= $stats['membres_count'] ?></h3>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3 align-items-center mb-5">
                <div class="input-group flex-grow-1" style="max-width: 400px;">
                    <span class="input-group-text bg-white border-dark border-end-0 rounded-start-pill">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control border-dark border-start-0 rounded-end-pill bg-white" placeholder="RECHERCHER UN PRODUIT...">
                </div>

                <select id="typeFilter" class="form-select border-dark rounded-pill" style="width: auto;">
                    <option value="all">Tous les types</option>
                    <option value="Boisson Chaude">Boisson Chaude</option>
                    <option value="Boisson Froide">Boisson Froide</option>
                    <option value="Nourriture Chaude">Nourriture Chaude</option>
                    <option value="Nourriture Froide">Nourriture Froide</option>
                </select>

                <div class="ms-auto d-flex gap-2">
                    <?php if ($isAlerteActive): ?>
                        <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-danger text-white rounded-pill px-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>QUITTER ALERTES
                        </a>
                    <?php else: ?>
                        <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>&alerte=1" class="btn btn-outline-dark rounded-pill px-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>ALERTES
                        </a>
                    <?php endif; ?>

                    <a href="index.php?module=gestionnaire&action=gerer_adhesions&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill px-4">
                        <i class="bi bi-people-fill"></i>
                    </a>
                </div>
            </div>

            <div class="row g-4" id="inventoryGrid">
                <?php
                $dernierType = "";
                foreach ($produits as $p):
                    if ($dernierType != $p['type_produit']):
                        $dernierType = $p['type_produit'];
                        echo "<div class='col-12 mt-5 category-title' data-type-title='$dernierType'>
                                <h4 class='text-muted text-uppercase fw-bold border-bottom pb-2'>$dernierType</h4>
                              </div>";
                    endif;

                    $alerte = ($p['quantite'] <= $p['seuil_alerte']);
                    ?>
                    <div class="col-6 col-md-3 col-lg-2 product-card"
                         data-name="<?= strtolower(htmlspecialchars($p['nom_produit'])) ?>"
                         data-type="<?= htmlspecialchars($p['type_produit']) ?>">
                        <a href="index.php?module=gestionnaire&action=details&id=<?= $p['id_produit'] ?>&id_buvette=<?= $id_buvette ?>" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm rounded-4 p-3 text-center bg-white transition-hover">
                                <?php if ($alerte): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle" style="z-index: 2;"></span>
                                <?php endif; ?>

                                <div class="mb-2 d-flex align-items-center justify-content-center bg-light rounded-3" style="height: 100px;">
                                    <?php
                                    $imagePath = "public/img/" . htmlspecialchars($p['image_produit']);
                                    $src = (!empty($p['image_produit']) && file_exists($imagePath)) ? $imagePath : "public/img/default.png";
                                    ?>
                                    <img src="<?= $src ?>" class="img-fluid" style="max-height: 90px; object-fit: contain;" alt="<?= htmlspecialchars($p['nom_produit']) ?>">
                                </div>

                                <h6 class="small fw-bold text-uppercase mb-1 text-truncate" style="color: #212529 !important;"><?= htmlspecialchars($p['nom_produit']) ?></h6>
                                <p class="mb-0 small fw-bold" style="color: <?= $alerte ? '#dc3545' : '#6c757d' ?> !important;">Stock : <?= $p['quantite'] ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>

                <div class="col-6 col-md-3 col-lg-2">
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
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                    <tr><th>Nom</th><th>Email</th><th>Depuis le</th><th class="text-center">Action</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach($membres as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['nom']." ".$m['prenom']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td><?= date('d/m/Y', strtotime($m['date_debut'])) ?></td>
                            <td class="text-center">
                                <a href="index.php?module=gestionnaire&action=supprimer_membre&id_utilisateur=<?= $m['id_utilisateur'] ?>&id_buvette=<?= $id_buvette ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Révoquer ce client ?');">Révoquer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4"><a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-dark rounded-pill">Retour</a></div>
        </div>
        <?php
    }
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
}