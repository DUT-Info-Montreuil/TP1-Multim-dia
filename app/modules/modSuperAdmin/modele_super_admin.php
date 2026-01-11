
<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleSuperAdmin {
    private $bdd;

    public function __construct() {
        $this->bdd = Connexion::getBdd();
    }

    public function getStatistiques() {
        $stats = [];

        $stmt = $this->bdd->query("SELECT COUNT(*) as total FROM une_buvette WHERE archivee IS NULL");
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

        $stmt = $this->bdd->query("SELECT COUNT(*) as total FROM une_buvette WHERE est_ouverte = 1 AND archivee IS NULL");
        $stats['buvettes_ouvertes'] = $stmt->fetch()['total'];

        return $stats;
    }

/*    public function getBuvettes() {
        $stmt = $this->bdd->prepare("
            SELECT b.*,
                   u.nom as gestionnaire_nom,
                   u.prenom as gestionnaire_prenom,
                   u.id_utilisateur as gestionnaire_id
            FROM une_buvette b
            LEFT JOIN utilisateur u ON b.id_gestionnaire = u.id_utilisateur
            WHERE b.archivee = 0
            ORDER BY b.nom
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }*/

    public function getBuvettes() {
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
        WHERE b.archivee IS NULL
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

    public function getJournalActivite() {
        try {
            $stmt = $this->bdd->prepare("
            SELECT ja.*, u.email
            FROM journal_activite ja
            JOIN utilisateur u ON ja.id_utilisateur = u.id_utilisateur
            ORDER BY ja.horodatage DESC
        ");

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return [];
        }
    }

    public function archiverBuvette($id_buvette) {
        try {
            $stmt = $this->bdd->prepare("
            UPDATE une_buvette 
            SET archivee = 1 
            WHERE id_buvette = :id
        ");

            $result = $stmt->execute([':id' => $id_buvette]);

            if ($result) {
                $this->ajouterJournalActivite(
                    'Archivage buvette',
                    'Buvette ID: ' . $id_buvette,
                    'Archivée définitivement'
                );
            }

            return $result;

        } catch (PDOException $e) {
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
              AND b.archivee IS NULL
            LIMIT 1
        ");
            $stmt->execute([':id' => $id_buvette]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            return null;
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
}
?>