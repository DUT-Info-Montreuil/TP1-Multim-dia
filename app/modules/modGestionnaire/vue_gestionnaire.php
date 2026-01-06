<?php
class VueGestionnaire {

    public function afficherGrilleGlobale($produits, $token) {
        ?>
        <div class="container mt-5 pt-5">
            <h2 class="font-handwritten text-white mb-4">Gestion des Stocks</h2>

            <div class="d-flex gap-3 mb-5">
                <div class="input-group w-25">
                    <input type="text" class="form-control rounded-pill border-secondary bg-transparent text-white" placeholder="RECHERCHER">
                    <span class="input-group-text bg-transparent border-0 text-white"><i class="bi bi-search"></i></span>
                </div>
                <button class="btn btn-outline-light rounded-pill px-4">FILTRE</button>
            </div>

            <div class="row g-4">
                <?php foreach ($produits as $p): ?>
                    <div class="col-6 col-md-3 col-lg-2">
                        <a href="index.php?module=gestionnaire&action=details&id=<?= $p['id_produit'] ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 border-0 shadow-sm rounded-4 p-3 text-center bg-white">
                                <img src="public/img/<?= htmlspecialchars($p['image_produit']) ?>" class="mx-auto mb-2 img-fluid" style="height: 80px; object-fit: contain;">
                                <h6 class="small fw-bold text-uppercase mb-1"><?= htmlspecialchars($p['nom_produit']) ?></h6>
                                <p class="mb-0 small text-muted"><?= $p['prix_produit'] ?> €</p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>

                <div class="col-6 col-md-3 col-lg-2">
                    <div class="card h-100 border-2 border-dashed rounded-4 d-flex align-items-center justify-content-center bg-transparent border-secondary" style="min-height: 150px;">
                        <i class="bi bi-plus-lg display-6 text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function afficherDetailsArticle($produit, $token) {
        if (!$produit) {
            echo "<div class='container mt-5 pt-5 text-white'>Produit introuvable.</div>";
            return;
        }
        ?>
        <div class="container mt-5 pt-5">
            <div class="bg-secondary-subtle rounded-5 p-5 shadow-lg">
                <div class="row g-5 align-items-center">

                    <div class="col-md-5 col-lg-4">
                        <div class="bg-white rounded-4 p-4 shadow-sm d-flex align-items-center justify-content-center" style="min-height: 350px;">
                            <img src="public/img/<?= htmlspecialchars($produit['image_produit']) ?>" class="img-fluid" style="max-height: 300px;">
                        </div>
                    </div>

                    <div class="col-md-7 col-lg-8">
                        <form method="POST" action="index.php?module=gestionnaire&action=modifier">
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            <input type="hidden" name="id" value="<?= $produit['id_produit'] ?>">

                            <h1 class="font-serif display-5 fw-bold mb-4 text-uppercase"><?= htmlspecialchars($produit['nom_produit']) ?></h1>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">NOM DU PRODUIT</label>
                                <input type="text" class="form-control rounded-pill border-0 p-3" name="nom_produit" value="<?= htmlspecialchars($produit['nom_produit']) ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">PRIX DE VENTE (TTC)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control rounded-pill border-0 p-3" name="prix_produit" value="<?= $produit['prix_produit'] ?>">
                                    <span class="input-group-text bg-transparent border-0">€</span>
                                </div>
                            </div>

                            <div class="mt-5 d-flex gap-3">
                                <button type="submit" class="btn btn-dark rounded-pill px-5 py-2 shadow">ENREGISTRER</button>
                                <a href="index.php?module=gestionnaire" class="btn btn-outline-dark rounded-pill px-5 py-2">RETOUR</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}