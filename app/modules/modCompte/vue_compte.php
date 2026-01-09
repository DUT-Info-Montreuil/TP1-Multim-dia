<?php

class VueCompte
{
    public function afficherHistorique($commandes)
    {
        ?>
        <div class="container mt-5 pt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="font-handwritten mb-0">Mes anciennes commandes</h1>
                <a href="index.php?module=menu&action=afficher" class="btn btn-outline-dark rounded-pill">
                    <i class="bi bi-arrow-left me-2"></i>Retour au menu
                </a>
            </div>

            <?php if (empty($commandes)): ?>
                <div class="alert alert-secondary text-center rounded-4 py-5">
                    <i class="bi bi-clock-history display-4 d-block mb-3"></i>
                    <p class="fs-5">Aucune commande passée pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($commandes as $cmd):
                        $badgeClass = 'bg-secondary';
                        switch ($cmd['statut']) {
                            case 'Payée':
                                $badgeClass = 'bg-success';
                                break;
                            case 'Prête':
                                $badgeClass = 'bg-info text-dark';
                                break;
                            case 'Annulée':
                                $badgeClass = 'bg-danger';
                                break;
                            case 'En cours':
                                $badgeClass = 'bg-warning text-dark';
                                break;
                        }
                        $date = new DateTime($cmd['date_commande']);

                        $modalId = "modalCmd" . $cmd['id_commande'];
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border border-2 shadow-sm rounded-4 h-100 bg-light"
                                 style="border-color: #dee2e6 !important;">
                                <div class="card-body p-4 d-flex flex-column">

                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title fw-bold mb-0 text-truncate" style="max-width: 70%;">
                                            <?= htmlspecialchars($cmd['nom_buvette']) ?>
                                        </h5>
                                        <span class="badge rounded-pill <?= $badgeClass ?>">
                        <?= htmlspecialchars($cmd['statut']) ?>
                    </span>
                                    </div>

                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-calendar-event me-2"></i>
                                        <?= $date->format('d/m/Y à H:i') ?>
                                    </p>

                                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fs-5 fw-bold text-dark"><?= number_format($cmd['prix_total'], 2) ?> €</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-dark rounded-pill px-3"
                                                data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                                            Détails
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-bottom-0">
                                            <h5 class="modal-title fw-bold">Commande #<?= $cmd['id_commande'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <div class="list-group list-group-flush rounded-0">
                                                <?php foreach ($cmd['liste_produits'] as $produit): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center me-3"
                                                                 style="width: 50px; height: 50px;">
                                                                <?php if (!empty($produit['image_produit'])): ?>
                                                                    <img src="public/images/<?= htmlspecialchars($produit['image_produit']) ?>"
                                                                         class="img-fluid w-100 h-100 object-fit-cover rounded-3"
                                                                         alt="img">
                                                                <?php else: ?>
                                                                    <i class="bi bi-cup-hot text-muted"></i>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($produit['nom_produit']) ?></h6>
                                                                <small class="text-muted">Qté
                                                                    : <?= $produit['quantite'] ?></small>
                                                            </div>
                                                        </div>
                                                        <span class="fw-bold text-muted">
                                        <?= number_format($produit['prix_unitaire_moment_vente'] * $produit['quantite'], 2) ?> €
                                    </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <div class="bg-light p-3 d-flex justify-content-between align-items-center rounded-bottom-4">
                                                <span class="text-uppercase small fw-bold text-muted">Total payé</span>
                                                <span class="fs-4 fw-bold text-success"><?= number_format($cmd['prix_total'], 2) ?> €</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
