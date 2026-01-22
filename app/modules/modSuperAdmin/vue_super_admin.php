<?php

class VueSuperAdmin {

    public function afficherTableauBord($stats) {
        ?>
        <style>
            .mt-7{
                margin-top: 4rem !important;
            }
            .pt-7{
                padding-top: 4rem !important;
            }
        </style>
        <div class="container mt-7 pt-7">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="font-handwritten">Panneau d'Administration</h1>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['total_buvettes'] ?></h4>
                                    <p class="card-text">Buvettes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-store fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['buvettes_ouvertes'] ?></h4>
                                    <p class="card-text">Ouvertes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-door-open fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['total_gestionnaires'] ?></h4>
                                    <p class="card-text">Gestionnaires</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users-cog fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['total_utilisateurs'] ?></h4>
                                    <p class="card-text">Utilisateurs</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation principale -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-store fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Gestion des Buvettes</h5>
                            <p class="card-text">Modifier, archiver et gérer les buvettes de la plateforme</p>
                            <a href="index.php?module=superadmin&action=gestion_buvettes" class="btn btn-primary">
                                Gérer les Buvettes
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users-cog fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Gestion des Gestionnaires</h5>
                            <p class="card-text">Attribuer, modifier et retirer les rôles de gestionnaire</p>
                            <a href="index.php?module=superadmin&action=gestion_gestionnaires" class="btn btn-warning">
                                Gérer les Gestionnaires
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-clipboard-list fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Journal d'Activité</h5>
                            <p class="card-text">Consulter l'historique des actions administratives</p>
                            <a href="index.php?module=superadmin&action=journal_activite" class="btn btn-info">
                                Voir le Journal
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Demandes de création</h5>
                            <p class="card-text">Valider ou rejeter les demandes de nouvelles buvettes</p>
                            <a href="index.php?module=superadmin&action=gestion_demandes_creation" class="btn btn-primary">
                                Gérer les demandes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function afficherGestionDemandesCreation($demandes, $message = null) {
        $token = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
        ?>
        <div class="container mt-5 pt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="font-handwritten">Demandes de création de buvettes</h1>
                <a href="index.php?module=superadmin" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php
                    switch($message) {
                        case 'validee':
                            echo "Demande validée avec succès !";
                            break;
                        case 'rejetee':
                            echo "Demande rejetée avec succès !";
                            break;
                        case 'error':
                            echo "Une erreur est survenue lors du traitement de la demande.";
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($demandes)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4>Aucune demande en attente</h4>
                        <p class="text-muted">Toutes les demandes ont été traitées.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($demandes as $demande): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-<?php echo $demande['statut'] === 'En attente' ? 'warning' : 'secondary'; ?>">
                                <div class="card-header bg-<?php echo $demande['statut'] === 'En attente' ? 'warning' : 'light'; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <?= htmlspecialchars($demande['nom_buvette']) ?>
                                        </h5>
                                        <span class="badge bg-<?php echo $demande['statut'] === 'En attente' ? 'warning' : 'secondary'; ?>">
                                        <?= htmlspecialchars($demande['statut']) ?>
                                    </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="text-muted">Description :</h6>
                                        <p><?= nl2br(htmlspecialchars($demande['description'])) ?></p>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Demandeur :</h6>
                                            <p class="mb-0">
                                                <i class="fas fa-user me-1"></i>
                                                <?= htmlspecialchars($demande['demandeur_nom'] . ' ' . $demande['demandeur_prenom']) ?>
                                            </p>
                                            <small class="text-muted"><?= htmlspecialchars($demande['demandeur_email']) ?></small>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Date de demande :</h6>
                                            <p class="mb-0">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?>
                                            </p>
                                        </div>
                                    </div>

                                    <?php if ($demande['raison_refus']): ?>
                                        <div class="alert alert-danger mt-3">
                                            <h6><i class="fas fa-ban me-1"></i> Raison du refus :</h6>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($demande['raison_refus'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($demande['statut'] === 'En attente'): ?>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex justify-content-between">
                                            <form method="POST" action="index.php?module=superadmin&action=valider_demande_creation"
                                                  class="me-2">
                                                <input type="hidden" name="csrf_token" value="<?= $token ?>">
                                                <input type="hidden" name="id_demande" value="<?= $demande['id_demande'] ?>">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check me-1"></i> Valider
                                                </button>
                                            </form>

                                            <button type="button" class="btn btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejeterModal"
                                                    data-id="<?= $demande['id_demande'] ?>"
                                                    data-nom="<?= htmlspecialchars($demande['nom_buvette']) ?>">
                                                <i class="fas fa-times me-1"></i> Rejeter
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal pour rejeter une demande -->
        <div class="modal fade" id="rejeterModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="index.php?module=superadmin&action=rejeter_demande_creation">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Rejeter la demande</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_demande" id="rejeter_id_demande">

                            <p>Vous êtes sur le point de rejeter la demande pour :</p>
                            <p class="fw-bold" id="rejeter_nom_buvette"></p>

                            <div class="mb-3">
                                <label for="raison_refus" class="form-label">Raison du refus *</label>
                                <textarea class="form-control" name="raison_refus" id="raison_refus"
                                          rows="4" placeholder="Expliquez pourquoi vous rejetez cette demande..."
                                          required></textarea>
                                <div class="form-text">Cette raison sera communiquée au demandeur.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('rejeterModal').addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                document.getElementById('rejeter_id_demande').value = button.getAttribute('data-id');
                document.getElementById('rejeter_nom_buvette').textContent = button.getAttribute('data-nom');
            });
        </script>
        <?php
    }
    public function afficherGestionBuvettes($buvettes, $token, $message = null) {
        require_once __DIR__ . '/modele_super_admin.php';
        $modele = new ModeleSuperAdmin();
        $utilisateursDisponibles = $modele->getUtilisateursSansRole();
        ?>
        <div class="container mt-5 pt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="font-handwritten">Gestion des Buvettes</h1>
                <div>
                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#creeBuvetteModal">
                        <i class="fas fa-plus"></i> Crée une Buvette
                    </button>
                    <a href="index.php?module=superadmin" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Liste des Buvettes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Nom de la buvette</th>
                                <th>Gestionnaire</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($buvettes as $buvette): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($buvette['nom']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($buvette['description']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($buvette['gestionnaire_nom']): ?>
                                            <span class="badge bg-success fs-6">
                                                <?= htmlspecialchars($buvette['gestionnaire_nom'] . ' ' . $buvette['gestionnaire_prenom']) ?>
                                            </span>
                                        <?php else: ?>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-warning fs-6 me-2">Aucun gestionnaire</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#attribuerGestionnaireBuvetteModal"
                                                        data-id-buvette="<?= $buvette['id_buvette'] ?>"
                                                        data-nom-buvette="<?= htmlspecialchars($buvette['nom']) ?>">
                                                    <i class="fas fa-user-plus"></i> Attribuer
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($buvette['est_ouverte']): ?>
                                            <span class="badge bg-success fs-6">Ouverte</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger fs-6">Fermée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modifierBuvetteModal"
                                                data-id="<?= $buvette['id_buvette'] ?>"
                                                data-nom="<?= htmlspecialchars($buvette['nom']) ?>"
                                                data-description="<?= htmlspecialchars($buvette['description']) ?>"
                                                data-est-ouverte="<?= $buvette['est_ouverte'] ?>">
                                            <i class="fas fa-edit"></i> Modifier
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#supprimerBuvetteModal"
                                                data-id="<?= $buvette['id_buvette'] ?>"
                                                data-nom="<?= htmlspecialchars($buvette['nom']) ?>">
                                            <i class="fas fa-archive"></i> Archiver
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Attribuer Gestionnaire à une Buvette -->
        <div class="modal fade" id="attribuerGestionnaireBuvetteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="index.php?module=superadmin&action=attribuer_gestionnaire_buvette">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Attribuer un Gestionnaire</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_buvette" id="attribuer_id_buvette">

                            <div class="mb-3">
                                <p>Buvette : <strong id="attribuer_nom_buvette"></strong></p>
                            </div>

                            <div class="mb-3">
                                <label for="attribuer_id_utilisateur" class="form-label">Sélectionner un utilisateur *</label>
                                <select class="form-control" name="id_utilisateur" id="attribuer_id_utilisateur" required>
                                    <option value="">Choisir un utilisateur...</option>
                                    <?php foreach ($utilisateursDisponibles as $utilisateur): ?>
                                        <option value="<?= $utilisateur['id_utilisateur'] ?>">
                                            <?= htmlspecialchars($utilisateur['nom'] . ' ' . $utilisateur['prenom'] . ' (' . $utilisateur['email'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($utilisateursDisponibles)): ?>
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Aucun utilisateur disponible. Tous les utilisateurs ont déjà un rôle.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success" <?= empty($utilisateursDisponibles) ? 'disabled' : '' ?>>
                                <i class="fas fa-user-plus"></i> Attribuer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Créer Buvette -->
        <div class="modal fade" id="creeBuvetteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="index.php?module=superadmin&action=creer_buvette">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Créer une Buvette</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="creer">

                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom de la buvette *</label>
                                <input type="text" class="form-control" name="nom" required
                                       placeholder="Ex: Buvette du terrain A">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"
                                          placeholder="Description de la buvette (emplacement, caractéristiques...)"
                                          maxlength="255"></textarea>
                                <div class="form-text">Maximum 255 caractères.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">Créer la buvette</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Modifier Buvette -->
        <div class="modal fade" id="modifierBuvetteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier la buvette</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="modifier">
                            <input type="hidden" name="id_buvette" id="modifier_id_buvette">

                            <div class="mb-3">
                                <label for="modifier_nom" class="form-label">Nom de la buvette</label>
                                <input type="text" class="form-control" id="modifier_nom" name="nom" required>
                            </div>

                            <div class="mb-3">
                                <label for="modifier_description" class="form-label">Description</label>
                                <textarea class="form-control" id="modifier_description" name="description" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           id="modifier_est_ouverte"
                                           name="est_ouverte"
                                           value="1">
                                    <label class="form-check-label" for="modifier_est_ouverte">
                                        Buvette ouverte
                                    </label>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Si activé, la buvette sera marquée comme "ouverte".
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Supprimer Buvette -->
        <div class="modal fade" id="supprimerBuvetteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="index.php?module=superadmin&action=supprimer_buvette">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Archiver la buvette</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_buvette" id="supprimer_id_buvette">

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Attention :</strong> Voulez-vous archiver cette buvette ?
                                Elle ne sera plus visible mais les données historiques (réservations, commandes) seront conservées.
                            </div>

                            <p>Buvette : <strong id="supprimer_nom_buvette"></strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Archiver</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('attribuerGestionnaireBuvetteModal').addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                document.getElementById('attribuer_id_buvette').value = button.getAttribute('data-id-buvette');
                document.getElementById('attribuer_nom_buvette').textContent = button.getAttribute('data-nom-buvette');
            });

            document.getElementById('modifierBuvetteModal').addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                document.getElementById('modifier_id_buvette').value = button.getAttribute('data-id');
                document.getElementById('modifier_nom').value = button.getAttribute('data-nom');
                document.getElementById('modifier_description').value = button.getAttribute('data-description');

                let estOuverte = button.getAttribute('data-est-ouverte');
                document.getElementById('modifier_est_ouverte').checked = (estOuverte === '1');
            });

            document.getElementById('supprimerBuvetteModal').addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                document.getElementById('supprimer_id_buvette').value = button.getAttribute('data-id');
                document.getElementById('supprimer_nom_buvette').textContent = button.getAttribute('data-nom');
            });
        </script>
        <?php
    }

    public function afficherGestionGestionnaires($gestionnaires, $utilisateursDisponibles, $buvettesDisponibles, $token, $message = null) {
        ?>
        <div class="container mt-5 pt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="font-handwritten">Gestion des Gestionnaires</h1>
                <div>
                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#attribuerGestionnaireModal">
                        <i class="fas fa-plus"></i> Attribuer un Gestionnaire
                    </button>
                    <a href="index.php?module=superadmin" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Liste des Gestionnaires</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Nom et Prénom</th>
                                <th>Email</th>
                                <th>Buvette assignée</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($gestionnaires as $gestionnaire): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gestionnaire['nom'] . ' ' . $gestionnaire['prenom']) ?></td>
                                    <td><?= htmlspecialchars($gestionnaire['email']) ?></td>
                                    <td>
                                        <?php if ($gestionnaire['buvette_nom']): ?>
                                            <span class="badge bg-primary fs-6"><?= htmlspecialchars($gestionnaire['buvette_nom']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning fs-6">Aucune buvette</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#retirerGestionnaireModal"
                                                data-id="<?= $gestionnaire['id_utilisateur'] ?>"
                                                data-nom="<?= htmlspecialchars($gestionnaire['nom'] . ' ' . $gestionnaire['prenom']) ?>">
                                            <i class="fas fa-user-minus"></i> Retirer le rôle
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($gestionnaire)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">Aucune gestionnaire enregistré</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Modal Attribuer Gestionnaire -->
        <div class="modal fade" id="attribuerGestionnaireModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Attribuer un Gestionnaire</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="attribuer">

                            <div class="mb-3">
                                <label for="id_utilisateur" class="form-label">Utilisateur</label>
                                <select class="form-control" name="id_utilisateur" required>
                                    <option value="">Sélectionnez un utilisateur</option>
                                    <?php foreach ($utilisateursDisponibles as $utilisateur): ?>
                                        <option value="<?= $utilisateur['id_utilisateur'] ?>">
                                            <?= htmlspecialchars($utilisateur['nom'] . ' ' . $utilisateur['prenom'] . ' (' . $utilisateur['email'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="id_buvette" class="form-label">Buvette</label>
                                <select class="form-control" name="id_buvette" required>
                                    <option value="">Sélectionnez une buvette</option>
                                    <?php foreach ($buvettesDisponibles as $buvette): ?>
                                        <option value="<?= $buvette['id_buvette'] ?>">
                                            <?= htmlspecialchars($buvette['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">Attribuer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Modifier Gestionnaire -->
        <div class="modal fade" id="modifierGestionnaireModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'assignation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="modifier">
                            <input type="hidden" name="id_utilisateur" id="modifier_id_utilisateur">

                            <p>Gestionnaire : <strong id="modifier_nom_gestionnaire"></strong></p>

                            <div class="mb-3">
                                <label for="modifier_id_buvette" class="form-label">Nouvelle buvette</label>
                                <select class="form-control" name="id_buvette" id="modifier_id_buvette">
<!--                                    <option value="">Aucune buvette (retirer de l'assignation actuelle)</option>-->
                                    <?php foreach ($buvettesDisponibles as $buvette): ?>
                                        <option value="<?= $buvette['id_buvette'] ?>">
                                            <?= htmlspecialchars($buvette['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Modifier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Retirer Gestionnaire -->
        <div class="modal fade" id="retirerGestionnaireModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="index.php?module=superadmin&action=retirer_gestionnaire">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Retirer le rôle de gestionnaire</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_utilisateur" id="retirer_id_utilisateur">

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Voulez-vous retirer le rôle de gestionnaire à <strong id="retirer_nom_gestionnaire"></strong> ?
                                Il redeviendra un utilisateur standard.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Retirer le rôle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('modifierGestionnaireModal').addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                document.getElementById('modifier_id_utilisateur').value = button.getAttribute('data-id');
                document.getElementById('modifier_nom_gestionnaire').textContent = button.getAttribute('data-nom');
            });

            document.getElementById('retirerGestionnaireModal').addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                document.getElementById('retirer_id_utilisateur').value = button.getAttribute('data-id');
                document.getElementById('retirer_nom_gestionnaire').textContent = button.getAttribute('data-nom');
            });
        </script>
        <?php
    }

    public function afficherJournalActivite($activites) {
        ?>
         <style>
             .bg-purple {
                 background-color: #6f42c1;
                 color: white;
             }
         </style>
        <div class="container mt-5 pt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="font-handwritten">Journal d'Activité</h1>
                <a href="index.php?module=superadmin" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Historique des Actions Administratives</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Horodatage</th>
                                <th>Action</th>
                                <th>Cible</th>
                                <th>Détails</th>
                                <th>Administrateur</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($activites as $activite): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i:s', strtotime($activite['horodatage'])) ?></td>
                                    <td>
                                        <span class="badge bg-purple fs-6"><?= htmlspecialchars($activite['action']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($activite['cible']) ?></td>
                                    <td><?= htmlspecialchars($activite['details']) ?></td>
                                    <td><?= htmlspecialchars($activite['email']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($activites)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">Aucune activité enregistrée</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
?>