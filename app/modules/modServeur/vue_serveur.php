<?php
class VueServeur {
    public function afficherDashboard($reservations, $produits) {
        ?>
        <!-- Notification Toast (Cachée par défaut) -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
            <div id="liveToast" class="toast align-items-center text-bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fs-6 fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i> Vente enregistrée avec succès !
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-5 pt-5 px-5">
            <div class="row">
                <!-- COLONNE GAUCHE : VENTE -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-5 mb-4 bg-light position-sticky" style="top: 100px;">
                        <div class="card-body p-4">
                            <h3 class="font-handwritten mb-4 text-center">Vente au Comptoir</h3>

                            <!-- Recherche Client -->
                            <div class="mb-4">
                                <label class="small fw-bold text-muted mb-2">1. CLIENT</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-person-search"></i></span>
                                    <input type="text" id="searchClient" class="form-control border-start-0" placeholder="Chercher un nom..." onkeyup="rechercherClient(this.value)" autocomplete="off">
                                </div>
                                <div id="resultatsRecherche" class="list-group mt-2 shadow-sm position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto; display:none;"></div>

                                <div id="clientSelectionne" class="mt-2 p-3 bg-white rounded-3 border d-none shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span id="nomClient" class="fw-bold"></span>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger py-0" onclick="resetClient()">X</button>
                                    </div>
                                    <div class="mt-2 text-muted small">Solde : <span id="soldeClient" class="fw-bold text-dark fs-6"></span> €</div>
                                    <input type="hidden" id="idClientVente">
                                </div>
                            </div>

                            <!-- Sélection Produits -->
                            <div class="mb-4">
                                <label class="small fw-bold text-muted mb-2">2. PRODUITS</label>
                                <div class="row g-2 overflow-auto custom-scrollbar" style="max-height: 300px;">
                                    <?php foreach ($produits as $p): ?>
                                        <div class="col-6">
                                            <button class="btn btn-white border w-100 text-start p-2 rounded-3 small h-100 shadow-sm-hover transition-all"
                                                    onclick="ajouterAuPanier(<?= $p['id_produit'] ?>, '<?= addslashes($p['nom_produit']) ?>', <?= $p['prix_produit'] ?>)">
                                                <div class="fw-bold text-truncate"><?= htmlspecialchars($p['nom_produit']) ?></div>
                                                <div class="text-success fw-bold"><?= $p['prix_produit'] ?> €</div>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Panier -->
                            <div class="border-top pt-3">
                                <div id="panierContent" class="mb-3 small overflow-auto" style="max-height: 150px;">
                                    <div class="text-muted text-center py-2 fst-italic">Le panier est vide</div>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-4 mb-3">
                                    <span>TOTAL</span>
                                    <span><span id="totalVente">0</span> €</span>
                                </div>
                                <button id="btnValiderVente" class="btn btn-warning w-100 rounded-pill fw-bold py-3 shadow-sm disabled" onclick="validerVente()">
                                    <i class="bi bi-cash-coin me-2"></i>ENCAISSER
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLONNE DROITE : SUIVI -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="font-handwritten mb-0">Suivi des Commandes</h2>
                        <div class="input-group w-auto shadow-sm rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search"></i></span>
                            <input type="text" id="filterInput" class="form-control border-0" placeholder="Chercher une commande..." onkeyup="updateFilters()">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-4 overflow-auto pb-2">
                        <button class="btn btn-outline-dark rounded-pill px-3 active filter-btn" onclick="setStatutFilter('actif', this)">En cours</button>
                        <button class="btn btn-outline-info rounded-pill px-3 filter-btn" onclick="setStatutFilter('Payé', this)">En Attente</button>
                        <button class="btn btn-outline-warning rounded-pill px-3 filter-btn" onclick="setStatutFilter('Préparation', this)">En Prépa</button>
                        <button class="btn btn-outline-success rounded-pill px-3 filter-btn" onclick="setStatutFilter('Arrivé', this)">Prêts</button>
                        <div class="vr mx-1"></div>
                        <button class="btn btn-outline-secondary rounded-pill px-3 filter-btn" onclick="setStatutFilter('Parti', this)">Historique</button>
                        <button class="btn btn-outline-danger rounded-pill px-3 filter-btn" onclick="setStatutFilter('Annulé', this)">Annulés</button>
                    </div>

                    <div class="row" id="commandes-container">
                        <?php if (empty($reservations)): ?>
                            <div class="col-12 text-center text-muted py-5">Aucune commande pour cette buvette.</div>
                        <?php else: ?>
                            <?php foreach ($reservations as $resa):
                                $s = $resa['statut'];
                                $border = 'border-secondary'; $bg = 'bg-light'; $badge = 'bg-secondary';
                                if($s == 'Payé' || $s == 'Réservé') { $border = 'border-info'; $bg = 'bg-info-subtle'; $badge = 'bg-info text-dark'; }
                                elseif ($s == 'Préparation') { $border = 'border-warning'; $bg = 'bg-warning-subtle'; $badge = 'bg-warning text-dark'; }
                                elseif ($s == 'Arrivé') { $border = 'border-success'; $bg = 'bg-success-subtle'; $badge = 'bg-success'; }
                                elseif ($s == 'Annulé') { $border = 'border-danger'; $bg = 'bg-danger-subtle'; $badge = 'bg-danger'; }
                                ?>
                                <div class="col-md-6 mb-4 commande-item transition-all"
                                     data-statut="<?= htmlspecialchars($resa['statut']) ?>"
                                     data-client="<?= htmlspecialchars(strtolower($resa['nom'] . ' ' . $resa['prenom'])) ?>">

                                    <div class="card shadow-sm h-100 rounded-4 border-2 <?= $border ?>">
                                        <div class="card-header border-bottom-0 pt-3 px-3 d-flex justify-content-between align-items-center <?= $bg ?> rounded-top-4">
                                            <span class="fw-bold text-truncate" style="max-width: 150px;">
                                                <i class="bi bi-person-circle me-1 opacity-50"></i>
                                                <?= htmlspecialchars($resa['nom'] . ' ' . $resa['prenom']) ?>
                                            </span>
                                            <span class="badge rounded-pill px-3 py-2 <?= $badge ?>">
                                                <?= htmlspecialchars($resa['statut']) ?>
                                            </span>
                                        </div>
                                        <div class="card-body px-3 py-2 d-flex flex-column">
                                            <div class="small text-muted mb-2"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($resa['date_commande'])) ?></div>
                                            <ul class="list-unstyled mb-3 bg-light p-2 rounded-3 small border">
                                                <?php foreach ($resa['details'] as $detail): ?>
                                                    <li class="d-flex justify-content-between py-1 border-bottom border-white">
                                                        <span><?= $detail['quantite'] ?>x <?= htmlspecialchars($detail['nom_produit']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="fw-bold text-end mb-3">Total : <?= $resa['prix_total'] ?> €</div>

                                            <div class="mt-auto border-top pt-3">
                                                <form action="index.php?module=serveur&action=changer_statut" method="POST">
                                                    <input type="hidden" name="id_commande" value="<?= $resa['id_commande'] ?>">
                                                    <div class="input-group input-group-sm">
                                                        <label class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-sliders2"></i></label>
                                                        <select name="nouveau_statut" class="form-select form-select-sm border-start-0 fw-semibold cursor-pointer shadow-none" onchange="this.form.submit()" style="background-color: transparent;">
                                                            <option value="Payé" class="text-info fw-bold" <?= ($s == 'Payé' || $s == 'Réservé') ? 'selected' : '' ?>>🔵 En attente</option>
                                                            <option value="Préparation" class="text-warning fw-bold" <?= ($s == 'Préparation') ? 'selected' : '' ?>>🟡 En cuisine</option>
                                                            <option value="Arrivé" class="text-success fw-bold" <?= ($s == 'Arrivé') ? 'selected' : '' ?>>🟢 Prêt à servir</option>
                                                            <option disabled>──────────</option>
                                                            <option value="Parti" class="text-secondary" <?= ($s == 'Parti') ? 'selected' : '' ?>>🏁 Terminé (Parti)</option>
                                                            <option value="Annulé" class="text-danger" <?= ($s == 'Annulé') ? 'selected' : '' ?>>❌ Annulé</option>
                                                        </select>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // --- LOGIQUE FILTRES ET VENTE ---
            // (Le code précédent des filtres reste identique ici, je ne le répète pas pour abréger, mais il faut le garder)
            let currentFilter = 'actif';
            let currentSearch = '';
            function setStatutFilter(statut, btn) { document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active')); btn.classList.add('active'); currentFilter = statut; applyFilters(); }
            function updateFilters() { currentSearch = document.getElementById('filterInput').value.toLowerCase(); applyFilters(); }
            function applyFilters() {
                const items = document.querySelectorAll('.commande-item');
                items.forEach(item => {
                    const statut = item.getAttribute('data-statut');
                    const client = item.getAttribute('data-client');
                    let showStatut = false;
                    if (currentFilter === 'actif') showStatut = (statut !== 'Annulé' && statut !== 'Parti');
                    else if (currentFilter === 'Payé') showStatut = (statut === 'Payé' || statut === 'Réservé');
                    else showStatut = (statut === currentFilter);
                    let showSearch = client.includes(currentSearch);
                    item.style.display = (showStatut && showSearch) ? 'block' : 'none';
                });
            }
            document.addEventListener('DOMContentLoaded', () => { applyFilters(); });

            let panier = [];
            let total = 0;
            let clientSelectionne = null;

            function rechercherClient(query) {
                if (query.length < 2) { document.getElementById('resultatsRecherche').style.display = 'none'; return; }
                fetch(`index.php?module=serveur&action=rechercher_user&query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (data.length > 0) {
                            data.forEach(user => {
                                html += `<button class="list-group-item list-group-item-action" onclick='selectionnerClient(${JSON.stringify(user)})'>
                                        <div class="d-flex justify-content-between"><strong>${user.nom} ${user.prenom}</strong><span class="badge bg-light text-dark border">${user.solde} €</span></div>
                                     </button>`;
                            });
                            document.getElementById('resultatsRecherche').innerHTML = html;
                            document.getElementById('resultatsRecherche').style.display = 'block';
                        } else { document.getElementById('resultatsRecherche').style.display = 'none'; }
                    });
            }

            function selectionnerClient(user) {
                clientSelectionne = user;
                document.getElementById('idClientVente').value = user.id_utilisateur;
                document.getElementById('nomClient').innerText = `${user.nom} ${user.prenom}`;
                document.getElementById('soldeClient').innerText = user.solde;
                document.getElementById('searchClient').value = '';
                document.getElementById('resultatsRecherche').style.display = 'none';
                document.getElementById('clientSelectionne').classList.remove('d-none');
                checkValidation();
            }

            function resetClient() {
                clientSelectionne = null;
                document.getElementById('clientSelectionne').classList.add('d-none');
                checkValidation();
            }

            function ajouterAuPanier(id, nom, prix) {
                let item = panier.find(p => p.id === id);
                if (item) item.quantite++; else panier.push({id: id, nom: nom, prix: prix, quantite: 1});
                updatePanierDisplay();
            }

            function updatePanierDisplay() {
                let html = ''; total = 0;
                if (panier.length === 0) html = '<div class="text-muted text-center py-2 fst-italic">Le panier est vide</div>';
                else {
                    panier.forEach((item, index) => {
                        total += item.prix * item.quantite;
                        html += `<div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1"><div>${item.quantite}x ${item.nom}</div><div class="d-flex align-items-center"><span class="me-2 fw-bold">${item.prix * item.quantite} €</span><button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="retirerDuPanier(${index})">&times;</button></div></div>`;
                    });
                }
                document.getElementById('panierContent').innerHTML = html;
                document.getElementById('totalVente').innerText = total;
                checkValidation();
            }

            function retirerDuPanier(index) { panier.splice(index, 1); updatePanierDisplay(); }

            function checkValidation() {
                const btn = document.getElementById('btnValiderVente');
                if (clientSelectionne && panier.length > 0 && clientSelectionne.solde >= total) btn.classList.remove('disabled');
                else btn.classList.add('disabled');
            }

            function validerVente() {
                if (!clientSelectionne || panier.length === 0) return;
                fetch('index.php?module=serveur&action=valider_vente', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ user_id: clientSelectionne.id_utilisateur, panier: panier, total: total })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Affiche le Toast au lieu de l'alerte
                            const toastLiveExample = document.getElementById('liveToast');
                            const toast = new bootstrap.Toast(toastLiveExample);
                            toast.show();

                            // Réinitialisation interface
                            panier = [];
                            updatePanierDisplay();
                            resetClient();

                            // Rechargement après 1.5 secondes
                            setTimeout(() => { window.location.reload(); }, 1500);
                        } else {
                            alert('Erreur : ' + data.message);
                        }
                    });
            }
        </script>
        <?php
    }
}
?>