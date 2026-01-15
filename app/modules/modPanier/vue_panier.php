<?php

class VuePanier
{
    public function afficherPanier($produits, $totalGlobal, $aDesHistoriques)
    {
        ?>
        <?php if (isset($_SESSION['modal_error'])): ?>

        <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-danger text-white border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Oups ! Problème de solde</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-wallet2 text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="fs-5"><?= $_SESSION['modal_error'] ?></p>
                        <p class="text-muted small">Veuillez recharger votre compte auprès d'un administrateur.</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">J'ai compris</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
                myModal.show();
            });
        </script>

        <?php unset($_SESSION['modal_error']); ?>

    <?php endif; ?>
        <div class="container mt-5 pt-5">
            <h1 class="mb-4 font-handwritten">Votre Panier - Buvette : <?= htmlspecialchars($_SESSION['nom_buvette']) ?></h1>

            <?php if (empty($produits)): ?>
                <div class="alert alert-info text-center py-5 rounded-4">
                    <h3>Votre panier est vide</h3>
                    <p class="mb-4">Il semble que vous n'ayez pas encore craqué pour nos produits !</p>
                    <a href="index.php?module=menu&action=afficher" class="btn bg-custom-dark rounded-pill px-4 text-white">
                        Découvrir nos produits
                    </a>
                    <?php if ($aDesHistoriques): ?>
                        <div class="mt-4 text-center">
                            <a href="index.php?module=compte&action=historique" class="btn bg-custom-dark rounded-pill px-4 text-white">
                                <i class="bi bi-clock-history me-2"></i>Voir vos anciennes commandes
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($produits as $prod):
                                $id = $prod['id_produit'];

                                $qte = $prod['quantite'];

                                $prixUnit = isset($prod['prix_unitaire_moment_vente']) ? $prod['prix_unitaire_moment_vente'] : $prod['prix_produit'];
                                $totalLigne = $prixUnit * $qte;
                                ?>
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-secondary-subtle">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-md-3">
                                            <div class="bg-danger-subtle d-flex align-items-center justify-content-center overflow-hidden" style="height: 140px;">
                                                <?php if(!empty($prod['image_produit'])): ?>
                                                    <img src="public/img/produits/<?= htmlspecialchars($prod['image_produit']) ?>"
                                                         alt="<?= htmlspecialchars($prod['nom_produit']) ?>"
                                                         class="w-100 h-100 object-fit-cover">
                                                <?php else: ?>
                                                    <span class="text-muted fw-bold">IMG</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="col-md-9">
                                            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                                                <div class="flex-grow-1">
                                                    <h5 class="card-title fw-bold font-serif mb-1"><?= htmlspecialchars($prod['nom_produit']) ?></h5>
                                                    <p class="card-text text-muted small mb-0"><?= htmlspecialchars(isset($prod['description']) ? $prod['description'] : '') ?></p>
                                                    <p class="mt-2 mb-0 fw-bold"><?= number_format($prixUnit, 2) ?> €</p>
                                                </div>

                                                <div class="d-flex align-items-center bg-white rounded-3 p-1 shadow-sm">
                                                    <a href="index.php?module=panier&action=diminuer&id_produit=<?= $id ?>"
                                                       class="btn btn-sm btn-link text-dark text-decoration-none px-2">
                                                        <i class="bi bi-dash"></i>
                                                    </a>

                                                    <input type="text" readonly value="<?= $qte ?>"
                                                           class="form-control form-control-sm border-0 text-center p-0 fw-bold bg-transparent"
                                                           style="width: 30px;">

                                                    <a href="index.php?module=panier&action=ajouter&id_produit=<?= $id ?>"
                                                       class="btn btn-sm btn-link text-dark text-decoration-none px-2">
                                                        <i class="bi bi-plus"></i>
                                                    </a>
                                                </div>

                                                <div class="text-end" style="min-width: 80px;">
                                                    <span class="fw-bold fs-5"><?= number_format($totalLigne, 2) ?> €</span>
                                                </div>

                                                <div>
                                                    <a href="index.php?module=panier&action=supprimer&id_produit=<?= $id ?>"
                                                       class="btn btn-outline-danger border-0 rounded-circle p-2"
                                                       title="Supprimer">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 bg-secondary-subtle rounded-4 shadow-sm p-4 sticky-top" style="top: 100px; z-index: 10;">
                            <h4 class="font-serif mb-4">Récapitulatif de commande</h4>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Produits</span>
                                <span class="fw-bold"><?= number_format($totalGlobal, 2) ?> €</span>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between mb-4">
                                <span class="fs-5 fw-bold">Total</span>
                                <span class="fs-4 fw-bold text-success"><?= number_format($totalGlobal, 2) ?> €</span>
                            </div>

                            <a href="index.php?module=panier&action=valider" class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow-sm mb-3">
                                Valider ma commande
                            </a>

                            <a href="index.php?module=menu&action=afficher" class="d-block text-center text-decoration-none text-muted small">
                                Continuer mes achats
                            </a>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
        <?php
    }
}
?>