<?php
class VueServeur {
    public function afficherDashboard($reservations) {
        // RÈGLE DE TRI : Statut (Ordre logique) puis Heure (Ancienne en haut)
        usort($reservations, function($a, $b) {
            $ordre = ['Réservé' => 1, 'Préparation' => 2, 'Arrivé' => 3, 'Parti' => 4, 'Annulé' => 5];
            $sA = $ordre[$a['statut']] ?? 9;
            $sB = $ordre[$b['statut']] ?? 9;
            if ($sA === $sB) {
                return strtotime($a['date_commande']) - strtotime($b['date_commande']);
            }
            return $sA - $sB;
        });
        ?>
        <style>
            .hidden-row { display: none !important; }
            .font-serif { font-family: 'Georgia', serif; }
            .option-text { color: red; font-weight: bold; font-size: 0.9em; }
            .status-btn { cursor: pointer; transition: 0.2s; }
            .status-btn:hover { opacity: 0.8; transform: scale(1.05); }
        </style>

        <div class="container-fluid mt-5 pt-5 fade-in px-5">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="index.php?module=buvettes" class="btn btn-outline-dark rounded-pill px-4 shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Buvettes
                </a>
                <h1 class="font-handwritten display-4 mb-0 text-center">Interface de Service</h1>

                <!-- FILTRES PAR BOUTONS -->
                <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                    <button class="btn btn-white fw-bold border-end" onclick="filterServeur('all')">TOUT</button>
                    <button class="btn btn-warning fw-bold border-end" onclick="filterServeur('Réservé')">À VENIR</button>
                    <button class="btn btn-info text-white fw-bold border-end" onclick="filterServeur('Préparation')">EN PRÉPA</button>
                    <button class="btn btn-success fw-bold" onclick="filterServeur('Arrivé')">PRÊT / SERVI</button>
                </div>
            </div>

            <?php if (empty($reservations)): ?>
                <div class="text-center p-5 bg-light rounded-5"><h3 class="text-muted">Aucune commande</h3></div>
            <?php else: ?>
                <div class="table-responsive bg-white p-4 rounded-5 shadow">
                    <table class="table table-hover align-middle text-center">
                        <thead>
                        <tr class="text-uppercase small text-muted border-bottom">
                            <th class="text-start">Client</th>
                            <th>Heure</th>
                            <th>Arrivée</th>
                            <th>État (Cycle)</th>
                            <th>Détails</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($reservations as $resa):
                            $statut = $resa['statut'];
                            $isFinished = ($statut === 'Parti' || $statut === 'Annulé');
                            ?>
                            <tr class="reservation-row" data-statut="<?= $statut ?>">
                                <td class="text-start fw-bold fs-5 py-4"><?= htmlspecialchars($resa['prenom'] . ' ' . $resa['nom']) ?></td>
                                <td class="font-monospace fs-5"><?= date('H\hi', strtotime($resa['date_commande'])) ?></td>
                                <td class="font-monospace fw-bold text-success fs-5"><?= !empty($resa['heure_arrivee']) ? date('H\hi', strtotime($resa['heure_arrivee'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $cls = 'bg-secondary';
                                    if($statut === 'Réservé') $cls = 'bg-warning text-dark';
                                    elseif($statut === 'Préparation') $cls = 'bg-info text-white';
                                    elseif($statut === 'Arrivé') $cls = 'bg-success';
                                    ?>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <?php if($statut !== 'Réservé'): ?>
                                            <a href="index.php?module=serveur&action=revert_statut&id=<?= $resa['id_commande'] ?>&actuel=<?= $statut ?>" class="text-muted me-2"><i class="bi bi-arrow-left-circle"></i></a>
                                        <?php endif; ?>
                                        <a href="index.php?module=serveur&action=cycle_statut&id=<?= $resa['id_commande'] ?>&actuel=<?= $statut ?>" class="text-decoration-none">
                                            <span class="badge <?= $cls ?> fs-6 rounded-pill px-3 py-2 status-btn"><?= ucfirst($statut) ?></span>
                                        </a>
                                    </div>
                                </td>
                                <td><button class="btn btn-light rounded-circle" data-bs-toggle="modal" data-bs-target="#modal<?= $resa['id_commande'] ?>"><i class="bi bi-eye"></i></button></td>
                                <td>
                                    <?php if(!$isFinished): ?>
                                        <a href="index.php?module=serveur&action=annuler&id=<?= $resa['id_commande'] ?>" class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('Annuler ?')">Annuler</a>
                                    <?php else: ?>
                                        <i class="bi bi-lock text-muted"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <!-- MODALE ICI (reprends ton code de modale précédent) -->
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <script>
            function filterServeur(statut) {
                document.querySelectorAll('.reservation-row').forEach(r => {
                    r.style.display = (statut === 'all' || r.getAttribute('data-statut') === statut) ? "" : "none";
                });
            }
        </script>
        <?php
    }
}