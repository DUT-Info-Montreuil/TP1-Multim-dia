<?php

class VueDetailsCommande
{
    public function afficherDetails($commande)
    {
        $statut = $commande['statut'];
        $id = $commande['id_commande'];
        $buvette = $commande['nom_buvette'];
        $date = new DateTime($commande['date_commande']);
        $produits = $commande['produits'];
        $total = $commande['prix_total'];
        $est_paye = $commande['est_paye'];

        $steps = [
            'En attente' => 1,
            'Préparation' => 2,
            'Arrivé' => 3,
            'Parti' => 4

        ];
        $currentStep = isset($steps[$statut]) ? $steps[$statut] : 0;
        $isAnnulee = ($statut === 'Annulée');

        ?>
        <div class="container mt-5 pt-5">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="font-handwritten mb-0">Suivi de commande</h1>
                    <p class="text-muted mb-0">Buvette : <span class="fw-bold text-dark"><?= htmlspecialchars($buvette) ?></span></p>
                </div>
                <a href="index.php?module=compte&action=historique" class="btn btn-outline-dark rounded-pill">
                    <i class="bi bi-house me-2"></i>Mes commandes
                </a>
            </div>

            <div class="row g-4">

                <div class="col-lg-5">

                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                        <div class="card-body p-4 text-center">
                            <h5 class="text-muted text-uppercase small fw-bold mb-3">Commande #<?= $id ?></h5>

                            <?php if ($isAnnulee): ?>
                                <div class="alert alert-danger rounded-4">
                                    <i class="bi bi-x-circle-fill display-4 d-block mb-2"></i>
                                    <strong>Cette commande a été annulée.</strong>
                                </div>
                            <?php else: ?>
                                <div class="position-relative m-4">
                                    <div class="progress" style="height: 2px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= ($currentStep - 1) * 33 ?>%;"></div>
                                    </div>

                                    <div class="position-absolute top-0 start-0 translate-middle btn btn-sm rounded-pill <?= $currentStep >= 1 ? 'btn-success' : 'btn-secondary' ?>" style="width: 2rem; height:2rem;">1</div>
                                    <div class="position-absolute top-0 start-50 translate-middle btn btn-sm rounded-pill <?= $currentStep >= 2 ? 'btn-success' : 'btn-secondary' ?>" style="width: 2rem; height:2rem;">2</div>
                                    <div class="position-absolute top-0 start-100 translate-middle btn btn-sm rounded-pill <?= $currentStep >= 3 ? 'btn-success' : 'btn-secondary' ?>" style="width: 2rem; height:2rem;">3</div>
                                </div>

                                <div class="d-flex justify-content-between text-muted small fw-bold mt-2">
                                    <span>Validée</span>
                                    <span>En prépa.</span>
                                    <span>Prête</span>
                                </div>

                                <div class="mt-4">
                                    <?php if($currentStep == 1): ?>
                                        <div class="badge bg-warning text-dark fs-6 rounded-pill px-3 py-2 animate-pulse">
                                            <i class="bi bi-hourglass-split me-2"></i>En attente de validation
                                        </div>
                                        <p class="text-muted small mt-2">La buvette a bien reçu votre commande.</p>

                                    <?php elseif($currentStep == 2): ?>
                                        <div class="badge bg-info text-dark fs-6 rounded-pill px-3 py-2">
                                            <i class="bi bi-fire me-2"></i>En préparation
                                        </div>
                                        <p class="text-muted small mt-2">Vos produits sont en cours de préparation.</p>

                                    <?php elseif($currentStep == 3): ?>
                                        <div class="badge bg-success fs-6 rounded-pill px-3 py-2 animate-bounce">
                                            <i class="bi bi-check-lg me-2"></i>Commande Prête !
                                        </div>
                                        <p class="text-success small mt-2 fw-bold">Rendez-vous au comptoir pour le retrait.</p>

                                    <?php else: ?>
                                        <div class="badge bg-secondary fs-6 rounded-pill px-3 py-2">
                                            <i class="bi bi-check2-all me-2"></i>Terminée
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 bg-custom-dark text-white">
                        <div class="card-body p-4 text-center">
                            <p class="mb-2 text-white-50 small text-uppercase">Code de retrait</p>
                            <h2 class="display-4 fw-bold font-serif letter-spacing-2"><?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h2>
                            <p class="mb-0 small text-white-50">Présentez ce numéro au barman</p>
                        </div>
                    </div>

                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                        <div class="card-header bg-white border-bottom-0 rounded-top-4 p-4">
                            <h4 class="font-serif mb-0">Détails de la commande</h4>
                            <p class="text-muted small mb-0">Effectuée le <?= $date->format('d/m/Y à H:i') ?></p>
                        </div>

                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($produits as $prod):
                                    $prix = isset($prod['prix_unitaire_moment_vente']) ? $prod['prix_unitaire_moment_vente'] : $prod['prix_produit'];
                                    ?>
                                    <div class="list-group-item bg-transparent border-bottom px-4 py-3 d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-white rounded-3 border d-flex align-items-center justify-content-center overflow-hidden"
                                                 style="width: 60px; height: 60px;">

                                                <?php if (!empty($prod['image_produit'])): ?>
                                                    <img src="public/img/produits/<?= htmlspecialchars($prod['image_produit']) ?>"
                                                         alt="<?= htmlspecialchars($prod['nom_produit']) ?>"
                                                         class="w-100 h-100 object-fit-cover">
                                                <?php else: ?>
                                                    <i class="bi bi-cup-hot text-muted"></i>
                                                <?php endif; ?>

                                            </div>
                                        </div>

                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($prod['nom_produit']) ?></h6>
                                            <small class="text-muted">Quantité : x<?= $prod['quantite'] ?></small>
                                        </div>

                                        <div class="text-end">
                                            <span class="fw-bold text-dark"><?= number_format($prix * $prod['quantite'], 2) ?> €</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top-0 rounded-bottom-4 p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-5 text-muted">Total à payer</span>
                                <span class="fs-3 fw-bold text-success"><?= number_format($total, 2) ?> €</span>
                            </div>
                            <?php if($est_paye == 0): ?>
                                <div class="alert alert-light border text-center mt-3 mb-0 text-muted small">
                                    <i class="bi bi-info-circle me-1"></i> Le paiement s'effectuera lors du retrait au comptoir.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success border text-center mt-3 mb-0 text-success small">
                                    <i class="bi bi-check-circle me-1"></i> Paiement effectué avec succès.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php
    }
}
?>