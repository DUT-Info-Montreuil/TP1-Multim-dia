<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleCreationBuvette
{
    private $pdo;

    public function __construct() {
        $this->pdo = Connexion::getBdd();
    }
    public function creerDemande($idUser, $nom, $description, $fichierStatuts, $fichierPV, $fichierCNI)
    {
        $sql = "INSERT INTO demande_creation_buvette 
                (nom_buvette, description, fichier_statuts, fichier_pv_ag, fichier_cnid, statut, date_demande, id_utilisateur) 
                VALUES (?, ?, ?, ?, ?, 'En attente', NOW(), ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $nom,
            $description,
            $fichierStatuts,
            $fichierPV,
            $fichierCNI,
            $idUser
        ]);
    }

    public function aDemandeEnCours($idUser)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM demande_creation_buvette WHERE id_utilisateur = ? AND statut = 'En attente'");
        $stmt->execute([$idUser]);
        return $stmt->fetchColumn() > 0;
    }
}
?>