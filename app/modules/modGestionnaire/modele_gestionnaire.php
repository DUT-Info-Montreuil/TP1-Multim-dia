<?php
require_once __DIR__ . '/../../../connexion.php';

class ModeleGestionnaire {

    public function getListeProduits() {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT id_produit, nom_produit, prix_produit, image_produit FROM Produit");
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduit($id) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("SELECT id_produit, nom_produit, prix_produit, image_produit FROM Produit WHERE id_produit = ?");
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    public function modifierProduit($id, $nom, $prix) {
        $bdd = Connexion::getBdd();
        $req = $bdd->prepare("UPDATE Produit SET nom_produit = ?, prix_produit = ? WHERE id_produit = ?");
        return $req->execute([$nom, $prix, $id]);
    }
}