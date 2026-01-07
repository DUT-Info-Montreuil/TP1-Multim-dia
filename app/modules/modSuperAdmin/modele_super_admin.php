
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

    public function getBuvettes() {
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

}
?>