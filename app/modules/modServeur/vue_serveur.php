<?php
class VueServeur {
    public function afficherDashboard($reservations, $produits) {
        ?>
        <style>
            :root { --bg-app: #f4f6f9; }
            body { background-color: var(--bg-app); }

            /* Effet de carte moderne */
            .card-modern {
                background: white;
                border: none;
                border-radius: 1.5rem;
                box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                transition: transform 0.2s ease;
            }

            /* Grille produits */
            .product-btn {
                transition: all 0.2s;
                border: 1px solid #eee;
            }
            .product-btn:hover {
                transform: translateY(-3px);
                border-color: var(--bs-primary);
                background-color: #f8f9fa;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            }
            .product-btn:active { transform: scale(0.98); }
            .product-thumb { height: 60px; object-fit: contain; }

            /* Scrollbar invisible */
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #dee2e6; border-radius: 10px; }

            /* Animation */
            .fade-in-up { animation: fadeInUp 0.4s ease-out forwards; opacity: 0; transform: translateY(20px); }
            @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

            /* Status border colors */
            .status-border-Payé, .status-border-Réservé { border-left: 5px solid #0dcaf0 !important; }
            .status-border-Préparation { border-left: 5px solid #ffc107 !important; }
            .status-border-Arrivé { border-left: 5px solid #198754 !important; }
            .status-border-Annulé { border-left: 5px solid #dc3545 !important; }
            .status-border-Parti { border-left: 5px solid #6c757d !important; }
        </style>

        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
            <div id="liveToast" class="toast align-items-center text-bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fs-6 fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i> Vente enregistrée !
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <div class="container-fluid px-md-5 mt-5 pt-5">
            <div class="row g-4">

                <div class="col-lg-4 d-flex flex-column" style="height: calc(100vh - 120px);"> <div class="card card-modern h-100 d-flex flex-column overflow-hidden position-sticky" style="top: 100px;">

                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h4 class="fw-bold text-primary mb-3"><i class="bi bi-shop me-2"></i>Caisse Buvette</h4>

                            <div class="position-relative mb-3">
                                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                                    <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-person-search"></i></span>
                                    <input type="text" id="searchClient" class="form-control border-0 fs-6" placeholder="Rechercher un client..." onkeyup="rechercherClient(this.value)" autocomplete="off">
                                </div>
                                <div id="resultatsRecherche" class="list-group position-absolute w-100 mt-2 shadow rounded-3 border-0" style="z-index: 1050; display:none; max-height: 250px; overflow-y: auto;"></div>
                            </div>

                            <div id="clientSelectionne" class="alert alert-success d-none d-flex justify-content-between align-items-center shadow-sm border-0 rounded-3 mb-2 animate__animated animate__fadeIn">
                                <div>
                                    <div class="small text-uppercase fw-bold text-success opacity-75">Client identifié</div>
                                    <div class="fw-bold fs-5 text-dark"><i class="bi bi-person-check-fill me-2"></i><span id="nomClient"></span></div>
                                    <div class="small">Solde : <span id="soldeClient" class="badge bg-white text-success border border-success"></span> €</div>
                                </div>
                                <button class="btn btn-sm btn-close" onclick="resetClient()"></button>
                                <input type="hidden" id="idClientVente">
                            </div>
                        </div>

                        <div class="card-body px-4 py-2 overflow-auto custom-scrollbar flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-bold text-uppercase">Produits</span>
                            </div>
                            <div class="row g-2">
                                <?php foreach ($produits as $p): ?>
                                    <div class="col-6 col-md-4 col-lg-6 col-xl-4">
                                        <div class="product-btn p-3 rounded-3 h-100 cursor-pointer d-flex flex-column justify-content-between text-center"
                                             role="button"
                                             onclick="ajouterAuPanier(<?= $p['id_produit'] ?>, '<?= addslashes($p['nom_produit']) ?>', <?= $p['prix_produit'] ?>)">
                                            <div class="mb-2">
                                                <?php if (!empty($p['image_produit'])): ?>
                                                    <img src="public/img/produits/<?= htmlspecialchars($p['image_produit']) ?>"
                                                         class="product-thumb w-100"
                                                         alt="<?= htmlspecialchars($p['nom_produit']) ?>">
                                                <?php else: ?>
                                                    <i class="bi bi-cup-hot fs-3 text-secondary opacity-50"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="fw-bold text-dark lh-sm mb-1 small"><?= htmlspecialchars($p['nom_produit']) ?></div>
                                            <div class="badge bg-primary-subtle text-primary rounded-pill"><?= number_format($p['prix_produit'], 2) ?> €</div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top p-4 shadow-lg" style="z-index: 10;">
                            <div id="panierContent" class="mb-3 custom-scrollbar" style="max-height: 120px; overflow-y: auto;">
                                <div class="text-center text-muted py-3 opacity-50">
                                    <i class="bi bi-basket3 fs-1 d-block mb-1"></i>
                                    <small>Votre panier est vide</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-end mb-3">
                                <div class="text-muted small">Total à payer</div>
                                <div class="fs-2 fw-bold text-dark"><span id="totalVente">0.00</span> <small class="fs-5 text-muted">€</small></div>
                            </div>

                            <button id="btnValiderVente" class="btn btn-dark w-100 py-3 rounded-4 fw-bold fs-5 shadow-sm disabled transition-all" onclick="validerVente()">
                                <i class="bi bi-credit-card me-2"></i> Encaisser
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                        <h2 class="fw-bold mb-0">Suivi Commandes</h2>

                        <div class="d-flex gap-2 bg-white p-1 rounded-pill shadow-sm overflow-auto" style="max-width: 100%;">
                            <button class="btn btn-sm rounded-pill px-3 fw-bold active filter-btn btn-dark" onclick="setStatutFilter('actif', this)">En cours</button>
                            <button class="btn btn-sm rounded-pill px-3 fw-bold filter-btn btn-light text-secondary" onclick="setStatutFilter('Payé', this)">En Attente</button>
                            <button class="btn btn-sm rounded-pill px-3 fw-bold filter-btn btn-light text-secondary" onclick="setStatutFilter('Préparation', this)">Cuisine</button>
                            <button class="btn btn-sm rounded-pill px-3 fw-bold filter-btn btn-light text-secondary" onclick="setStatutFilter('Arrivé', this)">Prêts</button>
                            <div class="vr my-1"></div>
                            <button class="btn btn-sm rounded-pill px-3 fw-bold filter-btn btn-light text-secondary" onclick="setStatutFilter('Parti', this)">Historique</button>
                        </div>

                        <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text bg-white border-0 ps-3 rounded-start-pill"><i class="bi bi-search"></i></span>
                            <input type="text" id="filterInput" class="form-control border-0 rounded-end-pill shadow-sm" placeholder="Filtrer..." onkeyup="updateFilters()">
                        </div>
                    </div>

                    <div class="row g-3" id="commandes-container">
                        <?php if (empty($reservations)): ?>
                            <div class="col-12">
                                <div class="text-center py-5 text-muted">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-3 opacity-50" alt="Empty">
                                    <p class="fs-5">Aucune commande en cours.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reservations as $index => $resa):
                                $s = $resa['statut'];
                                $borderClass = 'status-border-' . str_replace(' ', '', $s);

                                $statusConfig = [
                                        'Payé' => ['icon' => 'bi-hourglass-split', 'color' => 'text-info', 'bg' => 'bg-info-subtle', 'label' => 'En attente'],
                                        'Réservé' => ['icon' => 'bi-hourglass-split', 'color' => 'text-info', 'bg' => 'bg-info-subtle', 'label' => 'Réservé'],
                                        'Préparation' => ['icon' => 'bi-fire', 'color' => 'text-warning', 'bg' => 'bg-warning-subtle', 'label' => 'En cuisine'],
                                        'Arrivé' => ['icon' => 'bi-check-circle', 'color' => 'text-success', 'bg' => 'bg-success-subtle', 'label' => 'Prêt à servir'],
                                        'Parti' => ['icon' => 'bi-flag', 'color' => 'text-secondary', 'bg' => 'bg-secondary-subtle', 'label' => 'Terminé'],
                                        'Annulé' => ['icon' => 'bi-x-circle', 'color' => 'text-danger', 'bg' => 'bg-danger-subtle', 'label' => 'Annulé'],
                                ];
                                $conf = $statusConfig[$s] ?? $statusConfig['Payé'];
                                ?>

                                <div class="col-md-6 col-xl-4 commande-item fade-in-up"
                                     style="animation-delay: <?= $index * 0.05 ?>s"
                                     data-statut="<?= htmlspecialchars($resa['statut']) ?>"
                                     data-client="<?= htmlspecialchars(strtolower($resa['nom'] . ' ' . $resa['prenom'])) ?>">

                                    <div class="card card-modern h-100 <?= $borderClass ?>">
                                        <div class="card-body p-3 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-truncate" style="max-width: 140px;">
                                                        <?= htmlspecialchars($resa['nom'] . ' ' . $resa['prenom']) ?>
                                                    </h6>
                                                    <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($resa['date_commande'])) ?></small>
                                                </div>
                                                <span class="badge rounded-pill <?= $conf['bg'] ?> <?= $conf['color'] ?> border border-opacity-10 px-2 py-1">
                                                    <i class="<?= $conf['icon'] ?> me-1"></i> <?= $conf['label'] ?>
                                                </span>
                                            </div>

                                            <div class="bg-light rounded-3 p-2 mb-3 small flex-grow-1 border border-light-subtle">
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($resa['details'] as $detail): ?>
                                                        <li class="d-flex justify-content-between mb-1">
                                                            <span class="fw-medium text-secondary"><?= $detail['quantite'] ?> &times;</span>
                                                            <span class="text-dark text-truncate w-75 text-end"><?= htmlspecialchars($detail['nom_produit']) ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top border-light">
                                                <span class="fw-bold fs-5"><?= number_format($resa['prix_total'], 2) ?> €</span>
                                                <form action="index.php?module=serveur&action=changer_statut" method="POST" class="w-50">
                                                    <input type="hidden" name="id_commande" value="<?= $resa['id_commande'] ?>">
                                                    <select name="nouveau_statut" class="form-select form-select-sm border-0 bg-light fw-bold shadow-none text-end cursor-pointer" onchange="this.form.submit()">
                                                        <option value="Payé" <?= ($s=='Payé')?'selected':'' ?>>Attente</option>
                                                        <option value="Préparation" class="text-warning" <?= ($s=='Préparation')?'selected':'' ?>>Cuisine</option>
                                                        <option value="Arrivé" class="text-success" <?= ($s=='Arrivé')?'selected':'' ?>>Prêt</option>
                                                        <option value="Parti" class="text-muted" <?= ($s=='Parti')?'selected':'' ?>>Terminé</option>
                                                        <option value="Annulé" class="text-danger" <?= ($s=='Annulé')?'selected':'' ?>>Annuler</option>
                                                    </select>
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
            // (Le script JS reste identique à la version précédente)
            let currentFilter = 'actif';
            let currentSearch = '';
            function setStatutFilter(statut, btn) {
                document.querySelectorAll('.filter-btn').forEach(b => { b.classList.remove('btn-dark', 'active'); b.classList.add('btn-light', 'text-secondary'); });
                btn.classList.remove('btn-light', 'text-secondary'); btn.classList.add('btn-dark', 'active');
                currentFilter = statut; applyFilters();
            }
            function updateFilters() { currentSearch = document.getElementById('filterInput').value.toLowerCase(); applyFilters(); }
            function applyFilters() {
                document.querySelectorAll('.commande-item').forEach(item => {
                    const statut = item.getAttribute('data-statut');
                    const client = item.getAttribute('data-client');
                    let showStatut = false;
                    if (currentFilter === 'actif') showStatut = (statut !== 'Annulé' && statut !== 'Parti');
                    else if (currentFilter === 'Payé') showStatut = (statut === 'Payé' || statut === 'Réservé');
                    else showStatut = (statut === currentFilter);
                    let showSearch = client.includes(currentSearch);

                    if (showStatut && showSearch) { item.classList.remove('d-none'); item.classList.add('animate__animated', 'animate__fadeIn'); }
                    else { item.classList.add('d-none'); }
                });
            }
            let panier = []; let total = 0; let clientSelectionne = null;
            function rechercherClient(query) {
                const resDiv = document.getElementById('resultatsRecherche');
                if (query.length < 2) { resDiv.style.display = 'none'; return; }
                fetch(`index.php?module=serveur&action=rechercher_user&query=${encodeURIComponent(query)}`).then(r => r.json()).then(data => {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(u => { html += `<button class="list-group-item list-group-item-action border-0 py-3 px-3 d-flex justify-content-between" onclick='selectionnerClient(${JSON.stringify(u)})'><div><div class="fw-bold">${u.nom} ${u.prenom}</div><small class="text-muted">Solde: ${u.solde}€</small></div><i class="bi bi-chevron-right text-muted"></i></button>`; });
                        resDiv.innerHTML = html; resDiv.style.display = 'block';
                    } else { resDiv.style.display = 'none'; }
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
            function resetClient() { clientSelectionne = null; document.getElementById('clientSelectionne').classList.add('d-none'); checkValidation(); }
            function ajouterAuPanier(id, nom, prix) {
                let item = panier.find(p => p.id === id); if (item) item.quantite++; else panier.push({id: id, nom: nom, prix: prix, quantite: 1});
                updatePanierDisplay();
            }
            function updatePanierDisplay() {
                const container = document.getElementById('panierContent'); let html = ''; total = 0;
                if (panier.length === 0) html = `<div class="text-center text-muted py-3 opacity-50"><i class="bi bi-basket3 fs-1 d-block mb-1"></i><small>Votre panier est vide</small></div>`;
                else {
                    panier.forEach((item, index) => {
                        total += item.prix * item.quantite;
                        html += `<div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded-3 bg-light border border-light-subtle animate__animated animate__pulse animate__faster"><div class="d-flex align-items-center gap-2"><span class="badge bg-white text-dark border shadow-sm">${item.quantite}x</span><span class="fw-medium small">${item.nom}</span></div><div class="d-flex align-items-center gap-2"><span class="fw-bold text-dark small">${(item.prix * item.quantite).toFixed(2)} €</span><button class="btn btn-sm btn-outline-danger border-0 p-1 rounded-circle" style="width:24px; height:24px; line-height:1;" onclick="retirerDuPanier(${index})">&times;</button></div></div>`;
                    });
                }
                container.innerHTML = html; document.getElementById('totalVente').innerText = total.toFixed(2); container.scrollTop = container.scrollHeight; checkValidation();
            }
            function retirerDuPanier(index) { panier.splice(index, 1); updatePanierDisplay(); }
            function checkValidation() {
                const btn = document.getElementById('btnValiderVente');
                if (clientSelectionne && panier.length > 0 && parseFloat(clientSelectionne.solde) >= total) { btn.classList.remove('disabled', 'btn-dark'); btn.classList.add('btn-success', 'shadow-lg'); btn.innerHTML = '<i class="bi bi-check-lg me-2"></i> Valider la vente'; }
                else { btn.classList.add('disabled', 'btn-dark'); btn.classList.remove('btn-success', 'shadow-lg'); if(clientSelectionne && parseFloat(clientSelectionne.solde) < total) btn.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i> Solde insuffisant'; else btn.innerHTML = '<i class="bi bi-credit-card me-2"></i> Encaisser'; }
            }
            function validerVente() {
                if (!clientSelectionne || panier.length === 0) return;
                document.getElementById('btnValiderVente').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Traitement...';
                fetch('index.php?module=serveur&action=valider_vente', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ user_id: clientSelectionne.id_utilisateur, panier: panier, total: total }) })
                    .then(r => r.json()).then(data => {
                    if (data.success) { const toast = new bootstrap.Toast(document.getElementById('liveToast')); toast.show(); panier = []; updatePanierDisplay(); resetClient(); setTimeout(() => { window.location.reload(); }, 1200); }
                    else { alert('Erreur : ' + data.message); checkValidation(); }
                });
            }
            document.addEventListener('DOMContentLoaded', () => { applyFilters(); });
        </script>
        <?php
    }
}
?>
