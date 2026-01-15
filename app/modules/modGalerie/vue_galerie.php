<?php

class VueGalerie
{
    public function afficherGalerie()
    {
        ?>
        <div class="container mt-5 pt-5">
            <!-- En-tête -->
            <div class="text-center mb-10 fade-in-up">
                <span class="badge bg-warning text-dark rounded-pill px-5 py-2 mb-2 shadow-sm">Moments de vie</span>
                <h1 class="display-4 fw-bold font-serif mb-3">La Galerie de A-la-Cool</h1>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Plongez dans l'ambiance unique de nos buvettes. Des sourires, des rencontres et surtout de bons moments partagés.
                </p>
            </div>

            <!-- Grille de photos -->
            <div class="row g-4 mb-5">

                <!-- Photo 1 (Grande) -->
                <div class="col-md-8 fade-in-up" style="animation-delay: 0.1s;">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100 position-relative group-hover-zoom">
                        <img src="https://placehold.co/800x500/EEE/31343C?text=Ambiance+Soir%C3%A9e" class="img-fluid w-100 h-100 object-fit-cover transition-transform" alt="Soirée étudiante">
                        <div class="card-img-overlay bg-gradient-dark d-flex align-items-end p-4 opacity-0 hover-opacity-100 transition-opacity">
                            <div class="text-white">
                                <h5 class="fw-bold mb-1">Soirées Étudiantes</h5>
                                <p class="small mb-0 opacity-75">Chaque jeudi soir, l'ambiance est au rendez-vous.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo 2 -->
                <div class="col-md-4 fade-in-up" style="animation-delay: 0.2s;">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100 position-relative group-hover-zoom">
                        <img src="https://placehold.co/400x500/EEE/31343C?text=Barman" class="img-fluid w-100 h-100 object-fit-cover transition-transform" alt="Barman">
                        <div class="card-img-overlay bg-gradient-dark d-flex align-items-end p-4 opacity-0 hover-opacity-100 transition-opacity">
                            <div class="text-white">
                                <h5 class="fw-bold mb-1">Nos Barmans</h5>
                                <p class="small mb-0 opacity-75">Toujours le sourire pour vous servir.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo 3 -->
                <div class="col-md-4 fade-in-up" style="animation-delay: 0.3s;">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100 position-relative group-hover-zoom">
                        <img src="https://placehold.co/400x400/EEE/31343C?text=Terrasse" class="img-fluid w-100 h-100 object-fit-cover transition-transform" alt="Terrasse">
                        <div class="card-img-overlay bg-gradient-dark d-flex align-items-end p-4 opacity-0 hover-opacity-100 transition-opacity">
                            <div class="text-white">
                                <h5 class="fw-bold mb-1">La Terrasse</h5>
                                <p class="small mb-0 opacity-75">Profitez du soleil entre deux cours.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo 4 -->
                <div class="col-md-4 fade-in-up" style="animation-delay: 0.4s;">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100 position-relative group-hover-zoom">
                        <img src="https://placehold.co/400x400/EEE/31343C?text=Caf%C3%A9" class="img-fluid w-100 h-100 object-fit-cover transition-transform" alt="Café">
                        <div class="card-img-overlay bg-gradient-dark d-flex align-items-end p-4 opacity-0 hover-opacity-100 transition-opacity">
                            <div class="text-white">
                                <h5 class="fw-bold mb-1">Pause Café</h5>
                                <p class="small mb-0 opacity-75">Le meilleur expresso du campus.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo 5 -->
                <div class="col-md-4 fade-in-up" style="animation-delay: 0.5s;">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100 position-relative group-hover-zoom">
                        <img src="https://placehold.co/400x400/EEE/31343C?text=Concert" class="img-fluid w-100 h-100 object-fit-cover transition-transform" alt="Concert">
                        <div class="card-img-overlay bg-gradient-dark d-flex align-items-end p-4 opacity-0 hover-opacity-100 transition-opacity">
                            <div class="text-white">
                                <h5 class="fw-bold mb-1">Live Music</h5>
                                <p class="small mb-0 opacity-75">Des concerts acoustiques une fois par mois.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Call to action -->
            <div class="bg-custom-dark text-white rounded-5 p-5 text-center mb-5 fade-in-up" style="animation-delay: 0.6s;">
                <h2 class="font-handwritten mb-3">Envie de nous rejoindre ?</h2>
                <p class="mb-4 text-white-50">Venez découvrir l'ambiance par vous-même !</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="index.php?module=connexion" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">Se connecter</a>
                    <a href="index.php?module=connexion&action=form_inscription" class="btn btn-outline-light rounded-pill px-4">Créer un compte</a>
                </div>
            </div>
        </div>

        <style>
            /* Petits effets CSS pour rendre la galerie vivante */
            .group-hover-zoom:hover .transition-transform {
                transform: scale(1.05);
            }
            .transition-transform {
                transition: transform 0.5s ease;
            }
            .bg-gradient-dark {
                background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            }
            .transition-opacity {
                transition: opacity 0.3s ease;
            }
            .hover-opacity-100:hover {
                opacity: 1 !important;
            }

            .fade-in-up {
                animation: fadeInUp 0.8s ease-out forwards;
                opacity: 0;
                transform: translateY(20px);
            }

            @keyframes fadeInUp {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
        <?php
    }
}
?>