<?php

class VueCreationBuvette
{
    public function afficherFormulaire($error = null)
    {
        ?>
        <div class="container mt-5 pt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="font-handwritten mb-0">Créer une buvette</h1>
                        <a href="index.php?module=buvettes" class="btn btn-outline-dark rounded-pill">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>
                    </div>

                    <div class="card border-0 shadow-lg rounded-5 overflow-hidden">
                        <div class="card-header bg-custom-dark text-white p-4 border-0">
                            <h4 class="mb-0 fw-bold font-serif"><i class="bi bi-shop-window me-2"></i>Votre projet</h4>
                            <p class="mb-0 opacity-75 small">Remplissez ce formulaire pour soumettre votre idée aux administrateurs.</p>
                        </div>

                        <div class="card-body p-5 bg-white">

                            <?php if ($error): ?>
                                <div class="alert alert-danger rounded-4 mb-4">
                                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form action="index.php?module=creation_buvette&action=creer" method="POST">

                                <div class="mb-4">
                                    <label for="nom" class="form-label fw-bold text-uppercase small text-muted">Nom de la buvette</label>
                                    <input type="text" class="form-control form-control-lg rounded-4 bg-light border-1"
                                           id="nom" name="nom" placeholder="Ex: Chez Dédé, Le QG " required>
                                </div>

                                <div class="mb-4">
                                    <label for="desc" class="form-label fw-bold text-uppercase small text-muted">Description & Concept</label>
                                    <textarea class="form-control rounded-4 bg-light border-1"
                                              id="desc" name="description" rows="5"
                                              placeholder="Décrivez l'ambiance, ce que vous comptez vendre, pourquoi ce serait génial..." required></textarea>
                                </div>

                                <div class="alert alert-light border rounded-4 small text-muted mb-4">
                                    <i class="bi bi-info-circle me-1"></i> Votre demande sera examinée par un administrateur. Vous recevrez une notification une fois validée.
                                </div>

                                <button type="submit" class="btn btn-warning w-100 rounded-pill py-3 fw-bold shadow-sm fs-5 transform-hover">
                                    Envoyer la demande <i class="bi bi-send-fill ms-2"></i>
                                </button>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }

    public function afficherSucces()
    {
        ?>
        <div class="container mt-5 pt-5 text-center">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card border-0 shadow rounded-5 p-5">
                        <div class="mb-4">
                            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="bi bi-check-lg display-1 text-success"></i>
                            </div>
                        </div>

                        <h2 class="font-serif fw-bold mb-3">Demande envoyée !</h2>
                        <p class="text-muted fs-5 mb-5">
                            Merci pour votre initiative. Votre projet est maintenant entre les mains de nos administrateurs.
                            Croisons les doigts !
                        </p>

                        <a href="index.php?module=buvettes" class="btn btn-dark rounded-pill px-5 py-3 fw-bold">
                            Retour aux buvettes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>