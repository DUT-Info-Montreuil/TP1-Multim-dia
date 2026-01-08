
<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleSuperAdmin {
    private $bdd;

    public function __construct() {
        $this->bdd = Connexion::getBdd();
    }

    public function getStatistiques() {
        $stats = [];

        // Nombre total de buvettes
        $stmt = $this->bdd->query("SELECT COUNT(*) as total FROM une_buvette WHERE archivee IS NULL");
        $stats['total_buvettes'] = $stmt->fetch()['total'];

        // Nombre de gestionnaires (utilisateurs avec le rôle gestionnaire actif)
        $stmt = $this->bdd->query("
        SELECT COUNT(DISTINCT a.id_utilisateur) as total 
        FROM affecter a 
        JOIN role_utilisateur r ON a.id_role = r.id_role 
        WHERE r.nom_role = 'gestionnaire' 
        AND (a.date_fin IS NULL OR a.date_fin >= CURDATE())
    ");
        $stats['total_gestionnaires'] = $stmt->fetch()['total'];

        // Nombre d'utilisateurs standard (utilisateurs sans rôle actif)
        $stmt = $this->bdd->query("
        SELECT COUNT(*) as total 
        FROM utilisateur u 
        WHERE NOT EXISTS (
            SELECT 1 
            FROM affecter a 
            WHERE a.id_utilisateur = u.id_utilisateur 
            AND (a.date_fin IS NULL OR a.date_fin >= CURDATE())
        )
    ");
        $stats['total_utilisateurs'] = $stmt->fetch()['total'];

        // Buvettes ouvertes
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
        SELECT b.*, 
               u.nom as gestionnaire_nom, 
               u.prenom as gestionnaire_prenom,
               u.id_utilisateur as gestionnaire_id
        FROM une_buvette b 
        LEFT JOIN affecter a ON b.id_buvette = a.id_buvette
        LEFT JOIN utilisateur u ON a.id_utilisateur = u.id_utilisateur
        LEFT JOIN role_utilisateur r ON a.id_role = r.id_role
        WHERE b.archivee IS NULL
        AND r.nom_role = 'gestionnaire'
        AND (a.date_fin IS NULL OR a.date_fin >= CURDATE())
        ORDER BY b.nom
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function modifierBuvette($id, $nom, $description) {
        try {
            $stmt = $this->bdd->prepare("
                UPDATE une_buvette 
                SET nom = ?, description = ? 
                WHERE id_buvette = ?
            ");
            return $stmt->execute([$nom, $description, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function ajouterJournalActivite($action, $cible, $details = null) {
        try {
            // Récupérer l'ID de l'utilisateur connecté depuis la session
            $id_utilisateur = isset($_SESSION['id_utilisateur']) ? $_SESSION['id_utilisateur'] : null;

            if (!$id_utilisateur) {
                // Si pas d'utilisateur en session, log l'erreur
                error_log("Erreur journal: Aucun utilisateur en session");
                return false;
            }

            $stmt = $this->bdd->prepare("
            INSERT INTO journal_activite (action, cible, details, id_utilisateur, horodatage) 
            VALUES (:action, :cible, :details, :id_utilisateur, NOW())
        ");

            return $stmt->execute([
                ':action' => $action,
                ':cible' => $cible,
                ':details' => $details,
                ':id_utilisateur' => $id_utilisateur
            ]);

        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout au journal: " . $e->getMessage());
            return false;
        }
    }

    public function getJournalActivite($limit = 100) {
        try {
            $stmt = $this->bdd->prepare("
            SELECT ja.*, u.nom, u.prenom 
            FROM journal_activite ja
            JOIN utilisateur u ON ja.id_utilisateur = u.id_utilisateur
            ORDER BY ja.horodatage DESC
            LIMIT :limit
        ");

            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du journal: " . $e->getMessage());
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
            error_log("Erreur lors de l'archivage: " . $e->getMessage());
            return false;
        }
    }
}
?>