<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-custom-dark shadow fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand font-handwritten fs-2" href="index.php?module=accueil">
            <i class="bi bi-cup-hot-fill me-2 text-warning"></i>À LA COOL
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto align-items-center">

                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item"><a class="nav-link text-white mx-3" href="index.php?module=buvettes">Nos buvettes</a></li>

                    <?php if (isset($_SESSION['id_buvette'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white mx-3" href="index.php?module=menu&action=afficher&id_buvette=<?= $_SESSION['id_buvette'] ?>">
                                <i class="bi bi-book-half me-1"></i>La Carte
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <li class="nav-item"><a class="nav-link text-white mx-3" href="#">Notre histoire</a></li>
                <li class="nav-item"><a class="nav-link text-white mx-3" href="#">Galerie</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="text-white text-end me-2 d-none d-lg-block">
                        <div class="fw-bold small"><?= htmlspecialchars($_SESSION['user']['prenom']) ?></div>
                        <div class="badge bg-warning text-dark rounded-pill">
                            <?= $_SESSION['user']['solde'] ?> €
                        </div>
                    </div>
                    <a href="index.php?module=connexion&action=deconnexion" class="btn btn-outline-light rounded-pill px-4 btn-sm">
                        Déconnexion
                    </a>
                <?php else: ?>
                    <a href="index.php?module=connexion" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm">
                        Connexion
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>