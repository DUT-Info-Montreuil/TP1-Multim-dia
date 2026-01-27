<?php
class VueHistoire {
    public function afficherHistoire() {
        ?>
        <div class="container mt-5 pt-5 mb-5">

            <!-- En-tête -->
            <div class="text-center mb-5 fade-in">
                <h1 class="font-handwritten display-3 mb-3">Notre Histoire</h1>
                <p class="lead text-muted w-75 mx-auto">
                    D'une simple idée entre amis à ce que nous sommes aujourd'hui, découvrez comment
                    <span class="font-serif fw-bold text-warning">A-LA-COOL</span> est devenu le rendez-vous incontournable.
                </p>
                <div class="mt-4">
                    <i class="bi bi-stars text-warning fs-3"></i>
                </div>
            </div>

            <!-- Section 1 : Le commencement -->
            <div class="row align-items-center mb-5 pb-4">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="position-relative">
                        <!-- Image placeholder : remplace par le nom de ton image -->
                        <div class="rounded-5 overflow-hidden shadow-lg border border-5 border-white transform-rotate-n3">
                            <img src="public/img/story_1.jpg" onerror="this.src='https://placehold.co/600x400?text=Le+Commencement'" class="img-fluid w-100" alt="Le début">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 px-md-5">
                    <h2 class="font-serif text-uppercase mb-3">2023 : Tout a commencé par une soif...</h2>
                    <p class="text-muted">
                        C'était un soir d'été, après les cours. Nous étions assis sur un banc, à refaire le monde.
                        On s'est dit : <em>"Pourquoi est-ce si compliqué de trouver un endroit sympa, pas cher, et où l'ambiance est vraiment détendue ?"</em>
                    </p>
                    <p class="text-muted">
                        L'idée était lancée. Pas de chichi, pas de dress-code, juste de la bonne humeur et des produits de qualité.
                        Nous avons récupéré trois planches de bois, deux tréteaux, et notre première buvette éphémère était née.
                    </p>
                </div>
            </div>

            <!-- Section 2 : L'évolution (Inversée) -->
            <div class="row align-items-center mb-5 pb-4">
                <div class="col-md-6 order-md-2 mb-4 mb-md-0">
                    <div class="position-relative">
                        <div class="rounded-5 overflow-hidden shadow-lg border border-5 border-white transform-rotate-3">
                            <img src="public/img/story_2.jpg" onerror="this.src='https://placehold.co/600x400?text=L\'Ambiance'" class="img-fluid w-100" alt="L'ambiance">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 order-md-1 px-md-5 text-md-end">
                    <h2 class="font-serif text-uppercase mb-3">Un concept qui rassemble</h2>
                    <p class="text-muted">
                        Très vite, le concept a plu. Ce n'était plus seulement nous, mais vous.
                        Les étudiants, les profs, les passants... tout le monde s'est pris au jeu.
                    </p>
                    <p class="text-muted">
                        Nous avons développé notre propre système de commande (celui que vous utilisez aujourd'hui !)
                        pour éviter les files d'attente interminables. Plus de temps pour discuter, moins de temps à attendre.
                        C'est ça, la philosophie <span class="fw-bold">A-LA-COOL</span>.
                    </p>
                </div>
            </div>

            <!-- Section 3 : Aujourd'hui -->
            <div class="row align-items-center mb-5">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="position-relative">
                        <div class="rounded-5 overflow-hidden shadow-lg border border-5 border-white transform-rotate-n3">
                            <img src="public/img/story_3.jpg" onerror="this.src='https://placehold.co/600x400?text=L\'Equipe'" class="img-fluid w-100" alt="L'équipe">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 px-md-5">
                    <h2 class="font-serif text-uppercase mb-3">Aujourd'hui et demain</h2>
                    <p class="text-muted">
                        Aujourd'hui, nous gérons plusieurs buvettes lors des événements majeurs.
                        Notre équipe s'est agrandie, mais l'esprit reste le même : convivialité et simplicité.
                    </p>
                    <div class="bg-light p-4 rounded-4 mt-4 border-start border-4 border-warning">
                        <p class="mb-0 fst-italic fw-bold text-dark">
                            "Merci de faire partie de cette aventure. À chaque verre partagé, c'est une nouvelle page de l'histoire qui s'écrit."
                        </p>
                        <small class="text-muted mt-2 d-block">- L'équipe fondatrice</small>
                    </div>
                </div>
            </div>

            <!-- Call to Action : Visible uniquement si NON connecté -->
            <?php if (!isset($_SESSION['user'])): ?>
                <div class="text-center mt-5 pt-4">
                    <a href="index.php?module=connexion" class="btn btn-warning rounded-pill px-5 py-3 fw-bold shadow-sm text-uppercase">
                        Rejoignez la fête <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php endif; ?>

        </div>

        <!-- Petit CSS inline pour les rotations sympas des images -->
        <style>
            .transform-rotate-3 { transform: rotate(3deg); transition: transform 0.3s; }
            .transform-rotate-n3 { transform: rotate(-3deg); transition: transform 0.3s; }
            .transform-rotate-3:hover, .transform-rotate-n3:hover { transform: rotate(0deg) scale(1.02); }
        </style>
        <?php
    }
}
?>