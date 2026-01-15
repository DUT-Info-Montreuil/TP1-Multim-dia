<?php
class VueMenu {

    public function afficherProduits($produits, $filtreActuel = 'all') {
        $types_produits = ['Boisson Chaude', 'Boisson Froide', 'Nourriture Chaude', 'Nourriture Froide'];
        ?>

        <div class="container mt-5 pt-5 pb-5">

            <div class="text-center mb-5">
                <h1 class="font-handwritten display-4 mb-3">La Carte - <?= htmlspecialchars($_SESSION['nom_buvette']) ?></h1>

                <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">

                    <a href="index.php?module=menu&action=afficher&filtre=all"
                       class="btn rounded-pill px-4 fw-bold shadow-sm <?= ($filtreActuel == 'all') ? 'btn-dark text-white' : 'btn-outline-dark' ?>">
                        Tout
                    </a>

                    <?php foreach ($types_produits as $type):
                        $isActive = ($filtreActuel === $type);
                        ?>
                        <a href="index.php?module=menu&action=afficher&filtre=<?= urlencode($type) ?>"
                           class="btn rounded-pill px-4 fw-bold shadow-sm <?= $isActive ? 'btn-dark text-white' : 'btn-outline-dark' ?>">
                            <?= htmlspecialchars($type) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($produits as $produit):
                    $isRupture = ($produit['quantite'] <= 0);
                    ?>
                    <div class="col-lg-6">
                        <div class="card card-menu border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-white">
                            <div class="row g-0 h-100">

                                <div class="col-md-5 position-relative overflow-hidden" style="height: 220px;">
                                    <?php if (!empty($produit['image_produit'])): ?>
                                        <img src="public/img/produits/<?= htmlspecialchars($produit['image_produit']) ?>"
                                             class="w-100 h-100 object-fit-cover position-absolute start-0 top-0 transition-zoom"
                                             alt="<?= htmlspecialchars($produit['nom_produit']) ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted">
                                            <i class="bi bi-cup-hot display-4 opacity-50"></i>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($isRupture): ?>
                                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center backdrop-blur">
                                            <span class="badge bg-danger text-uppercase px-3 py-2 rounded-pill shadow-sm">
                                                <i class="bi bi-x-circle me-1"></i> Épuisé
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-7">
                                    <div class="card-body d-flex flex-column h-100 p-4">

                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h3 class="card-title h4 font-serif fw-bold mb-0 text-truncate">
                                                <?= htmlspecialchars($produit['nom_produit']) ?>
                                            </h3>
                                            <span class="fs-5 fw-bold text-success text-nowrap ms-2">
                                                <?= number_format($produit['prix_produit'], 2) ?> €
                                            </span>
                                        </div>

                                        <p class="card-text text-muted small flex-grow-1 line-clamp-2">
                                            <?= !empty($produit['description']) ? htmlspecialchars($produit['description']) : "Aucune description disponible." ?>
                                        </p>

                                        <div class="mt-3 pt-3 border-top border-light">
                                            <?php if (!$isRupture): ?>
                                                <form action="index.php?module=panier&action=ajouter" method="POST" class="d-flex gap-2">
                                                    <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">

                                                    <button type="submit" class="btn btn-dark rounded-pill w-100 py-2 fw-bold shadow-sm btn-add-cart">
                                                        <span>Ajouter</span> <i class="bi bi-plus-lg ms-1"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-light text-muted border rounded-pill w-100 py-2 small fw-bold" disabled>
                                                    Victime de son succès
                                                </button>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($produits)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-filter-circle display-4 mb-3 d-block"></i>
                            <p class="lead">Aucun produit trouvé dans cette catégorie.</p>
                            <a href="index.php?module=menu&action=afficher&filtre=all" class="btn btn-link text-dark fw-bold">Voir tout le menu</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>