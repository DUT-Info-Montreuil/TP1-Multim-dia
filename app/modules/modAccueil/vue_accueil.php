<?php
class VueAccueil {
    public function afficherAccueil() {
        ?>
        <div class="vh-100 d-flex align-items-center justify-content-center position-relative"
             style="background-image: url('public/img/main.jpg');
                    background-size: cover;
                    background-position: center;">

            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>

            <div class="text-center position-relative text-white">
                <h1 class="display-1 font-handwritten text-uppercase fw-bold" style="font-size: 8rem;">
                    A-LA-COOL
                </h1>

                <div class="mt-4">
                    <span class="fs-4 text-uppercase border-top border-bottom py-2 px-4">Bar & Ambiance</span>
                </div>
            </div>
        </div>
        <?php
    }
}
?>