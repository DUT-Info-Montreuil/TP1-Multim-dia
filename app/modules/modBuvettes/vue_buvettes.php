<?php

class VueBuvettes
{
    public function afficherBuvettes($buvettes,$buvettesAdherent, $adhesions, $enAttente, $staffBuvettes = [])
    {
        $idsAdhesions = array_column($buvettesAdherent, 'id_buvette');
        // Préparation des données
        $idsMembres = array_column($adhesions, 'id_buvette');
        $idsEnAttente = array_column($enAttente, 'id_buvette');
        $idsStaff = array_column($staffBuvettes, 'id_buvette'); // Récupération des ID Staff

        $mesBuvettes = [];
        $autresBuvettes = [];

        foreach ($buvettes as $b) {
            // On considère qu'on "a" la buvette si on est membre OU staff
            if (in_array($b['id_buvette'], $idsMembres) || in_array($b['id_buvette'], $idsStaff)) {
                $mesBuvettes[] = $b;
            } else {
                $autresBuvettes[] = $b;
            }
        }
        ?>
        <div class="container mt-5 pt-5">
            <h1 class="mb-5 text-center font-handwritten">Nos Buvettes</h1>

            <div class="mb-5 px-md-5">
                <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white p-1 border">
                    <span class="input-group-text bg-white border-0 ps-4"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchBuvette" class="form-control border-0 fs-5" placeholder="Trouver une buvette..." onkeyup="filtrerBuvettes()">
                </div>
            </div>

            <div class="accordion border-0" id="buvetteAccordion">

                <div class="accordion-item border-0 mb-4 shadow-sm rounded-5 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button bg-custom-dark text-white fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMembres">
                            <i class="bi bi-star-fill me-3 text-warning"></i> MES ACCÈS (<?= count($mesBuvettes) ?>)
                        </button>
                    </h2>
                    <div id="collapseMembres" class="accordion-collapse collapse show" data-bs-parent="#buvetteAccordion">
                        <div class="accordion-body bg-light p-4">
                            <?php $this->renderListe($mesBuvettes, $idsMembres, $idsEnAttente, $idsStaff, true); ?>
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 shadow-sm rounded-5 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-secondary text-white fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAutres">
                            <i class="bi bi-geo-alt-fill me-3"></i> AUTRES ÉTABLISSEMENTS
                        </button>
                    </h2>
                    <div id="collapseAutres" class="accordion-collapse collapse" data-bs-parent="#buvetteAccordion">
                        <div class="accordion-body bg-light p-4">
                            <?php $this->renderListe($autresBuvettes, $idsMembres, $idsEnAttente, $idsStaff, false); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            function filtrerBuvettes() {
                let input = document.getElementById('searchBuvette').value.toLowerCase();
                let cards = document.querySelectorAll('.buvette-card');
                cards.forEach(card => {
                    let name = card.getAttribute('data-name').toLowerCase();
                    card.style.display = name.includes(input) ? "block" : "none";
                });
            }
        </script>
        <?php
    }

    // Ajout de $idsStaff dans les arguments
    private function renderListe($buvettes, $idsMembres, $idsEnAttente, $idsStaff, $estMembreSection) {
        if (empty($buvettes)) {
            echo "<div class='text-center py-4 text-muted'>Aucune buvette dans cette section.</div>";
            return;
        }

        $currentBuvetteId = isset($_SESSION['id_buvette']) ? $_SESSION['id_buvette'] : null;

        foreach ($buvettes as $b) {
            $id = $b['id_buvette'];
            $estEnAttente = in_array($id, $idsEnAttente);
            $estStaff = in_array($id, $idsStaff); // Vérification du statut Staff
            $estActuelle = ($id == $currentBuvetteId);

            // Gestion de la couleur de bordure
            $borderClass = 'border-secondary';
            if ($estActuelle) {
                $borderClass = 'border-success';
            } elseif ($estStaff) {
                $borderClass = 'border-warning'; // Jaune pour le staff
            } elseif ($estMembreSection) {
                // $borderClass = 'border-dark'; // Optionnel pour les membres simples
            }
            ?>
            <div class="buvette-card mb-4" data-name="<?= htmlspecialchars($b['nom']) ?>">
                <div class="row align-items-center bg-white p-4 rounded-5 shadow-sm mx-0 border-start border-5 <?= $borderClass ?>">

                    <div class="col-12 col-md-3 col-lg-2 text-center mb-3 mb-md-0">
                        <div class="rounded-4 bg-light mx-auto d-flex align-items-center justify-content-center overflow-hidden border"
                             style="width: 120px; height: 120px;">
                            <?php if(!empty($b['image_buvette'])): ?>
                                <img src="public/images/<?= htmlspecialchars($b['image_buvette']) ?>" class="w-100 h-100 object-fit-cover" alt="Logo">
                            <?php else: ?>
                                <span class="text-muted fw-bold">IMG</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12 col-md-5 mb-3 mb-md-0">
                        <div class="d-flex flex-wrap align-items-center mb-1 gap-2">
                            <h3 class="fs-4 fw-bold mb-0 font-serif"><?= htmlspecialchars($b['nom']) ?></h3>

                            <?php if($estActuelle): ?>
                                <span class="badge bg-success text-white rounded-pill small">
                                    <i class="bi bi-check-circle-fill me-1"></i>Active
                                </span>
                            <?php endif; ?>

                            <?php if($estStaff): ?>
                                <span class="badge bg-warning text-dark small rounded-pill shadow-sm">
                                    <i class="bi bi-person-badge-fill me-1"></i>VOTRE ÉQUIPE
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-2 text-truncate"><?= htmlspecialchars($b['description']) ?></p>

                        <span class="badge <?= $b['est_ouverte'] ? 'bg-success' : 'bg-danger' ?> rounded-pill small">
                            <?= $b['est_ouverte'] ? 'Ouverte' : 'Fermée' ?>
                        </span>

                        <?php if($estEnAttente): ?>
                            <span class="badge bg-warning text-dark rounded-pill small ms-2">
                                <i class="bi bi-hourglass-split"></i> Demande envoyée
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-4 text-md-end">
                        <div class="d-flex flex-column flex-md-row justify-content-end gap-2">

                            <?php if ($estStaff): ?>
                                <a href="index.php?module=serveur&id_buvette=<?= $id ?>"
                                   class="btn btn-warning rounded-pill px-3 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-clipboard-check me-1"></i>GESTION
                                </a>
                            <?php endif; ?>

                            <?php if ($estMembreSection || $estStaff): ?>
                                <?php if ($estActuelle): ?>
                                    <button class="btn btn-success bg-opacity-75 rounded-pill px-3 py-2 shadow-sm border-0 pe-none">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="index.php?module=menu&action=afficher&id_buvette=<?= $id ?>"
                                       class="btn btn-dark rounded-pill px-3 py-2 shadow-sm">
                                        <i class="bi bi-cup-straw me-1"></i>La Carte
                                    </a>
                                <?php endif; ?>

                            <?php elseif ($estEnAttente): ?>
                                <button class="btn btn-secondary rounded-pill px-4 py-2 shadow-sm opacity-50" disabled>
                                    <i class="bi bi-clock me-2"></i>En attente...
                                </button>

                            <?php else: ?>
                                <?php if ($b['est_ouverte']): ?>
                                    <a href="index.php?module=buvettes&action=adherer&id_buvette=<?= $id ?>"
                                       class="btn btn-outline-dark rounded-pill px-4 py-2 shadow-sm"
                                       onclick="return confirm('Rejoindre la file d\'attente de cette buvette ?');">
                                        <i class="bi bi-plus-circle me-2"></i>Adhérer
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-light text-muted border rounded-pill px-4 py-2" disabled>
                                        Fermée
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
    }
}
?>