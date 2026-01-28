<?php
class VueSolde
{
    public function afficherFormulaire($mesBuvettes) {
        $idBuvetteCourante = isset($_SESSION['id_buvette']) ? $_SESSION['id_buvette'] : null;
        $buvetteActuelle = null;

        if ($idBuvetteCourante && !empty($mesBuvettes)) {
            foreach ($mesBuvettes as $b) {
                if ($b['id_buvette'] == $idBuvetteCourante) {
                    $buvetteActuelle = $b;
                    break;
                }
            }
        }
        ?>
        <div class="container mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
                        <div class="card-header bg-success text-white p-3 border-0">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2"></i>Recharger mon solde</h5>
                        </div>

                        <div class="card-body p-4 bg-light">
                            <?php if (!$buvetteActuelle): ?>
                                <div class="alert alert-warning rounded-4 mb-0 text-center">
                                    <i class="bi bi-exclamation-triangle display-4 d-block mb-3"></i>
                                    <p class="fs-5">Aucune buvette sélectionnée.</p>
                                    <p>Veuillez entrer dans une buvette via le menu "Nos buvettes" pour recharger votre compte.</p>
                                    <a href="index.php?module=buvettes" class="btn btn-dark rounded-pill mt-2">Choisir une buvette</a>
                                </div>
                            <?php else:
                                $soldeActuel = isset($buvetteActuelle['solde']) ? $buvetteActuelle['solde'] : 0;
                                ?>
                                <form action="index.php?module=solde&action=validerRechargement" method="POST">

                                    <input type="hidden" name="id_buvette" value="<?= $buvetteActuelle['id_buvette'] ?>">

                                    <div class="mb-4 text-center">
                                        <h3 class="fw-bold text-success mb-1">
                                            <?= htmlspecialchars($buvetteActuelle['nom']) ?>
                                        </h3>
                                        <p class="text-muted mb-0">
                                            Solde actuel : <span class="fw-bold text-dark"><?= number_format($soldeActuel, 2) ?> €</span>
                                        </p>
                                    </div>

                                    <div class="mb-4">
                                        <label for="montant" class="form-label fw-bold text-muted small text-uppercase">1. Montant à ajouter</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-1 rounded-start-4 ps-3">€</span>
                                            <input type="number" name="montant" id="montant" class="form-control form-control-lg border-1 rounded-end-4" placeholder="0.00" min="1" step="0.50" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-muted small text-uppercase">2. Paiement Sécurisé (Simulation)</label>
                                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                            <div class="mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-credit-card"></i></span>
                                                    <input type="text" class="form-control border-start-0" placeholder="Numéro de carte (XXXX XXXX XXXX XXXX)" required>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="text" class="form-control" placeholder="MM / AA" required>
                                                </div>
                                                <div class="col-6">
                                                    <input type="text" class="form-control" placeholder="CVV" maxlength="3" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm transform-hover">
                                        <i class="bi bi-check-lg me-2"></i>Payer et Créditer
                                    </button>
                                </form>
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