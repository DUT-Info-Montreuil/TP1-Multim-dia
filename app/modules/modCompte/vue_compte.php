<?php

class VueCompte
{
    public function afficherHistorique($commandes)
    {
        ?>
        <div class="container mt-5 pt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="font-handwritten mb-0">Mes anciennes commandes</h1>
                <a href="index.php?module=menu&action=afficher" class="btn btn-outline-dark rounded-pill">
                    <i class="bi bi-arrow-left me-2"></i>Retour au menu
                </a>
            </div>

            <?php if (empty($commandes)): ?>
                <div class="alert alert-secondary text-center rounded-4 py-5">
                    <i class="bi bi-clock-history display-4 d-block mb-3"></i>
                    <p class="fs-5">Aucune commande passée pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($commandes as $cmd):
                        $badgeClass = 'bg-secondary';
                        $textStatut = $cmd['statut'];
                        switch ($cmd['statut']) {
                            case 'En attente confirmation':
                                $badgeClass = 'bg-warning text-dark';
                                break;
                            case 'Attente Validation':
                            case 'En attente':
                            case 'Payé':
                                $badgeClass = 'bg-warning text-dark';
                                break;
                            case 'Préparation':
                                $badgeClass = 'bg-info text-dark';
                                break;
                            case 'Arrivé':
                            case 'Parti':
                                $badgeClass = 'bg-success';
                                break;
                            case 'Annulée':
                            case 'Annulé':
                                $badgeClass = 'bg-danger';
                                break;
                            default:
                                $badgeClass = 'bg-secondary';
                        }
                        $date = new DateTime($cmd['date_commande']);
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border border-2 shadow-sm rounded-4 h-100 bg-light" style="border-color: #dee2e6 !important;">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title fw-bold mb-0 text-truncate" style="max-width: 70%;">
                                            <?= htmlspecialchars($cmd['nom_buvette']) ?>
                                        </h5>
                                        <span class="badge rounded-pill <?= $badgeClass ?>">
                                            <?= htmlspecialchars($textStatut) ?>
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-calendar-event me-2"></i>
                                        <?= $date->format('d/m/Y à H:i') ?>
                                    </p>
                                    <div class="mb-3">
                                        <span class="fs-5 fw-bold text-dark"><?= number_format($cmd['prix_total'], 2) ?> €</span>
                                    </div>
                                    <div class="mt-auto pt-3 border-top text-center">
                                        <a href="index.php?module=detailsCommande&action=afficher&id_commande=<?= $cmd['id_commande'] ?>"
                                           class="btn btn-dark rounded-pill px-4 w-100">
                                            Suivre / Détails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function afficherMonCompte($user, $mesBuvettes = [], $message = null, $typeMessage = 'success')
    {
        ?>
        <div class="container mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="font-handwritten mb-0">Mon Profil</h1>
                        <a href="index.php?module=menu&action=afficher" class="btn btn-outline-dark rounded-pill">
                            <i class="bi bi-house me-2"></i>Accueil
                        </a>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $typeMessage ?> rounded-4 mb-4 shadow-sm">
                            <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-3 me-3 text-secondary">
                                    <i class="bi bi-person-fill fs-3"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold font-serif"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                                    <p class="text-muted mb-0 small"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-custom-dark text-white p-3 border-0">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-envelope me-2"></i>Modifier mon adresse e-mail</h5>
                        </div>
                        <div class="card-body p-4 bg-light">
                            <form action="index.php?module=compte&action=modifierEmail" method="POST">
                                <div class="input-group mb-3">
                                    <input type="email" name="new_email" class="form-control rounded-start-pill" placeholder="Nouvel email..." required>
                                    <button type="submit" class="btn btn-warning rounded-end-pill fw-bold">Valider</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-secondary text-white p-3 border-0">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>Sécurité</h5>
                        </div>
                        <div class="card-body p-4 bg-light">
                            <form action="index.php?module=compte&action=modifierMdp" method="POST">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="password" name="old_mdp" class="form-control rounded-pill mb-2" placeholder="Mot de passe actuel" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" name="new_mdp" class="form-control rounded-pill" placeholder="Nouveau mot de passe" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" name="confirm_mdp" class="form-control rounded-pill" placeholder="Confirmer nouveau" required>
                                    </div>
                                    <div class="col-12 text-end mt-2">
                                        <button type="submit" class="btn btn-dark rounded-pill px-4">Changer</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function afficherNotifications($notifications, $message = null, $typeMessage = 'success')
    {
        ?>
        <div class="container mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h1 class="font-handwritten mb-0">Mes Notifications</h1>
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $typeMessage ?> rounded-4 mb-4 shadow-sm">
                                <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>
                        <a href="index.php?module=compte&action=profil" class="btn btn-outline-dark rounded-pill">
                            <i class="bi bi-arrow-left me-2"></i>Retour au profil
                        </a>
                    </div>

                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash display-1 text-muted opacity-25 mb-3"></i>
                            <p class="fs-4 text-muted">Vous n'avez aucune notification de paiement en attente.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($notifications as $notif):
                                $date = new DateTime($notif['date_creation']);
                                ?>
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                                                <i class="bi bi-cash-stack fs-3"></i>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-1">
                                                    Demande de paiement - <?= htmlspecialchars($notif['nom_buvette']) ?>
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    <i class="bi bi-calendar-event me-1"></i> <?= $date->format('d/m/Y à H:i') ?>
                                                    <span class="mx-2">•</span>
                                                    Commande #<?= $notif['id_commande'] ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="text-md-end d-flex flex-column align-items-md-end align-items-start gap-2">
                                            <span class="fs-4 fw-bold text-dark mb-1">
                                                <?= number_format($notif['montant'], 2) ?> €
                                            </span>

                                            <div class="d-flex gap-2">
                                                <form action="index.php?module=compte&action=refuserPaiement" method="POST">
                                                    <input type="hidden" name="id_notification" value="<?= $notif['id_notification'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger rounded-pill px-3 py-1 fw-bold btn-sm">
                                                        <i class="bi bi-x-lg me-1"></i>Refuser
                                                    </button>
                                                </form>

                                                <form action="index.php?module=compte&action=validerPaiement" method="POST">
                                                    <input type="hidden" name="id_notification" value="<?= $notif['id_notification'] ?>">
                                                    <button type="submit" class="btn btn-success rounded-pill px-4 py-1 fw-bold btn-sm shadow-sm">
                                                        <i class="bi bi-check-lg me-1"></i>Payer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-footer bg-warning bg-opacity-25 border-0 p-1"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php
    }
}
?>
