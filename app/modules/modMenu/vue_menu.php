<?php
class VueMenu {
    public function afficherProduits($produits) {
        ?>
        <div class="container mt-5 pt-5">
            <h2 class="text-center mb-5 font-serif display-4">Carte du jour</h2>

            <div class="row">
                <?php foreach ($produits as $produit): ?>
                    <div class="col-lg-6 mb-5">
                        <div class="card border-0 h-100">
                            <div class="row g-0">

                                <div class="col-md-5 bg-secondary-subtle d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                    <?php if (!empty($produit['image_produit'])): ?>
                                        <img src="assets/img/<?= htmlspecialchars($produit['image_produit']) ?>" class="img-fluid object-fit-cover w-100 h-100" alt="<?= htmlspecialchars($produit['nom_produit']) ?>">
                                    <?php else: ?>
                                        <span class="text-muted fs-1"><i class="bi bi-cup-hot"></i></span>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-7">
                                    <div class="card-body d-flex flex-column h-100 justify-content-center ps-4">

                                        <h3 class="card-title font-serif mb-2"><?= htmlspecialchars($produit['nom_produit']) ?></h3>

                                        <p class="card-text text-muted small mb-3">
                                            <?=$produit['description']?>
                                        </p>

                                        <p class="fw-bold mb-3"><?= number_format($produit['prix_produit'], 2) ?> €</p>

                                        <div class="mt-auto">
                                            <?php if ($produit['quantite'] > 0): ?>
                                                <form action="index.php?module=panier&action=ajouter" method="POST" class="d-flex align-items-center">
                                                    <input type="hidden" name="id_produit" value="<?= $produit['id_produit'] ?>">

                                                    <button type="submit" class="btn btn-secondary bg-opacity-25 text-dark border-0 rounded-pill px-4 py-2 w-100 text-uppercase small fw-bold hover-success">
                                                        Ajouter au panier
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-light text-muted border-0 rounded-pill px-4 py-2 w-100 text-uppercase small fw-bold" disabled>
                                                    Rupture de stock
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
                    <div class="col-12 text-center">
                        <p class="lead">Aucun produit n'est disponible pour cette buvette actuellement.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
