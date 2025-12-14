<?php
class VueBuvettes {
    public function afficherBuvettes() {
        ?>
        <div class="container mt-5 pt-5">
            <h1 class="mb-5 text-center font-handwritten">Nos Buvettes</h1>

            <?php for ($i = 0; $i < 4; $i++): //Boucle sur 4 pour le moment, mais mettre selon notre liste dans la BDD ?>

                <div class="row align-items-center bg-secondary-subtle p-4 mb-4 rounded-5 shadow-sm">

                    <div class="col-12 col-md-3 col-lg-2 text-center mb-3 mb-md-0">
                        <div class="rounded-circle bg-black mx-auto" style="width: 120px; height: 120px;"></div>
                    </div>

                    <div class="col-12 col-md-9 col-lg-10">
                        <h3 class="fw-bold font-serif">Titre de la buvette</h3>
                        <p class="mb-0">
                            Je sais pas tout ce que tu veux Je sais pas tout ce que tu veux
                            Je sais pas tout ce que tu veux Je sais pas tout ce que tu veux...
                        </p>
                    </div>

                </div>

            <?php endfor; ?>
        </div>
        <?php
    }
}
?>