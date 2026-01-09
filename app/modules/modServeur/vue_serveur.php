<?php
class VueServeur {
    public function afficherDashboard($reservations, $produits) {
        ?>
        <div class="container-fluid mt-5 pt-5 px-5">
            <div class="row">
                <!-- ========================
                     COLONNE GAUCHE : VENTE
                     ======================== -->
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

                <!-- ========================
                     COLONNE DROITE : SUIVI
                     ======================== -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <h2 class="font-handwritten mb-0">Suivi des Commandes</h2>

                        <!-- Filtres -->
                        <div class="btn-group shadow-sm rounded-pill" role="group">
                            <button class="btn btn-outline-dark px-3 active" onclick="filterServeur('actif', this)">En cours</button>
                            <button class="btn btn-outline-warning px-3" onclick="filterServeur('Préparation', this)">En Prépa</button>
                            <button class="btn btn-outline-primary px-3" onclick="filterServeur('Arrivé', this)">Prêts</button>
                            <button class="btn btn-outline-secondary px-3" onclick="filterServeur('Parti', this)">Historique</button>
                            <button class="btn btn-outline-danger px-3" onclick="filterServeur('Annulé', this)">Annulés</button>
                        </div>
                    </div>

                    <div class="row" id="commandes-container">
                        <?php if (empty($reservations)): ?>
                            <div class="col-12 text-center text-muted py-5">Aucune commande récente.</div>
                        <?php else: ?>
                            <?php foreach ($reservations as $resa): ?>
                                <div class="col-md-6 mb-4 commande-item transition-all" data-statut="<?= htmlspecialchars($resa['statut']) ?>">
                                    <div class="card shadow-sm border-0 h-100 rounded-4">
                                        <div class="card-header bg-white border-bottom-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-truncate" style="max-width: 150px;">
                                                <i class="bi bi-person-circle me-1 text-muted"></i>
                                                <?= htmlspecialchars($resa['nom'] . ' ' . $resa['prenom']) ?>
                                            </span>
                                            <span class="badge rounded-pill px-3 py-2
                                                <?= $resa['statut'] == 'Réservé' ? 'bg-secondary' :
                                                    ($resa['statut'] == 'Préparation' ? 'bg-warning text-dark' :
                                                            ($resa['statut'] == 'Arrivé' ? 'bg-primary' :
                                                                    ($resa['statut'] == 'Annulé' ? 'bg-danger' : 'bg-success'))) ?>">
                                                <?= htmlspecialchars($resa['statut']) ?>
                                            </span>
                                        </div>
                                        <div class="card-body px-3 py-2">
                                            <div class="small text-muted mb-2"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($resa['date_commande'])) ?></div>
                                            <ul class="list-unstyled mb-3 bg-light p-2 rounded-3 small">
                                                <?php foreach ($resa['details'] as $detail): ?>
                                                    <li class="d-flex justify-content-between py-1 border-bottom border-white">
                                                        <span><?= $detail['quantite'] ?>x <?= htmlspecialchars($detail['nom_produit']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="fw-bold text-end text-primary mb-3">Total : <?= $resa['prix_total'] ?> €</div>

                                            <!-- Actions -->
                                            <div class="d-flex justify-content-between align-items-center mt-auto pb-2 border-top pt-2">
                                                <?php if ($resa['statut'] !== 'Annulé' && $resa['statut'] !== 'Parti'): ?>

                                                    <!-- Précédent -->
                                                    <?php if ($resa['statut'] !== 'Réservé'): ?>
                                                        <a href="index.php?module=serveur&action=revert_statut&id=<?= $resa['id_commande'] ?>&actuel=<?= $resa['statut'] ?>"
                                                           class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0; line-height: 30px;">
                                                            <i class="bi bi-arrow-left"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <div style="width: 32px;"></div>
                                                    <?php endif; ?>

                                                    <!-- Annuler -->
                                                    <a href="index.php?module=serveur&action=annuler&id=<?= $resa['id_commande'] ?>"
                                                       class="btn btn-link text-danger btn-sm text-decoration-none" onclick="return confirm('Annuler cette commande ?')">
                                                        <i class="bi bi-trash"></i> Annuler
                                                    </a>

                                                    <!-- Suivant -->
                                                    <a href="index.php?module=serveur&action=cycle_statut&id=<?= $resa['id_commande'] ?>&actuel=<?= $resa['statut'] ?>"
                                                       class="btn btn-success btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0; line-height: 30px;">
                                                        <i class="bi bi-arrow-right"></i>
                                                    </a>

                                                <?php else: ?>
                                                    <div class="w-100 text-center text-muted small fst-italic py-1">
                                                        <i class="bi bi-lock-fill me-1"></i>Archivée
                                                    </div>
                                                <?php endif; ?>
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

        <!-- SCRIPTS JS POUR VENTE ET FILTRES -->
        <script>
            // --- FILTRES ---
            function filterServeur(statut, btnElement) {
                // Gestion boutons actifs
                if(btnElement) {
                    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active', 'btn-dark', 'btn-warning', 'btn-primary', 'btn-secondary', 'btn-danger'));
                    document.querySelectorAll('.btn-group .btn').forEach(btn => {
                        // Remet les classes outline par défaut pour les inactifs
                        if(btn.classList.contains('active')) return;
                        // (Simplification visuelle, on laisse juste active/inactive generic)
                        btn.classList.remove('active');
                    });
                    btnElement.classList.add('active');
                }

                const items = document.querySelectorAll('.commande-item');

                items.forEach(item => {
                    const itemStatut = item.getAttribute('data-statut');

                    if (statut === 'actif') {
                        // Affiche tout SAUF Annulé et Parti
                        if (itemStatut !== 'Annulé' && itemStatut !== 'Parti') {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    } else {
                        // Filtre strict (pour Parti, Annulé, Préparation, Arrivé)
                        if (itemStatut === statut) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });
            }

            // Initialisation filtre
            document.addEventListener('DOMContentLoaded', () => {
                const firstBtn = document.querySelector('.btn-group .btn');
                if(firstBtn) filterServeur('actif', firstBtn);
            });

            // --- VENTE AU COMPTOIR ---
            let panier = [];
            let total = 0;
            let clientSelectionne = null;

            function rechercherClient(query) {
                if (query.length < 2) {
                    document.getElementById('resultatsRecherche').style.display = 'none';
                    return;
                }

                fetch(`index.php?module=serveur&action=rechercher_user&query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (data.length > 0) {
                            data.forEach(user => {
                                html += `<button class="list-group-item list-group-item-action" onclick='selectionnerClient(${JSON.stringify(user)})'>
                                        <div class="d-flex justify-content-between">
                                            <strong>${user.nom} ${user.prenom}</strong>
                                            <span class="badge bg-light text-dark border">${user.solde} €</span>
                                        </div>
                                     </button>`;
                            });
                            document.getElementById('resultatsRecherche').innerHTML = html;
                            document.getElementById('resultatsRecherche').style.display = 'block';
                        } else {
                            document.getElementById('resultatsRecherche').style.display = 'none';
                        }
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
                if (item) {
                    item.quantite++;
                } else {
                    panier.push({id: id, nom: nom, prix: prix, quantite: 1});
                }
                updatePanierDisplay();
            }

            function updatePanierDisplay() {
                let html = '';
                total = 0;
                if (panier.length === 0) {
                    html = '<div class="text-muted text-center py-2 fst-italic">Le panier est vide</div>';
                } else {
                    panier.forEach((item, index) => {
                        total += item.prix * item.quantite;
                        html += `<div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                                <div>${item.quantite}x ${item.nom}</div>
                                <div class="d-flex align-items-center">
                                    <span class="me-2 fw-bold">${item.prix * item.quantite} €</span>
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="retirerDuPanier(${index})">&times;</button>
                                </div>
                             </div>`;
                    });
                }
                document.getElementById('panierContent').innerHTML = html;
                document.getElementById('totalVente').innerText = total;
                checkValidation();
            }

            function retirerDuPanier(index) {
                panier.splice(index, 1);
                updatePanierDisplay();
            }

            function checkValidation() {
                const btn = document.getElementById('btnValiderVente');
                if (clientSelectionne && panier.length > 0 && clientSelectionne.solde >= total) {
                    btn.classList.remove('disabled');
                } else {
                    btn.classList.add('disabled');
                }
            }

            function validerVente() {
                if (!clientSelectionne || panier.length === 0) return;

                fetch('index.php?module=serveur&action=valider_vente', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        user_id: clientSelectionne.id_utilisateur,
                        panier: panier,
                        total: total
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Vente réussie !');
                            // Reset total
                            panier = [];
                            updatePanierDisplay();
                            resetClient();
                            // Recharger la page pour voir la nouvelle commande dans l'historique
                            window.location.reload();
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