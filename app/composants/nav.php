<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom py-3">
    <div class="container-fluid px-5">
        <a class="navbar-brand fs-3 text-uppercase font-handwritten" href="index.php?module=accueil">A-LA-COOL</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <?php
            require_once __DIR__ . '/../../connexion.php';
            // MENU UTILISATEUR CONNECTÉ
            if (isset($_SESSION['user'])): ?>

                <ul class="navbar-nav mx-auto align-items-center">
                    <li class="nav-item"><a class="nav-link text-white mx-3" href="index.php?module=buvettes">Nos buvettes</a></li>

                    <?php if (isset($_SESSION['id_buvette'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white mx-3" href="index.php?module=menu&action=afficher&id_buvette=<?= $_SESSION['id_buvette'] ?>">
                                <i class="bi bi-book-half me-1"></i>La Carte
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link text-white mx-3" href="index.php?module=histoire">Notre histoire</a></li>

                    <li class="nav-item"><a class="nav-link text-white mx-3" href="index.php?module=galerie">Galerie</a></li>
                </ul>

                <div class="d-flex align-items-center">
                    <?php
                    if (!class_exists('Connexion')) {
                        require_once 'Connexion.php';
                    }

                    if (isset($_SESSION['id_buvette'])):
                        $nbArticles = 0;
                        try {
                            $pdo = Connexion::getBdd();
                            $sql = "SELECT SUM(lc.quantite) as total 
                                    FROM ligne_commande lc
                                    JOIN commande c ON lc.id_commande = c.id_commande
                                    WHERE c.id_utilisateur = ? 
                                    AND c.id_buvette = ? 
                                    AND c.statut = 'En cours'
                                    AND c.est_paye = 0";

                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$_SESSION['user']['id_utilisateur'], $_SESSION['id_buvette']]);
                            $res = $stmt->fetch(PDO::FETCH_ASSOC);
                            $nbArticles = isset($res['total']) ? $res['total'] : 0;
                        } catch (Exception $e) {
                            $nbArticles = 0;
                        }
                        ?>
                        <div class="me-2">
                            <a href="index.php?module=panier&action=afficher" class="btn btn-outline-light rounded-pill px-3 position-relative d-flex align-items-center h-100">
                                <i class="bi bi-cart3 fs-6"></i>
                                <?php if ($nbArticles > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                                        <?= $nbArticles ?>
                                        <span class="visually-hidden">articles</span>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div> <div class="dropdown ms-2">
                    <button class="btn btn-outline-light dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['user']['prenom']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <span class="dropdown-item-text small text-muted">Connecté en tant que :<br><strong><?= htmlspecialchars($_SESSION['user']['prenom']) ?></strong></span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?module=compte&action=historique"><i class="bi bi-clock-history me-2"></i>Mes commandes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?module=connexion&action=deconnexion">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>

            <?php else:
                // MENU VISITEUR (NON CONNECTÉ)
                ?>
                <ul class="navbar-nav mx-auto align-items-center">
                    <!-- Liens publics ajoutés ici -->
                    <li class="nav-item"><a class="nav-link text-white mx-3" href="index.php?module=histoire">Notre histoire</a></li>
                    <li class="nav-item"><a class="nav-link text-white mx-3" href="index.php?module=galerie">Galerie</a></li>
                </ul>

                <a href="index.php?module=connexion" class="btn bg-custom-dark rounded-0 px-4 py-2 text-white">
                    Démarrer la soirée
                </a>
            <?php endif; ?>

        </div> </div> </nav>