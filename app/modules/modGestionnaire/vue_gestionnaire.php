<?php
class VueGestionnaire {

    public function afficherGrilleGlobale($produits, $id_buvette) {
        ?>
        <div class="module-gestionnaire container mt-5 pt-5">
            <h2 class="font-handwritten text-dark mb-4">États des stocks</h2>

            <div class="d-flex gap-3 mb-5">
                <div class="input-group w-25">
                    <input type="text" class="form-control rounded-pill border-dark bg-white" placeholder="RECHERCHER">
                </div>
                <button class="btn btn-dark rounded-pill px-4">FILTRE</button>
            </div>

            <div class="row g-4">
                <?php foreach ($produits as $p):
                    $alerte = ($p['quantite'] <= $p['seuil_alerte']);
                    ?>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="index.php?module=gestionnaire&action=details&id=<?= $p['id_produit'] ?>&id_buvette=<?= $id_buvette ?>" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm rounded-4 p-3 text-center bg-white inventory-card">
                                <?php if ($alerte): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle"></span>
                                <?php endif; ?>
                                <div class="bg-white rounded-3 p-2 mb-2 d-flex align-items-center justify-content-center" style="height: 100px;">
                                    <img src="public/img/<?= htmlspecialchars($p['image_produit']) ?>" class="img-fluid" style="max-height: 80px;">
                                </div>
                                <h6 class="small fw-bold text-uppercase <?= $alerte ? 'text-danger' : 'text-dark' ?> mb-1"><?= htmlspecialchars($p['nom_produit']) ?></h6>
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
        <?php
    }

    public function afficherFormulaireNouveauProduit($id_buvette) {
        ?>
        <div class="container mt-5 pt-5">
            <div class="card border-0 shadow-lg rounded-5 p-5">
                <h2 class="font-handwritten mb-4">Créer un nouvel article</h2>
                <form action="index.php?module=gestionnaire&action=valider_nouveau&id_buvette=<?= $id_buvette ?>"
                      method="POST"
                      enctype="multipart/form-data">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Nom de l'article</label>
                        <input type="text" name="nom" class="form-control rounded-pill" placeholder="Ex: Cookie Géant" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prix (en centimes)</label>
                            <input type="number" name="prix" class="form-control rounded-pill" placeholder="250 pour 2.50€" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Image du produit</label>
                            <input type="file" name="image_file" class="form-control rounded-pill" accept="image/*" required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-dark rounded-pill px-5">CRÉER</button>
                        <a href="index.php?module=gestionnaire&id_buvette=<?= $id_buvette ?>"
                           class="btn btn-outline-secondary rounded-pill px-4 ms-2">ANNULER</a>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    public function afficherDetailsArticle($produit, $token) {
        ?>
        <div class="module-gestionnaire container mt-5 pt-5">
            <div class="bg-secondary-subtle rounded-5 p-5 shadow-lg">
                <div class="row g-5">
                    <div class="col-md-4">
                        <div class="bg-white rounded-4 p-4 d-flex align-items-center justify-content-center" style="min-height: 300px;">
                            <img src="public/img/<?= htmlspecialchars($produit['image_produit']) ?>" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-md-8 text-dark">
                        <h1 class="text-uppercase fw-bold"><?= htmlspecialchars($produit['nom_produit']) ?></h1>
                        <p class="fs-5 mt-3">Quantité actuelle : <strong><?= $produit['quantite'] ?></strong></p>
                        <hr>
                        <p class="mb-1">Prix de vente : <?= $produit['prix_produit'] ?>€</p>

                        <div class="mt-5">
                            <a href="index.php?module=gestionnaire" class="btn btn-dark rounded-pill px-5">RETOUR</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    public function afficherSelectionBuvette($buvettes) {
        ?>
        <div class="module-gestionnaire container mt-5 pt-5 text-center">
            <h2 class="font-handwritten text-dark mb-5">Mes Buvettes en Gestion</h2>

            <?php if (empty($buvettes)): ?>
                <div class="alert alert-warning rounded-pill">
                    Vous n'êtes affecté à aucune buvette en tant que gestionnaire.
                </div>
            <?php else: ?>
                <div class="row justify-content-center g-4">
                    <?php foreach ($buvettes as $b): ?>
                        <div class="col-md-4">
                            <a href="index.php?module=gestionnaire&id_buvette=<?= $b['id_buvette'] ?>" class="text-decoration-none">
                                <div class="card p-4 shadow-sm rounded-5 bg-white border-0 hover-card">
                                    <div class="rounded-circle bg-light mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="bi bi-shop fs-1 text-dark"></i>
                                    </div>
                                    <h4 class="text-uppercase fw-bold text-dark"><?= htmlspecialchars($b['nom']) ?></h4>
                                    <p class="small text-muted mb-0">Cliquez pour gérer les stocks</p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}