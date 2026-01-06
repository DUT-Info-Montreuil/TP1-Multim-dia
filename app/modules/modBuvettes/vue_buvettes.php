<?php

class VueBuvettes
{
    public function afficherBuvettes($buvettes)
    {
        ?>
        <div class="container mt-5 pt-5">
            <h1 class="mb-5 text-center font-handwritten">Nos Buvettes</h1>

            <?php foreach ($buvettes as $buvette): ?>

                <div class="row align-items-center bg-secondary-subtle p-4 mb-4 rounded-5 shadow-sm">

                    <div class="col-12 col-md-3 col-lg-2 text-center mb-3 mb-md-0">
                        <div class="rounded-circle bg-black mx-auto d-flex align-items-center justify-content-center text-white"
                             style="width: 120px; height: 120px;">
                            IMG
                        </div>
                    </div>

                    <div class="col-12 col-md-9 col-lg-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold font-serif"><?= htmlspecialchars($buvette['nom']) ?></h3>

                            <?php if ($buvette['est_ouverte']): ?>
                                <span class="badge bg-success rounded-pill">Ouverte</span>
                            <?php else: ?>
                                <span class="badge bg-danger rounded-pill">Fermée</span>
                            <?php endif; ?>
                        </div>

                        <p class="mb-0 mt-2">
                            Bienvenue au <?= htmlspecialchars($buvette['nom']) ?>. Venez découvrir nos produits !
                        </p>
                        <?php if (isset($_SESSION['user']) && $buvette['est_ouverte'] && ( !isset($_SESSION['id_buvette']) || $_SESSION['id_buvette']!=$buvette['id_buvette'])): ?>
                            <div class="mt-3">
                                <a href="index.php?module=menu&action=afficher&id_buvette=<?= $buvette['id_buvette'] ?>"
                                   class="btn bg-custom-dark rounded-pill px-4">
                                    Choisir cette buvette
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

            <?php if (empty($buvettes)): ?>
                <p class="text-center">Aucune buvette trouvée.</p>
            <?php endif; ?>
        </div>
        <?php
    }
}

?>
