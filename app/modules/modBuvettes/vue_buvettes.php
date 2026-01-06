<?php
class VueBuvettes {
    public function afficherBuvettesGroupées($groupes) {
        ?>
        <div class="container mt-5 pt-5">
            <h1 class="font-handwritten text-center mb-5 display-3">Nos Buvettes</h1>

            <!-- Barre de Recherche -->
            <div class="mb-5 px-md-5">
                <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white p-1 border">
                    <span class="input-group-text bg-white border-0 ps-4"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchBuvette" class="form-control border-0 fs-5" placeholder="Trouver une buvette par son nom..." onkeyup="filtrerBuvettes()">
                </div>
            </div>

            <div class="accordion border-0" id="buvetteAccordion">
                <!-- ACCORDÉON 1 : MES ACCÈS (Ouvert par défaut) -->
                <div class="accordion-item border-0 mb-4 shadow-sm rounded-5 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button bg-custom-dark text-white fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStaff">
                            <i class="bi bi-shield-lock-fill me-3 text-warning"></i> MES ACCÈS SERVEUR (<?= count($groupes['autorisees']) ?>)
                        </button>
                    </h2>
                    <div id="collapseStaff" class="accordion-collapse collapse show" data-bs-parent="#buvetteAccordion">
                        <div class="accordion-body bg-light p-4">
                            <?php $this->renderListe($groupes['autorisees'], true); ?>
                        </div>
                    </div>
                </div>

                <!-- ACCORDÉON 2 : AUTRES (Caché par défaut) -->
                <div class="accordion-item border-0 shadow-sm rounded-5 overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-secondary text-white fw-bold py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClients">
                            <i class="bi bi-geo-alt-fill me-3"></i> AUTRES ÉTABLISSEMENTS
                        </button>
                    </h2>
                    <div id="collapseClients" class="accordion-collapse collapse" data-bs-parent="#buvetteAccordion">
                        <div class="accordion-body bg-light p-4">
                            <?php $this->renderListe($groupes['autres'], false); ?>
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
    private function renderListe($buvettes, $estStaff) {
        if (empty($buvettes)) {
            echo "<div class='text-center py-4'><i class='bi bi-info-circle me-2'></i>Aucune buvette dans cette catégorie.</div>";
            return;
        }

        foreach ($buvettes as $b) { ?>
            <div class="buvette-card mb-3" data-name="<?= htmlspecialchars($b['nom']) ?>">
                <div class="row align-items-center bg-white p-3 rounded-4 border-start border-5 <?= $estStaff ? 'border-warning' : 'border-secondary' ?> shadow-sm mx-0">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center">
                            <h3 class="fs-4 fw-bold mb-0 font-serif"><?= htmlspecialchars($b['nom']) ?></h3>
                            <?php if($estStaff): ?>
                                <span class="ms-2 badge bg-warning text-dark small rounded-pill shadow-sm">
                                    <i class="bi bi-person-badge-fill me-1"></i>VOTRE ÉQUIPE
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="badge <?= $b['est_ouverte'] ? 'bg-success' : 'bg-danger' ?> rounded-pill small mt-1">
                            <?= $b['est_ouverte'] ? 'Ouverte' : 'Fermée' ?>
                        </span>
                    </div>
                    <div class="col-md-5 text-end">
                        <div class="btn-group">
                            <a href="index.php?module=menu&action=afficher&id_buvette=<?= $b['id_buvette'] ?>"
                               class="btn btn-sm btn-outline-dark rounded-pill px-3 shadow-sm">
                                <i class="bi bi-cup-straw me-1"></i>La Carte
                            </a>

                            <?php if($estStaff): ?>
                                <a href="index.php?module=serveur&id_buvette=<?= $b['id_buvette'] ?>"
                                   class="btn btn-sm btn-warning rounded-pill px-3 ms-2 fw-bold shadow-sm">
                                    <i class="bi bi-clipboard-check me-1"></i>GESTION
                                </a>
                            <?php else: ?>
                                <!-- Bouton pour devenir serveur de cette buvette -->
                                <a href="index.php?module=buvettes&action=rejoindre&id_buvette=<?= $b['id_buvette'] ?>"
                                   class="btn btn-sm bg-custom-dark text-white rounded-pill px-3 ms-2 shadow-sm"
                                   onclick="return confirm('Voulez-vous rejoindre l\'équipe de cette buvette ?');">
                                    <i class="bi bi-plus-circle me-1"></i>Rejoindre l'équipe
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
    }
}
