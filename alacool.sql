CREATE TABLE Role_Utilisateur (
                                  id_role INT NOT NULL,
                                  nom_role VARCHAR(50) NOT NULL,
                                  CONSTRAINT Role_Utilisateur_PK PRIMARY KEY (id_role)
)ENGINE=InnoDB;

CREATE TABLE Utilisateur (
                             INE VARCHAR(50) NOT NULL,
                             nom VARCHAR(50) NOT NULL,
                             prenom VARCHAR(50) NOT NULL,
                             email VARCHAR(50) NOT NULL,
                             motdepasse VARCHAR(50) NOT NULL,
                             statut_universitaire VARCHAR(50) NOT NULL,
                             solde INT NOT NULL,
                             CONSTRAINT Utilisateur_PK PRIMARY KEY (INE)
)ENGINE=InnoDB;

CREATE TABLE Une_Buvette (
                             id_buvette INT NOT NULL,
                             nom VARCHAR(50) NOT NULL,
                             est_ouverte TINYINT(1) NOT NULL,
                             CONSTRAINT Une_Buvette_PK PRIMARY KEY (id_buvette, nom)
)ENGINE=InnoDB;

CREATE TABLE Produit (
                         id_produit INT NOT NULL,
                         nom_produit VARCHAR(50) NOT NULL,
                         prix_produit INT NOT NULL,
                         image_produit VARCHAR(50) NOT NULL,
                         CONSTRAINT Produit_PK PRIMARY KEY (id_produit)
)ENGINE=InnoDB;

CREATE TABLE Changement_Stock (
                                  id INT NOT NULL,
                                  type_changement VARCHAR(50) NOT NULL,
                                  quantite INT NOT NULL,
                                  date_changement DATE NOT NULL,
                                  commentaire VARCHAR(50) NOT NULL,
                                  INE VARCHAR(50) NOT NULL,
                                  CONSTRAINT Changement_Stock_PK PRIMARY KEY (id),
                                  CONSTRAINT Changement_Stock_INE_FK FOREIGN KEY (INE) REFERENCES Utilisateur (INE)
)ENGINE=InnoDB;

CREATE TABLE Affecter (
                          id_role INT NOT NULL,
                          INE VARCHAR(50) NOT NULL,
                          id_buvette INT NOT NULL,
                          nom VARCHAR(50) NOT NULL,
                          date_debut DATE NOT NULL,
                          date_fin DATE NOT NULL,
                          CONSTRAINT Affecter_PK PRIMARY KEY (id_role, INE, id_buvette, nom),
                          CONSTRAINT Affecter_id_role_FK FOREIGN KEY (id_role) REFERENCES Role_Utilisateur (id_role),
                          CONSTRAINT Affecter_INE_FK FOREIGN KEY (INE) REFERENCES Utilisateur (INE),
                          CONSTRAINT Affecter_id_buvette_nom_FK FOREIGN KEY (id_buvette, nom) REFERENCES Une_Buvette (id_buvette, nom)
)ENGINE=InnoDB;

CREATE TABLE Commande (
                          id_commande INT NOT NULL,
                          statut VARCHAR(50) NOT NULL,
                          date_commande DATETIME NOT NULL,
                          prix_total INT NOT NULL,
                          INE VARCHAR(50) NOT NULL,
                          id_buvette INT NOT NULL,
                          nom VARCHAR(50) NOT NULL,
                          CONSTRAINT Commande_PK PRIMARY KEY (id_commande),
                          CONSTRAINT Commande_INE_FK FOREIGN KEY (INE) REFERENCES Utilisateur (INE),
                          CONSTRAINT Commande_id_buvette_nom_FK FOREIGN KEY (id_buvette, nom) REFERENCES Une_Buvette (id_buvette, nom)
)ENGINE=InnoDB;