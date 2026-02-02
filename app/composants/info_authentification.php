<?php if (isset($_SESSION['user'])): ?>
    <div class="container mt-2">
        <div class="d-flex justify-content-end align-items-center">
            <span class="badge bg-light text-dark border p-2">
                <i class="bi bi-person-circle me-1"></i>
                Connecté en tant que : <strong><?= htmlspecialchars($_SESSION['user']['prenom']) ?></strong>
            </span>
            <a href="index.php?module=connexion&action=deconnexion" class="btn btn-sm btn-outline-danger ms-2">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
<?php endif; ?>