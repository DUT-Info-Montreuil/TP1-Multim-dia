<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom py-3">
    <div class="container-fluid px-5">
        <a class="navbar-brand fs-3 text-uppercase font-handwritten" href="index.php?module=accueil">A-LA-COOL</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto fs-5">
                <li class="nav-item"><a class="nav-link text-white mx-3" href="index.php?module=buvettes">Nos buvettes</a></li>

                <?php if (isset($_SESSION['user']) && isset($_SESSION['id_buvette'])): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white mx-3" href="index.php?module=menu&action=afficher&id_buvette=<?= $_SESSION['id_buvette'] ?>">
                            La Carte
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item"><a class="nav-link text-white mx-3" href="#">Notre histoire</a></li>
                <li class="nav-item"><a class="nav-link text-white mx-3" href="#">Galerie</a></li>
            </ul>

            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['user']['prenom']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><span class="dropdown-item-text small text-muted">Connecté en tant que :<br><strong><?= htmlspecialchars($_SESSION['user']['prenom']) ?></strong></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?module=connexion&action=deconnexion">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="index.php?module=connexion" class="btn bg-custom-dark rounded-0 px-4 py-2">Démarrer la soirée</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>