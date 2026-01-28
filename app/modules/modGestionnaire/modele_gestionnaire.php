<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleGestionnaire {

    public function getBuvettesAutorisees($id_utilisateur) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT b.* FROM une_buvette b
            INNER JOIN affecter a ON b.id_buvette = a.id_buvette
            INNER JOIN role_utilisateur r ON a.id_role = r.id_role
            WHERE a.id_utilisateur = ? 
            AND r.nom_role = 'Gestionnaire'
            AND (a.date_fin IS NULL OR a.date_fin >= CURDATE())
        ");
        $req->execute([$id_utilisateur]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailsProduit($id_produit) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT p.*, c.quantite, c.seuil_alerte 
            FROM produit p
            INNER JOIN contient c ON p.id_produit = c.id_produit
            WHERE p.id_produit = ?
        ");
        $req->execute([$id_produit]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function creerEtAjouterProduit($id_buvette, $nom, $prix, $description, $nom_image, $type) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();
            $req1 = $bdd->prepare("INSERT INTO produit (nom_produit, prix_produit, description, image_produit, type_produit) VALUES (?, ?, ?, ?, ?)");
            $req1->execute([$nom, $prix, $description, $nom_image, $type]);
            $id_nouveau = $bdd->lastInsertId();

            $reqInv = $bdd->prepare("SELECT id_inventaire FROM concerner WHERE id_buvette = ? LIMIT 1");
            $reqInv->execute([$id_buvette]);
            $inv = $reqInv->fetch();

            if ($inv) {
                $req2 = $bdd->prepare("INSERT INTO contient (id_inventaire, id_produit, quantite, seuil_alerte) VALUES (?, ?, 0, 5)");
                $req2->execute([$inv['id_inventaire'], $id_nouveau]);
            }
            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            return false;
        }
    }

    public function modifierProduitEtStock($id_produit, $id_buvette, $nouveau_prix, $nouvelle_description, $nouvelle_quantite, $nouveau_type) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();
            $req1 = $bdd->prepare("UPDATE produit SET prix_produit = ?, description = ?, type_produit = ? WHERE id_produit = ?");
            $req1->execute([$nouveau_prix, $nouvelle_description, $nouveau_type, $id_produit]);

            $req2 = $bdd->prepare("
                UPDATE contient c
                INNER JOIN concerner co ON c.id_inventaire = co.id_inventaire
                SET c.quantite = ?
                WHERE c.id_produit = ? AND co.id_buvette = ?
            ");
            $req2->execute([$nouvelle_quantite, $id_produit, $id_buvette]);
            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            return false;
        }
    }

    public function getStocksParBuvette($id_buvette, $seulementAlertes = false) {
        $bdd = Connexion::getBdd();
        $sql = "SELECT p.*, c.quantite, c.seuil_alerte 
            FROM produit p
            INNER JOIN contient c ON p.id_produit = c.id_produit
            INNER JOIN concerner co ON c.id_inventaire = co.id_inventaire
            WHERE co.id_buvette = ?";

        if ($seulementAlertes) {
            $sql .= " AND c.quantite <= c.seuil_alerte";
        }

        $sql .= " ORDER BY p.type_produit ASC, p.nom_produit ASC";

        $req = $bdd->prepare($sql);
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDemandesEnAttente($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT u.id_utilisateur, u.nom, u.prenom, u.email, a.date_adhesion as date_demande
            FROM utilisateur u
            INNER JOIN adhesion a ON u.id_utilisateur = a.id_utilisateur
            WHERE a.id_buvette = ?
            ORDER BY a.date_adhesion ASC
        ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMembresAcceptes($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT u.id_utilisateur, u.nom, u.prenom, u.email, aff.date_debut
            FROM utilisateur u
            INNER JOIN affecter aff ON u.id_utilisateur = aff.id_utilisateur
            INNER JOIN role_utilisateur r ON aff.id_role = r.id_role
            WHERE aff.id_buvette = ? AND r.nom_role = 'Client'
            AND (aff.date_fin IS NULL OR aff.date_fin >= CURDATE())
            ORDER BY u.nom ASC
        ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function accepterDemande($id_utilisateur, $id_buvette) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $reqRole = $bdd->prepare("SELECT id_role FROM role_utilisateur WHERE nom_role = 'Client' LIMIT 1");
            $reqRole->execute();
            $id_role_client = $reqRole->fetchColumn();

            $reqIns = $bdd->prepare("
            INSERT INTO affecter (id_role, id_utilisateur, id_buvette, date_debut, date_fin) 
            VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))
        ");
            $reqIns->execute([$id_role_client, $id_utilisateur, $id_buvette]);

            $reqDel = $bdd->prepare("DELETE FROM adhesion WHERE id_utilisateur = ? AND id_buvette = ?");
            $reqDel->execute([$id_utilisateur, $id_buvette]);

            $bdd->commit();
        } catch (Exception $e) {
            $bdd->rollBack();
            throw $e;
        }
    }

    public function supprimerDemandeOuMembre($id_utilisateur, $id_buvette, $estDemande = true) {
        $bdd = Connexion::getBdd();
        if ($estDemande) {
            $req = $bdd->prepare("DELETE FROM adhesion WHERE id_utilisateur = ? AND id_buvette = ?");
        } else {
            $req = $bdd->prepare("DELETE FROM affecter WHERE id_utilisateur = ? AND id_buvette = ? AND id_role = (SELECT id_role FROM role_utilisateur WHERE nom_role = 'Client')");
        }
        return $req->execute([$id_utilisateur, $id_buvette]);
    }

    public function getStatsBuvette($id_buvette) {
        $bdd = Connexion::getBdd();
        $sql = "SELECT 
            (SELECT SUM(c.quantite * p.prix_produit) 
             FROM contient c 
             JOIN produit p ON c.id_produit = p.id_produit 
             JOIN concerner co ON c.id_inventaire = co.id_inventaire 
             WHERE co.id_buvette = ?) as valeur_stock,
             
            (SELECT COUNT(*) 
             FROM contient c 
             JOIN concerner co ON c.id_inventaire = co.id_inventaire 
             WHERE co.id_buvette = ? 
             AND c.quantite <= c.seuil_alerte) as alertes_count,
             
            (SELECT COUNT(*) 
             FROM adhesion 
             WHERE id_buvette = ?) as demandes_count,
             
            (SELECT COUNT(*) 
             FROM affecter aff 
             JOIN role_utilisateur r ON aff.id_role = r.id_role 
             WHERE aff.id_buvette = ? 
             AND r.nom_role = 'Client' 
             AND (aff.date_fin IS NULL OR aff.date_fin >= CURDATE())) as membres_count";

        $req = $bdd->prepare($sql);
        $req->execute([$id_buvette, $id_buvette, $id_buvette, $id_buvette]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllFournisseurs() {
        $bdd = Connexion::getBdd();
        $req = $bdd->query("SELECT * FROM fournisseur WHERE actif = 1 ORDER BY nom_fournisseur ASC");
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFournisseurById($id_fournisseur) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT * FROM fournisseur WHERE id_fournisseur = ?");
        $req->execute([$id_fournisseur]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function getProduitsParFournisseur($id_fournisseur) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT p.*, pr.prix_achat, pr.quantite_minimum
            FROM produit p
            INNER JOIN proposer pr ON p.id_produit = pr.id_produit
            WHERE pr.id_fournisseur = ?
            ORDER BY p.type_produit ASC, p.nom_produit ASC
        ");
        $req->execute([$id_fournisseur]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function creerFournisseur($nom, $email, $telephone, $adresse, $siret, $delai) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            INSERT INTO fournisseur (nom_fournisseur, email, telephone, adresse, siret, delai_livraison_jours) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $req->execute([$nom, $email, $telephone, $adresse, $siret, $delai]);
    }

    public function creerCommandeFournisseur($id_fournisseur, $id_buvette, $id_utilisateur, $lignes, $notes = '') {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $montant_total = 0;
            foreach ($lignes as $ligne) {
                $montant_total += $ligne['quantite'] * $ligne['prix_unitaire'];
            }

            $reqFourn = $bdd->prepare("SELECT delai_livraison_jours FROM fournisseur WHERE id_fournisseur = ?");
            $reqFourn->execute([$id_fournisseur]);
            $delai = $reqFourn->fetchColumn();

            $date_livraison = date('Y-m-d', strtotime("+$delai days"));
            $numero_commande = 'CF-' . date('YmdHis') . '-' . $id_buvette;

            $req = $bdd->prepare("
                INSERT INTO commande_fournisseur 
                (numero_commande, date_livraison_prevue, montant_total, notes, id_fournisseur, id_buvette, id_utilisateur) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $req->execute([$numero_commande, $date_livraison, $montant_total, $notes, $id_fournisseur, $id_buvette, $id_utilisateur]);

            $id_commande = $bdd->lastInsertId();

            $reqLigne = $bdd->prepare("
                INSERT INTO ligne_commande_fournisseur (id_commande_fournisseur, id_produit, quantite, prix_unitaire) 
                VALUES (?, ?, ?, ?)
            ");
            foreach ($lignes as $ligne) {
                $reqLigne->execute([$id_commande, $ligne['id_produit'], $ligne['quantite'], $ligne['prix_unitaire']]);
            }

            // Enregistrement du mouvement de trésorerie
            $reqMouv = $bdd->prepare("
                INSERT INTO mouvement_tresorerie 
                (id_buvette, type_mouvement, montant, categorie, description, id_utilisateur, id_commande_fournisseur) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $reqMouv->execute([$id_buvette, 'Sortie', $montant_total, 'Achat fournisseur', "Commande $numero_commande", $id_utilisateur, $id_commande]);

            $reqSolde = $bdd->prepare("
                UPDATE tresorerie 
                SET solde_actuel = solde_actuel - ? 
                WHERE id_buvette = ?
            ");
            $reqSolde->execute([$montant_total, $id_buvette]);

            $bdd->commit();
            return $id_commande;
        } catch (Exception $e) {
            $bdd->rollBack();
            throw $e;
        }
    }

    public function getCommandesFournisseur($id_buvette, $statut = null) {
        $bdd = Connexion::getBdd();
        $sql = "
            SELECT cf.*, f.nom_fournisseur, u.nom, u.prenom
            FROM commande_fournisseur cf
            INNER JOIN fournisseur f ON cf.id_fournisseur = f.id_fournisseur
            INNER JOIN utilisateur u ON cf.id_utilisateur = u.id_utilisateur
            WHERE cf.id_buvette = ?
        ";

        if ($statut) {
            $sql .= " AND cf.statut = ?";
            $req = $bdd->prepare($sql . " ORDER BY cf.date_commande DESC");
            $req->execute([$id_buvette, $statut]);
        } else {
            $req = $bdd->prepare($sql . " ORDER BY cf.date_commande DESC");
            $req->execute([$id_buvette]);
        }

        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetailsCommandeFournisseur($id_commande) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT cf.*, f.nom_fournisseur, f.email, f.telephone, u.nom, u.prenom
            FROM commande_fournisseur cf
            INNER JOIN fournisseur f ON cf.id_fournisseur = f.id_fournisseur
            INNER JOIN utilisateur u ON cf.id_utilisateur = u.id_utilisateur
            WHERE cf.id_commande_fournisseur = ?
        ");
        $req->execute([$id_commande]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function getLignesCommandeFournisseur($id_commande) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT lcf.*, p.nom_produit, p.image_produit
            FROM ligne_commande_fournisseur lcf
            INNER JOIN produit p ON lcf.id_produit = p.id_produit
            WHERE lcf.id_commande_fournisseur = ?
        ");
        $req->execute([$id_commande]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validerLivraisonCommande($id_commande, $id_buvette) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $req1 = $bdd->prepare("
                UPDATE commande_fournisseur 
                SET statut = 'Livrée', date_livraison_reelle = CURDATE() 
                WHERE id_commande_fournisseur = ?
            ");
            $req1->execute([$id_commande]);

            $lignes = $this->getLignesCommandeFournisseur($id_commande);

            $reqInv = $bdd->prepare("SELECT id_inventaire FROM concerner WHERE id_buvette = ? LIMIT 1");
            $reqInv->execute([$id_buvette]);
            $id_inventaire = $reqInv->fetchColumn();

            $reqStock = $bdd->prepare("
                UPDATE contient 
                SET quantite = quantite + ? 
                WHERE id_inventaire = ? AND id_produit = ?
            ");

            foreach ($lignes as $ligne) {
                $reqStock->execute([$ligne['quantite'], $id_inventaire, $ligne['id_produit']]);
            }

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            throw $e;
        }
    }

    public function changerStatutCommande($id_commande, $nouveau_statut) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("UPDATE commande_fournisseur SET statut = ? WHERE id_commande_fournisseur = ?");
        return $req->execute([$nouveau_statut, $id_commande]);
    }

    public function getTresorerie($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT * FROM tresorerie WHERE id_buvette = ?");
        $req->execute([$id_buvette]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function ajouterMouvementTresorerie($id_buvette, $type, $montant, $categorie, $description, $id_utilisateur, $id_commande_fournisseur = null, $id_commande = null) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $req = $bdd->prepare("
                INSERT INTO mouvement_tresorerie 
                (id_buvette, type_mouvement, montant, categorie, description, id_utilisateur, id_commande_fournisseur, id_commande) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $req->execute([$id_buvette, $type, $montant, $categorie, $description, $id_utilisateur, $id_commande_fournisseur, $id_commande]);

            $operateur = ($type === 'Entrée') ? '+' : '-';
            $reqSolde = $bdd->prepare("
                UPDATE tresorerie 
                SET solde_actuel = solde_actuel $operateur ? 
                WHERE id_buvette = ?
            ");
            $reqSolde->execute([$montant, $id_buvette]);

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            throw $e;
        }
    }

    public function getMouvementsTresorerie($id_buvette, $limit = 50) {
        $bdd = Connexion::getBdd();
        $limit = (int)$limit;
        $req = $bdd->prepare("
            SELECT mt.*, u.nom, u.prenom
            FROM mouvement_tresorerie mt
            INNER JOIN utilisateur u ON mt.id_utilisateur = u.id_utilisateur
            WHERE mt.id_buvette = ?
            ORDER BY mt.date_mouvement DESC
            LIMIT $limit
        ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatsTresorerie($id_buvette, $periode = 30) {
        $bdd = Connexion::getBdd();
        $date_debut = date('Y-m-d', strtotime("-$periode days"));

        $req = $bdd->prepare("
            SELECT 
                SUM(CASE WHEN type_mouvement = 'Entrée' THEN montant ELSE 0 END) as total_entrees,
                SUM(CASE WHEN type_mouvement = 'Sortie' THEN montant ELSE 0 END) as total_sorties,
                COUNT(*) as nb_mouvements
            FROM mouvement_tresorerie
            WHERE id_buvette = ? AND date_mouvement >= ?
        ");
        $req->execute([$id_buvette, $date_debut]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function getUtilisateurById($id_utilisateur) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
        $req->execute([$id_utilisateur]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function getPointsFidelite($id_utilisateur, $id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT * FROM points_fidelite WHERE id_utilisateur = ? AND id_buvette = ?");
        $req->execute([$id_utilisateur, $id_buvette]);
        $points = $req->fetch(PDO::FETCH_ASSOC);

        if (!$points) {
            $this->initialiserPointsFidelite($id_utilisateur, $id_buvette);
            return $this->getPointsFidelite($id_utilisateur, $id_buvette);
        }

        return $points;
    }

    public function initialiserPointsFidelite($id_utilisateur, $id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            INSERT IGNORE INTO points_fidelite (id_utilisateur, id_buvette, points_actuels, points_total_cumules, palier) 
            VALUES (?, ?, 0, 0, 'Bronze')
        ");
        return $req->execute([$id_utilisateur, $id_buvette]);
    }

    public function ajouterPoints($id_utilisateur, $id_buvette, $points, $description, $id_commande = null) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $req = $bdd->prepare("
                UPDATE points_fidelite 
                SET points_actuels = points_actuels + ?, 
                    points_total_cumules = points_total_cumules + ?
                WHERE id_utilisateur = ? AND id_buvette = ?
            ");
            $req->execute([$points, $points, $id_utilisateur, $id_buvette]);

            $reqHist = $bdd->prepare("
                INSERT INTO historique_points (id_utilisateur, id_buvette, type_mouvement, points, description, id_commande) 
                VALUES (?, ?, 'Gain', ?, ?, ?)
            ");
            $reqHist->execute([$id_utilisateur, $id_buvette, $points, $description, $id_commande]);

            $this->mettreAJourPalier($id_utilisateur, $id_buvette);

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            throw $e;
        }
    }

    public function utiliserPoints($id_utilisateur, $id_buvette, $points, $description, $id_commande = null) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            $pointsActuels = $this->getPointsFidelite($id_utilisateur, $id_buvette);
            if ($pointsActuels['points_actuels'] < $points) {
                throw new Exception("Points insuffisants");
            }

            $req = $bdd->prepare("
                UPDATE points_fidelite 
                SET points_actuels = points_actuels - ?
                WHERE id_utilisateur = ? AND id_buvette = ?
            ");
            $req->execute([$points, $id_utilisateur, $id_buvette]);

            $reqHist = $bdd->prepare("
                INSERT INTO historique_points (id_utilisateur, id_buvette, type_mouvement, points, description, id_commande) 
                VALUES (?, ?, 'Utilisation', ?, ?, ?)
            ");
            $reqHist->execute([$id_utilisateur, $id_buvette, -$points, $description, $id_commande]);

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            throw $e;
        }
    }

    public function mettreAJourPalier($id_utilisateur, $id_buvette) {
        $bdd = Connexion::getBdd();
        $points = $this->getPointsFidelite($id_utilisateur, $id_buvette);
        $total = $points['points_total_cumules'];

        $nouveau_palier = 'Bronze';
        if ($total >= 1500) {
            $nouveau_palier = 'Or';
        } elseif ($total >= 500) {
            $nouveau_palier = 'Argent';
        }

        $req = $bdd->prepare("UPDATE points_fidelite SET palier = ? WHERE id_utilisateur = ? AND id_buvette = ?");
        return $req->execute([$nouveau_palier, $id_utilisateur, $id_buvette]);
    }

    public function getHistoriquePoints($id_utilisateur, $id_buvette, $limit = 50) {
        $bdd = Connexion::getBdd();
        $limit = (int)$limit;
        $req = $bdd->prepare("
            SELECT * FROM historique_points 
            WHERE id_utilisateur = ? AND id_buvette = ? 
            ORDER BY date_mouvement DESC 
            LIMIT $limit
        ");
        $req->execute([$id_utilisateur, $id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReductionPalier($palier) {
        $reductions = [
            'Bronze' => 0,
            'Argent' => 5,
            'Or' => 10
        ];
        return $reductions[$palier] ?? 0;
    }

    public function getAllClientsAvecPoints($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT u.*, pf.points_actuels, pf.points_total_cumules, pf.palier
            FROM utilisateur u
            INNER JOIN points_fidelite pf ON u.id_utilisateur = pf.id_utilisateur
            WHERE pf.id_buvette = ?
            ORDER BY pf.points_total_cumules DESC
        ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStocksDetailles($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT 
                p.id_produit,
                p.nom_produit,
                p.prix_produit,
                p.type_produit,
                p.image_produit,
                c.quantite,
                c.seuil_alerte,
                (c.quantite * p.prix_produit) as valeur_stock,
                CASE 
                    WHEN c.quantite <= 0 THEN 'Rupture'
                    WHEN c.quantite <= c.seuil_alerte THEN 'Alerte'
                    ELSE 'OK'
                END as statut
            FROM produit p
            INNER JOIN contient c ON p.id_produit = c.id_produit
            INNER JOIN concerner co ON c.id_inventaire = co.id_inventaire
            WHERE co.id_buvette = ?
            ORDER BY statut DESC, p.type_produit ASC, p.nom_produit ASC
        ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoriqueChangementsStock($id_buvette, $limit = 50) {
        $bdd = Connexion::getBdd();
        $limit = (int)$limit;
        $req = $bdd->prepare("
            SELECT 
                cs.*,
                p.nom_produit,
                u.nom,
                u.prenom
            FROM changement_stock cs
            INNER JOIN produit p ON cs.id_produit = p.id_produit
            INNER JOIN utilisateur u ON cs.id_utilisateur = u.id_utilisateur
            WHERE cs.id_buvette = ?
            ORDER BY cs.date_changement DESC
            LIMIT $limit
        ");
        $req->execute([$id_buvette]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatsInventaire($id_buvette) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("
            SELECT 
                COUNT(DISTINCT c.id_produit) as total_produits,
                SUM(c.quantite) as total_unites,
                SUM(c.quantite * p.prix_produit) as valeur_totale,
                SUM(CASE WHEN c.quantite <= 0 THEN 1 ELSE 0 END) as ruptures,
                SUM(CASE WHEN c.quantite <= c.seuil_alerte AND c.quantite > 0 THEN 1 ELSE 0 END) as alertes,
                (SELECT COUNT(*) FROM changement_stock WHERE id_buvette = ? AND date_changement >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as mouvements_30j
            FROM contient c
            INNER JOIN produit p ON c.id_produit = p.id_produit
            INNER JOIN concerner co ON c.id_inventaire = co.id_inventaire
            WHERE co.id_buvette = ?
        ");
        $req->execute([$id_buvette, $id_buvette]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function ajusterStock($id_buvette, $id_produit, $type, $quantite, $commentaire, $id_utilisateur) {
        $bdd = Connexion::getBdd();
        try {
            $bdd->beginTransaction();

            // Enregistrer le changement dans l'historique
            $req = $bdd->prepare("
                INSERT INTO changement_stock 
                (type_changement, quantite, date_changement, commentaire, id_utilisateur, id_produit, id_buvette) 
                VALUES (?, ?, CURDATE(), ?, ?, ?, ?)
            ");
            $req->execute([$type, $quantite, $commentaire, $id_utilisateur, $id_produit, $id_buvette]);

            // Mettre à jour le stock
            $operateur = ($type === 'Entrée') ? '+' : '-';
            $reqStock = $bdd->prepare("
                UPDATE contient c
                INNER JOIN concerner co ON c.id_inventaire = co.id_inventaire
                SET c.quantite = GREATEST(0, c.quantite $operateur ?)
                WHERE c.id_produit = ? AND co.id_buvette = ?
            ");
            $reqStock->execute([$quantite, $id_produit, $id_buvette]);

            $bdd->commit();
            return true;
        } catch (Exception $e) {
            $bdd->rollBack();
            throw $e;
        }
    }

    public function modifierProduitSansStock($id_produit, $nouveau_prix, $nouvelle_description, $nouveau_type) {
        $bdd = Connexion::getBdd();
        try {
            $req = $bdd->prepare("UPDATE produit SET prix_produit = ?, description = ?, type_produit = ? WHERE id_produit = ?");
            $req->execute([$nouveau_prix, $nouvelle_description, $nouveau_type, $id_produit]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}