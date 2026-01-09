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
                            <label class="form-label fw-bold">Prix (en centimes)</label>
                            <input type="number" name="prix" class="form-control rounded-pill" placeholder="250" required>
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
                                    <label class="form-label fw-bold">Prix (en centimes)</label>
                                    <input type="number" name="prix_produit" class="form-control border-dark text-center fw-bold" value="<?= $produit['prix_produit'] ?>" required>
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

    public function afficherGrilleGlobale($produits, $id_buvette) {
        ?>
        <div class="module-gestionnaire container mt-5 pt-5">
            <h2 class="font-handwritten text-dark mb-4">États des stocks</h2>

            <div class="d-flex flex-wrap gap-3 align-items-center mb-5">
                <div class="input-group flex-grow-1" style="max-width: 400px;">
                    <span class="input-group-text bg-white border-dark border-end-0 rounded-start-pill">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control border-dark border-start-0 rounded-end-pill bg-white" placeholder="RECHERCHER UN PRODUIT...">
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="fw-bold small text-uppercase">Filtrer par :</label>
                    <select id="typeFilter" class="form-select border-dark rounded-pill" style="width: auto;">
                        <option value="all">Tous les types</option>
                        <option value="Boisson Chaude">Boisson Chaude</option>
                        <option value="Boisson Froide">Boisson Froide</option>
                        <option value="Nourriture Chaude">Nourriture Chaude</option>
                        <option value="Nourriture Froide">Nourriture Froide</option>
                    </select>
                </div>

                <div class="ms-auto">
                    <?php if (isset($_GET['alerte'])): ?>
                        <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>" class="btn btn-outline-dark rounded-pill px-4">TOUT AFFICHER</a>
                    <?php else: ?>
                        <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>&alerte=1" class="btn btn-dark rounded-pill px-4">ALERTES STOCK</a>
                    <?php endif; ?>
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
                                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle"></span>
                                <?php endif; ?>
                                <div class="mb-2 d-flex align-items-center justify-content-center" style="height: 80px;">
                                    <img src="public/img/<?= htmlspecialchars($p['image_produit']) ?>" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                </div>
                                <h6 class="small fw-bold text-uppercase mb-1 text-dark text-truncate"><?= htmlspecialchars($p['nom_produit']) ?></h6>
                                <p class="mb-0 small <?= $alerte ? 'fw-bold text-danger' : 'text-muted' ?>">Stock : <?= $p['quantite'] ?></p>
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

                // 1. Filtrer les cartes
                cards.forEach(card => {
                    const productName = card.getAttribute('data-name');
                    const productType = card.getAttribute('data-type');
                    const matchesText = productName.includes(textValue);
                    const matchesType = (typeValue === 'all' || productType === typeValue);

                    card.style.display = (matchesText && matchesType) ? '' : 'none';
                });

                // 2. Filtrer les titres de catégories
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

        <style>
            .transition-hover:hover {
                transform: translateY(-5px);
                transition: transform 0.3s ease;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
            }
        </style>
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
}