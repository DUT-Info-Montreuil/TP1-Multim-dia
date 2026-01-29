
<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleSuperAdmin {
    private $bdd;

    public function __construct() {
        $this->bdd = Connexion::getBdd();
    }

    public function getStatistiques() {
        $stats = [];

        $stmt = $this->bdd->query("SELECT COUNT(*) as total FROM une_buvette");
        $stats['total_buvettes'] = $stmt->fetch()['total'];

        $stmt = $this->bdd->query("
        SELECT COUNT(DISTINCT a.id_utilisateur) as total 
        FROM affecter a 
        JOIN role_utilisateur r ON a.id_role = r.id_role 
        WHERE r.nom_role = 'gestionnaire' 
        AND a.date_debut <= CURDATE()
        AND (a.date_fin IS NULL OR a.date_fin > CURDATE()) 
        ");
        $stats['total_gestionnaires'] = $stmt->fetch()['total'];

        $stmt = $this->bdd->query("
        SELECT COUNT(*) as total 
        FROM utilisateur u 
        WHERE NOT EXISTS (
            SELECT 1 
            FROM affecter a 
            WHERE a.id_utilisateur = u.id_utilisateur 
            AND a.date_debut <= CURDATE()
            AND (a.date_fin IS NULL OR a.date_fin > CURDATE())
        )
        ");
        $stats['total_utilisateurs'] = $stmt->fetch()['total'];

        $stmt = $this->bdd->query("
        SELECT COUNT(*) as total 
        FROM une_buvette 
        WHERE est_ouverte = 1
    ");
        $stats['buvettes_ouvertes'] = $stmt->fetch()['total'];

        return $stats;
    }

    public function getDemandesCreation() {
        try {
            $stmt = $this->bdd->prepare("
            SELECT dc.*, 
                   u.nom as demandeur_nom, 
                   u.prenom as demandeur_prenom, 
                   u.email as demandeur_email,
                   dc.fichier_statuts,
                   dc.fichier_pv_ag,
                   dc.fichier_cnid
            FROM demande_creation_buvette dc
            JOIN utilisateur u ON dc.id_utilisateur = u.id_utilisateur
            WHERE dc.statut = 'En attente'
            ORDER BY dc.date_demande DESC
        ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération demandes création: " . $e->getMessage());
            return [];
        }
    }

    public function getDemandeCreationById($id_demande) {
        try {
            $stmt = $this->bdd->prepare("
            SELECT dc.*, 
                   u.nom as demandeur_nom, 
                   u.prenom as demandeur_prenom, 
                   u.email as demandeur_email,
                   dc.fichier_statuts,
                   dc.fichier_pv_ag,
                   dc.fichier_cnid
            FROM demande_creation_buvette dc
            JOIN utilisateur u ON dc.id_utilisateur = u.id_utilisateur
            WHERE dc.id_demande = ?
        ");
            $stmt->execute([$id_demande]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération demande par ID: " . $e->getMessage());
            return null;
        }
    }
    public function getBuvettes() {
        $stmt = $this->bdd->prepare("
        SELECT 
            b.*,
            GROUP_CONCAT(DISTINCT CONCAT(u.nom, ' ', u.prenom) SEPARATOR ', ') as gestionnaires_noms,
            GROUP_CONCAT(DISTINCT u.email SEPARATOR ', ') as gestionnaires_emails,
            GROUP_CONCAT(DISTINCT u.id_utilisateur SEPARATOR ', ') as gestionnaires_ids
        FROM une_buvette b 
        LEFT JOIN (
            SELECT a.id_buvette, a.id_utilisateur
            FROM affecter a
            INNER JOIN role_utilisateur r ON a.id_role = r.id_role
            WHERE r.nom_role = 'gestionnaire'
            AND a.date_debut <= CURDATE()
            AND (a.date_fin IS NULL OR a.date_fin > CURDATE())
        ) gestion_actuelle ON b.id_buvette = gestion_actuelle.id_buvette
        LEFT JOIN utilisateur u ON gestion_actuelle.id_utilisateur = u.id_utilisateur
        WHERE b.archivee IS NULL
        GROUP BY b.id_buvette
        ORDER BY b.nom
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function modifierBuvette($id, $nom, $description, $est_ouverte) {
        try {
            $stmt = $this->bdd->prepare("
                    UPDATE une_buvette 
                    SET nom = ?, description = ?, est_ouverte = ?
                    WHERE id_buvette = ?
                ");
            return $stmt->execute([$nom, $description, $est_ouverte, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function ajouterJournalActivite($action, $cible, $details = null) {
        try {
            $stmt = $this->bdd->prepare("
            INSERT INTO journal_activite (action, cible, details, id_utilisateur, horodatage) 
            VALUES (:action, :cible, :details, :id_utilisateur, NOW())
        ");

            return $stmt->execute([
                ':action' => $action,
                ':cible' => $cible,
                ':details' => $details,
                ':id_utilisateur' => $_SESSION['user']['id_utilisateur']
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    public function getJournalActivite($filtres = null) {
        try {
            $query = "
            SELECT ja.*, u.email
            FROM journal_activite ja
            JOIN utilisateur u ON ja.id_utilisateur = u.id_utilisateur
            WHERE 1=1
            ";

            $params = [];

            // Appliquer les filtres
            if ($filtres) {
                // Filtre par action
                if (!empty($filtres['action'])) {
                    $query .= " AND ja.action = :action";
                    $params[':action'] = $filtres['action'];
                }

                // Filtre par administrateur (email)
                if (!empty($filtres['administrateur'])) {
                    $query .= " AND u.email = :email";
                    $params[':email'] = $filtres['administrateur'];
                }

                // Filtre par date prédéfinie
                if (!empty($filtres['date'])) {
                    switch ($filtres['date']) {
                        case 'today':
                            $query .= " AND DATE(ja.horodatage) = CURDATE()";
                            break;
                        case 'yesterday':
                            $query .= " AND DATE(ja.horodatage) = CURDATE() - INTERVAL 1 DAY";
                            break;
                        case 'last_7_days':
                            $query .= " AND ja.horodatage >= CURDATE() - INTERVAL 7 DAY";
                            break;
                        case 'last_30_days':
                            $query .= " AND ja.horodatage >= CURDATE() - INTERVAL 30 DAY";
                            break;
                        case 'this_month':
                            $query .= " AND MONTH(ja.horodatage) = MONTH(CURDATE()) 
                                      AND YEAR(ja.horodatage) = YEAR(CURDATE())";
                            break;
                        case 'last_month':
                            $query .= " AND MONTH(ja.horodatage) = MONTH(CURDATE() - INTERVAL 1 MONTH) 
                                      AND YEAR(ja.horodatage) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
                            break;
                    }
                }

                // Filtre par plage de dates personnalisée
                if (!empty($filtres['date_debut'])) {
                    $query .= " AND DATE(ja.horodatage) >= :date_debut";
                    $params[':date_debut'] = $filtres['date_debut'];
                }

                if (!empty($filtres['date_fin'])) {
                    $query .= " AND DATE(ja.horodatage) <= :date_fin";
                    $params[':date_fin'] = $filtres['date_fin'];
                }
            }

            $query .= " ORDER BY ja.horodatage DESC LIMIT 100";

            $stmt = $this->bdd->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du journal d'activité: " . $e->getMessage());
            return [];
        }
    }

    public function archiverBuvette($id_buvette, $raison_archivage = null) {
        try {
            error_log("DEBUG: Tentative d'archivage de la buvette ID: $id_buvette");

            $this->bdd->beginTransaction();

            // 1. DÉSACTIVER LES CONTRAINTES DE CLÉ ÉTRANGÈRE
            $this->bdd->exec("SET FOREIGN_KEY_CHECKS = 0");

            $stmt = $this->bdd->prepare("
            SELECT * FROM une_buvette 
            WHERE id_buvette = :id
        ");
            $stmt->execute([':id' => $id_buvette]);
            $buvette = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$buvette) {
                error_log("DEBUG: Buvette non trouvée ID: $id_buvette");
                $this->bdd->exec("SET FOREIGN_KEY_CHECKS = 1");
                $this->bdd->rollBack();
                return false;
            }

            error_log("DEBUG: Buvette trouvée: " . $buvette['nom']);

            $stmt = $this->bdd->prepare("
            INSERT INTO buvette_archivee 
            (id_buvette_origine, nom, description, est_ouverte, image_buvette, 
             archive_par_utilisateur, raison_archivage, date_archivage)
            VALUES (:id_origine, :nom, :description, :est_ouverte, :image_buvette,
                    :archive_par, :raison, NOW())
        ");

            $result = $stmt->execute([
                ':id_origine' => $buvette['id_buvette'],
                ':nom' => $buvette['nom'],
                ':description' => $buvette['description'],
                ':est_ouverte' => $buvette['est_ouverte'],
                ':image_buvette' => $buvette['image_buvette'],
                ':archive_par' => $_SESSION['user']['id_utilisateur'],
                ':raison' => $raison_archivage
            ]);

            if (!$result) {
                error_log("DEBUG: Échec INSERT dans buvette_archivee");
                $this->bdd->exec("SET FOREIGN_KEY_CHECKS = 1");
                $this->bdd->rollBack();
                return false;
            }

            error_log("DEBUG: Archivage réussi dans buvette_archivee");

            $stmt = $this->bdd->prepare("DELETE FROM mouvement_tresorerie WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("DELETE FROM commande WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("
            DELETE lc FROM ligne_commande lc
            JOIN commande c ON lc.id_commande = c.id_commande
            WHERE c.id_buvette = :id
        ");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("DELETE FROM affecter WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("DELETE FROM adhesion WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("DELETE FROM points_fidelite WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("DELETE FROM solde WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("
            DELETE c FROM contient c
            JOIN concerner cn ON c.id_inventaire = cn.id_inventaire
            WHERE cn.id_buvette = :id
        ");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("DELETE FROM concerner WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("DELETE FROM changement_stock WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("DELETE FROM tresorerie WHERE id_buvette = :id");
            $stmt->execute([':id' => $id_buvette]);

            $stmt = $this->bdd->prepare("
            DELETE FROM une_buvette 
            WHERE id_buvette = :id
        ");
            $result = $stmt->execute([':id' => $id_buvette]);

            if (!$result) {
                error_log("DEBUG: Échec DELETE FROM une_buvette");
                $this->bdd->exec("SET FOREIGN_KEY_CHECKS = 1");
                $this->bdd->rollBack();
                return false;
            }

            error_log("DEBUG: Suppression réussie de une_buvette");

            $this->bdd->exec("SET FOREIGN_KEY_CHECKS = 1");

            // 7. Valider la transaction
            $this->bdd->commit();

            $this->ajouterJournalActivite(
                'Archivage buvette',
                'Buvette ID: ' . $id_buvette . ' - ' . $buvette['nom'],
                'Archivée définitivement. Raison: ' . ($raison_archivage ?? 'Non spécifiée')
            );

            error_log("DEBUG: Archivage complet réussi");
            return true;

        } catch (PDOException $e) {
            if ($this->bdd->inTransaction()) {
                $this->bdd->rollBack();
            }
            $this->bdd->exec("SET FOREIGN_KEY_CHECKS = 1");
            error_log("ARCHIVAGE ERREUR [Buvette $id_buvette]: " . $e->getMessage());
            return false;
        }
    }
    public function getGestionnaires() {
        try {
            $stmt = $this->bdd->prepare("
            SELECT DISTINCT u.id_utilisateur, u.nom, u.prenom, u.email,
                   b.nom as buvette_nom, b.id_buvette,
                   a.date_debut, a.date_fin
            FROM utilisateur u
            INNER JOIN affecter a ON u.id_utilisateur = a.id_utilisateur
            INNER JOIN role_utilisateur r ON a.id_role = r.id_role
            LEFT JOIN une_buvette b ON a.id_buvette = b.id_buvette
            WHERE r.nom_role = 'gestionnaire'
            AND a.date_debut <= CURDATE()
            AND (a.date_fin IS NULL OR a.date_fin > CURDATE()) 
            AND b.archivee IS NULL
            ORDER BY u.nom, u.prenom
        ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUtilisateursSansRole() {
        try {
            $stmt = $this->bdd->prepare("
        SELECT u.id_utilisateur, u.nom, u.prenom, u.email
        FROM utilisateur u
        WHERE NOT EXISTS (
            SELECT 1 
            FROM affecter a 
            WHERE a.id_utilisateur = u.id_utilisateur 
            AND a.date_debut <= CURDATE()
            AND (a.date_fin IS NULL OR a.date_fin > CURDATE())
        )
        ORDER BY u.nom, u.prenom
    ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getBuvettesSansGestionnaire() {
        try {
            $stmt = $this->bdd->prepare("
        SELECT b.id_buvette, b.nom
        FROM une_buvette b
        WHERE b.archivee IS NULL
        AND NOT EXISTS (
            SELECT 1 
            FROM affecter a 
            JOIN role_utilisateur r ON a.id_role = r.id_role
            WHERE a.id_buvette = b.id_buvette
            AND r.nom_role = 'gestionnaire'
            AND a.date_debut <= CURDATE()
            AND (a.date_fin IS NULL OR a.date_fin > CURDATE())
        )
        ORDER BY b.nom
    ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    public function attribuerRoleGestionnaire($id_utilisateur, $id_buvette) {
        try {
            $stmt = $this->bdd->prepare("SELECT id_role FROM role_utilisateur WHERE nom_role = 'gestionnaire'");
            $stmt->execute();
            $role = $stmt->fetch();

            if (!$role) {
                $stmt = $this->bdd->prepare("INSERT INTO role_utilisateur (nom_role) VALUES ('gestionnaire')");
                $stmt->execute();
                $id_role = $this->bdd->lastInsertId();
            } else {
                $id_role = $role['id_role'];
            }

            $stmt = $this->bdd->prepare("
            SELECT id_utilisateur 
            FROM affecter 
            WHERE id_role = ? 
            AND id_buvette = ?
            AND date_debut <= CURDATE()
            AND (date_fin IS NULL OR date_fin > CURDATE())
        ");
            $stmt->execute([$id_role, $id_buvette]);
            $existingGestionnaire = $stmt->fetch();

            if ($existingGestionnaire) {
                return false;
            }

            $stmt = $this->bdd->prepare("
            SELECT id_buvette 
            FROM affecter 
            WHERE id_role = ? 
            AND id_utilisateur = ?
            AND date_debut <= CURDATE()
            AND (date_fin IS NULL OR date_fin > CURDATE())
        ");
            $stmt->execute([$id_role, $id_utilisateur]);
            $existingBuvette = $stmt->fetch();

            if ($existingBuvette) {
                $stmt = $this->bdd->prepare("
                UPDATE affecter 
                SET date_fin = CURDATE() - INTERVAL 1 DAY
                WHERE id_role = ? 
                AND id_utilisateur = ?
                AND id_buvette = ?
                AND (date_fin IS NULL OR date_fin > CURDATE())
            ");
                $stmt->execute([$id_role, $id_utilisateur, $existingBuvette['id_buvette']]);
            }

            $stmt = $this->bdd->prepare("
            SELECT 1 
            FROM affecter 
            WHERE id_role = ? 
            AND id_utilisateur = ? 
            AND id_buvette = ?
        ");
            $stmt->execute([$id_role, $id_utilisateur, $id_buvette]);

            if ($stmt->fetch()) {
                $stmt = $this->bdd->prepare("
                UPDATE affecter 
                SET date_debut = CURDATE(), 
                    date_fin = NULL 
                WHERE id_role = ? 
                AND id_utilisateur = ? 
                AND id_buvette = ?
            ");
                return $stmt->execute([$id_role, $id_utilisateur, $id_buvette]);
            } else {
                $stmt = $this->bdd->prepare("
                INSERT INTO affecter (id_role, id_utilisateur, id_buvette, date_debut, date_fin)
                VALUES (:id_role, :id_utilisateur, :id_buvette, CURDATE(), NULL)
            ");
                return $stmt->execute([
                    ':id_role' => $id_role,
                    ':id_utilisateur' => $id_utilisateur,
                    ':id_buvette' => $id_buvette
                ]);
            }

        } catch (PDOException $e) {
            return false;
        }
    }

    public function modifierAffectationGestionnaire($id_utilisateur, $id_buvette) {
        try {
            $stmt = $this->bdd->prepare("SELECT id_role FROM role_utilisateur WHERE nom_role = 'gestionnaire'");
            $stmt->execute();
            $role = $stmt->fetch();

            if (!$role) {
                return false;
            }
            $id_role = $role['id_role'];

            if (empty($id_buvette)) {
                $stmt = $this->bdd->prepare("
                UPDATE affecter 
                SET date_fin = CURDATE() - INTERVAL 1 DAY
                WHERE id_utilisateur = ?
                AND id_role = ?
                AND (date_fin IS NULL OR date_fin > CURDATE())
            ");
                return $stmt->execute([$id_utilisateur, $id_role]);
            } else {
                $stmt = $this->bdd->prepare("
                SELECT id_utilisateur 
                FROM affecter 
                WHERE id_role = ? 
                AND id_buvette = ?
                AND date_debut <= CURDATE()
                AND (date_fin IS NULL OR date_fin > CURDATE())
                AND id_utilisateur != ?
            ");
                $stmt->execute([$id_role, $id_buvette, $id_utilisateur]);

                if ($stmt->fetch()) {
                    return false;
                }

                $stmt = $this->bdd->prepare("
                UPDATE affecter 
                SET id_buvette = ?,
                    date_debut = CURDATE()
                WHERE id_utilisateur = ?
                AND id_role = ?
                AND (date_fin IS NULL OR date_fin > CURDATE())
            ");
                return $stmt->execute([
                    $id_buvette,
                    $id_utilisateur,
                    $id_role
                ]);
            }

        } catch (PDOException $e) {
            return false;
        }
    }
    public function retirerRoleGestionnaire($id_utilisateur) {
        try {
            $stmt = $this->bdd->prepare("
        UPDATE affecter a
        JOIN role_utilisateur r ON a.id_role = r.id_role
        SET a.date_fin = CURDATE() - INTERVAL 1 DAY  
        WHERE a.id_utilisateur = :id_utilisateur
        AND r.nom_role = 'gestionnaire'
        AND a.date_debut <= CURDATE()
        AND (a.date_fin IS NULL OR a.date_fin > CURDATE())
    ");

            $result = $stmt->execute([':id_utilisateur' => $id_utilisateur]);

            return $result;

        } catch (PDOException $e) {
            return false;
        }
    }
    public function getUtilisateurById($id_utilisateur) {
        try {
            $stmt = $this->bdd->prepare("
            SELECT id_utilisateur, nom, prenom, email 
            FROM utilisateur 
            WHERE id_utilisateur = :id_utilisateur
        ");
            $stmt->execute([':id_utilisateur' => $id_utilisateur]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getBuvetteById($id_buvette) {
        try {
            $stmt = $this->bdd->prepare("
            SELECT
                b.*,
                u.nom as gestionnaire_nom,
                u.prenom as gestionnaire_prenom,
                u.id_utilisateur as gestionnaire_id
            FROM une_buvette b
            LEFT JOIN (
                SELECT a.id_buvette, a.id_utilisateur
                FROM affecter a
                INNER JOIN role_utilisateur r ON a.id_role = r.id_role
                WHERE r.nom_role = 'gestionnaire'
                AND a.date_debut <= CURDATE()
                AND (a.date_fin IS NULL OR a.date_fin > CURDATE())
            ) gestion_actuelle ON b.id_buvette = gestion_actuelle.id_buvette
            LEFT JOIN utilisateur u ON gestion_actuelle.id_utilisateur = u.id_utilisateur
            WHERE b.id_buvette = :id
            LIMIT 1
        ");
            $stmt->execute([':id' => $id_buvette]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: false;

        } catch (PDOException $e) {
            error_log("Erreur récupération buvette par ID: " . $e->getMessage());
            return false;
        }
    }
    public function creerBuvette($nom, $description) {
        try {
            $stmt = $this->bdd->prepare("
                INSERT INTO une_buvette (nom, description, est_ouverte, archivee) 
                VALUES (:nom, :description, 0, NULL)
            ");

            return $stmt->execute([
                ':nom' => $nom,
                ':description' => $description
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    public function validerDemandeCreation($id_demande) {
        try {
            // Récupérer les informations de la demande
            $stmt = $this->bdd->prepare("
            SELECT nom_buvette, description, id_utilisateur 
            FROM demande_creation_buvette 
            WHERE id_demande = ? AND statut = 'En attente'
        ");
            $stmt->execute([$id_demande]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$demande) {
                return false;
            }

            // Commencer une transaction
            $this->bdd->beginTransaction();

            // 1. Créer la buvette
            $stmt = $this->bdd->prepare("
            INSERT INTO une_buvette (nom, description, est_ouverte, archivee) 
            VALUES (:nom, :description, 0, NULL)
        ");
            $stmt->execute([
                ':nom' => $demande['nom_buvette'],
                ':description' => $demande['description']
            ]);

            $id_buvette = $this->bdd->lastInsertId();

            // 2. Attribuer automatiquement le demandeur comme gestionnaire
            $id_role = $this->getRoleGestionnaireId();

            if ($id_role) {
                $stmt = $this->bdd->prepare("
                INSERT INTO affecter (id_role, id_utilisateur, id_buvette, date_debut, date_fin)
                VALUES (:id_role, :id_utilisateur, :id_buvette, CURDATE(), NULL)
            ");
                $stmt->execute([
                    ':id_role' => $id_role,
                    ':id_utilisateur' => $demande['id_utilisateur'],
                    ':id_buvette' => $id_buvette
                ]);
            }

            // 3. Mettre à jour le statut de la demande
            $stmt = $this->bdd->prepare("
            UPDATE demande_creation_buvette 
            SET statut = 'Validée', raison_refus = NULL 
            WHERE id_demande = ?
        ");
            $stmt->execute([$id_demande]);

            // Valider la transaction
            $this->bdd->commit();

            return true;

        } catch (PDOException $e) {
            // Annuler en cas d'erreur
            $this->bdd->rollBack();
            return false;
        }
    }

    public function rejeterDemandeCreation($id_demande, $raison_refus) {
        try {
            $stmt = $this->bdd->prepare("
            UPDATE demande_creation_buvette 
            SET statut = 'Refusée', raison_refus = :raison_refus 
            WHERE id_demande = :id_demande AND statut = 'En attente'
        ");

            return $stmt->execute([
                ':raison_refus' => $raison_refus,
                ':id_demande' => $id_demande
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    private function getRoleGestionnaireId() {
        try {
            $stmt = $this->bdd->prepare("SELECT id_role FROM role_utilisateur WHERE nom_role = 'gestionnaire'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result['id_role'];
            } else {
                // Créer le rôle s'il n'existe pas
                $stmt = $this->bdd->prepare("INSERT INTO role_utilisateur (nom_role) VALUES ('gestionnaire')");
                $stmt->execute();
                return $this->bdd->lastInsertId();
            }
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>