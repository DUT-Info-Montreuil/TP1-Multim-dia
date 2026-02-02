<?php
require_once 'modele_compte.php';
require_once 'vue_compte.php';

class ContCompte
{
    private $modele;
    private $vue;

    public function __construct()
    {
        $this->modele = new ModeleCompte();
        $this->vue = new VueCompte();
    }

    public function exec()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?module=connexion&action=form_connexion');
            exit;
        }

        $action = isset($_GET['action']) ? $_GET['action'] : 'historique';
        $idUser = $_SESSION['user']['id_utilisateur'];

        switch ($action) {
            case 'validerPaiement':
                $this->traiterValidationPaiement($idUser);
                break;

            case 'refuserPaiement':
                $this->traiterRefusPaiement($idUser);
                break;
            case 'notification':
                $this->vue->afficherNotifications($this->modele->getNotifications($idUser));
                break;
            case 'profil':
                $this->afficherProfil($idUser);
                break;

            case 'modifierEmail':
                $this->traiterModificationEmail($idUser);
                break;

            case 'modifierMdp':
                $this->traiterModificationMdp($idUser);
                break;

            case 'historique':
            default:
                $this->afficherHistorique($idUser);
                break;
        }
    }
    private function traiterValidationPaiement($idUser)
    {
        if (isset($_POST['id_notification'])) {
            $idNotif = (int)$_POST['id_notification'];
            $resultat = $this->modele->payerCommandeNotification($idUser, $idNotif);
            $notifs = $this->modele->getNotifications($idUser);

            if ($resultat === 1) {
                $this->vue->afficherNotifications($notifs, "Paiement validé ! Votre commande est maintenant payée.", "success");
            } elseif ($resultat === 2) {
                $this->vue->afficherNotifications($notifs, "Solde insuffisant pour cette buvette. Veuillez recharger votre compte.", "danger");
            } else {
                $this->vue->afficherNotifications($notifs, "Une erreur est survenue ou la notification n'existe plus.", "danger");
            }
        } else {
            header('Location: index.php?module=compte&action=notification');
        }
    }

    private function traiterRefusPaiement($idUser)
    {
        if (isset($_POST['id_notification'])) {
            $idNotif = (int)$_POST['id_notification'];
            $succes = $this->modele->refuserCommandeNotification($idUser, $idNotif);
            $notifs = $this->modele->getNotifications($idUser);

            if ($succes) {
                $this->vue->afficherNotifications($notifs, "La commande a été annulée avec succès.", "warning");
            } else {
                $this->vue->afficherNotifications($notifs, "Impossible d'annuler cette commande.", "danger");
            }
        } else {
            header('Location: index.php?module=compte&action=notification');
        }
    }
    private function afficherProfil($idUser)
    {
        $mesBuvettes = $this->modele->getBuvettesAdherent($idUser);
        $this->vue->afficherMonCompte($_SESSION['user'], $mesBuvettes);
    }

    private function traiterModificationEmail($idUser)
    {
        $mesBuvettes = $this->modele->getBuvettesAdherent($idUser);

        if (isset($_POST['new_email']) && !empty($_POST['new_email'])) {
            $newEmail = htmlspecialchars($_POST['new_email']);

            if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $succes = $this->modele->updateEmail($idUser, $newEmail);

                if ($succes) {
                    $_SESSION['user']['email'] = $newEmail;
                    $this->vue->afficherMonCompte($_SESSION['user'], $mesBuvettes, "Email mis à jour avec succès !", "success");
                } else {
                    $this->vue->afficherMonCompte($_SESSION['user'], $mesBuvettes, "Cet email est déjà utilisé ou une erreur est survenue.", "danger");
                }
            } else {
                $this->vue->afficherMonCompte($_SESSION['user'], $mesBuvettes, "Format d'email invalide.", "warning");
            }
        } else {
            $this->afficherProfil($idUser);
        }
    }

    private function traiterModificationMdp($idUser)
    {
        $mesBuvettes = $this->modele->getBuvettesAdherent($idUser);

        if (isset($_POST['old_mdp'], $_POST['new_mdp'], $_POST['confirm_mdp'])) {
            $oldMdp = $_POST['old_mdp'];
            $newMdp = $_POST['new_mdp'];
            $confirmMdp = $_POST['confirm_mdp'];

            if ($newMdp !== $confirmMdp) {
                $this->vue->afficherMonCompte($_SESSION['user'], $mesBuvettes, "Les nouveaux mots de passe ne correspondent pas.", "danger");
                return;
            }

            $hashActuel = $this->modele->getHashMdp($idUser);

            if (password_verify($oldMdp, $hashActuel)) {
                $newHash = password_hash($newMdp, PASSWORD_DEFAULT);
                $this->modele->updateMdp($idUser, $newHash);

                $this->vue->afficherMonCompte($_SESSION['user'], $mesBuvettes, "Mot de passe modifié avec succès !", "success");
            } else {
                $this->vue->afficherMonCompte($_SESSION['user'], $mesBuvettes, "L'ancien mot de passe est incorrect.", "danger");
            }
        } else {
            $this->afficherProfil($idUser);
        }
    }

    private function afficherHistorique($idUser)
    {
        $commandes = $this->modele->getHistorique($idUser);
        foreach ($commandes as $uneCommande) {
            $uneCommande['liste_produits'] = $this->modele->getDetailsCommande($uneCommande['id_commande']);
        }

        $this->vue->afficherHistorique($commandes);
    }
}
?>