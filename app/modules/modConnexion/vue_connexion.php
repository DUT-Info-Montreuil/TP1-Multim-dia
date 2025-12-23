<?php
class VueConnexion {
    public function afficherFormulaireConnexion() {
        ?>
        <div class="vh-100 d-flex align-items-center justify-content-center position-relative"
             style="background-image: url('public/img/main.jpg'); background-size: cover; background-position: center;">

            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>

            <div class="bg-white p-5 rounded shadow-lg position-relative" style="width: 400px; max-width: 90%;">

                <h2 class="text-center mb-4" style="font-family: serif; font-weight: bold;">Me connecter</h2>

                <form method="post" action="index.php?module=connexion&action=verifie_connexion">

                    <div class="mb-3 text-center">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="email" class="form-control border-start-0 rounded-end-pill" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="mb-4 text-center">
                        <label for="password" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 rounded-end-pill" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn bg-custom-dark rounded-pill px-4 mb-3">Se connecter</button>
                    </div>

                    <div class="text-center">
                        <a href="index.php?module=connexion&action=afficher_inscription" class="text-dark text-decoration-underline small">Je n'ai pas de compte</a>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    public function afficherFormulaireInscription() {
        ?>
        <div class="vh-100 d-flex align-items-center justify-content-center position-relative"
             style="background-image: url('public/img/main.jpg');
                    background-size: cover;
                    background-position: center;">

            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>

            <div class="bg-white p-5 rounded shadow-lg position-relative" style="width: 400px; max-width: 90%;">

                <h2 class="text-center mb-4" style="font-family: serif; font-weight: bold;">Inscription</h2>

                <form method="post" action="index.php?module=connexion&action=valider_inscription">

                    <div class="mb-3 text-center">
                        <label for="prenom" class="form-label ">Prenom</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 rounded-end-pill" id="prenom" name="prenom" required>
                        </div>
                    </div>

                    <div class="mb-3 text-center">
                        <label for="nom" class="form-label ">Nom</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 rounded-end-pill" id="nom" name="nom" required>
                        </div>
                    </div>

                    <div class="mb-3 text-center">
                        <label for="email" class="form-label ">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="email" class="form-control border-start-0 rounded-end-pill" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="mb-4 text-center">
                        <label for="motdepasse" class="form-label text-center">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 rounded-end-pill" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn bg-custom-dark rounded-pill px-4 mb-3">S'inscrire</button>
                    </div>

                    <div class="text-center">
                        <a href="index.php?module=connexion&action=afficher_connexion" class="text-dark text-decoration-underline small">J'ai déjà un compte</a>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
?>