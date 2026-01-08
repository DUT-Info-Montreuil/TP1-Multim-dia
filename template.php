<?php global$tampon;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A LA COOL</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC:wght@700&family=Oswald:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>
<?php include 'app/composants/nav.php'; ?>

<?php if (isset($_SESSION['bienvenue'])): ?>
    <div class="position-fixed top-0 start-50 translate-middle-x mt-5 pt-4" style="z-index: 1060;">
        <div class="alert alert-success alert-dismissible fade show shadow-lg" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $_SESSION['bienvenue'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['bienvenue']); ?>
<?php endif; ?>

<main>
    <?= $tampon ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>