<?php

class VueBuvettes
{
    public function afficherBuvettes($buvettes, $buvettesAdherent, $buvettesEnAttente)
    {
        $idsMembres = array_column($buvettesAdherent, 'id_buvette');
        $idsEnAttente = array_column($buvettesEnAttente, 'id_buvette');
        ?>
        <div class="container mt-5 pt-5">
            <h1 class="mb-5 text-center font-handwritten">Nos Buvettes</h1>

            <?php foreach ($buvettes as $buvette): ?>

                <div class="row align-items-center bg-secondary-subtle p-4 mb-4 rounded-5 shadow-sm">

                    <div class="col-12 col-md-3 col-lg-2 text-center mb-3 mb-md-0">
                        <div class="rounded-4 bg-black mx-auto d-flex align-items-center justify-content-center text-white"
                             style="width: 120px; height: 120px;">
                            <img src="assets/img/<?= htmlspecialchars($buvette['image_buvette']) ?>">
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
                            <?= $buvette['description'] ?>
                        </p>
                        <?php
                        if (isset($_SESSION['user'])):
                            if (in_array($buvette['id_buvette'], $idsEnAttente)):
                                ?>
                                <div class="mt-3">
                                    <span class="fst-italic text-success fw-bold">
                                        <i class="bi bi-check-circle"></i> Votre demande d'adhésion a bien été envoyée.
                                    </span>
                                </div>

                            <?php
                            elseif (!in_array($buvette['id_buvette'], $idsMembres)):
                                ?>
                                <div class="mt-3">
                                    <a href="index.php?module=buvettes&action=adherer&id_buvette=<?= $buvette['id_buvette'] ?>"
                                       class="btn bg-custom-dark rounded-pill px-4">
                                        Adhérer à cette buvette
                                    </a>
                                </div>

                            <?php
                            elseif ($buvette['est_ouverte'] && (!isset($_SESSION['id_buvette']) || $_SESSION['id_buvette'] != $buvette['id_buvette'])):
                                ?>
                                <div class="mt-3">
                                    <a href="index.php?module=menu&action=afficher&id_buvette=<?= $buvette['id_buvette'] ?>"
                                       class="btn bg-custom-dark rounded-pill px-4">
                                        Choisir cette buvette
                                    </a>
                                </div>
                                <?php
                            elseif ($buvette['id_buvette'] == (isset($_SESSION['id_buvette']) ? $_SESSION['id_buvette'] : null)):
                                ?>
                                <div class="mt-3">
                                    <span class="fst-italic text-success fw-bold">
                                       Buvette choisie.
                                    </span>
                                </div>
                            <?php endif; ?>

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
