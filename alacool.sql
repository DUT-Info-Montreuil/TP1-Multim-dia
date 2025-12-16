-- Création de la base de données
CREATE DATABASE IF NOT EXISTS alacool_db;
USE alacool_db;

-- Table Roles
CREATE TABLE Roles (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       role VARCHAR(50) NOT NULL UNIQUE
);

-- Table users (Utilisateurs)
CREATE TABLE users (
                       ine VARCHAR(20) PRIMARY KEY,
                       nom VARCHAR(100) NOT NULL,
                       email VARCHAR(100) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL,
                       statut_universitaire VARCHAR(50),
                       solde DECIMAL(10, 2) DEFAULT 0.00
);

-- Table buvettes (Buvettes/Stands)
CREATE TABLE buvettes (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          nom VARCHAR(100) NOT NULL UNIQUE,
                          est_ouverte BOOLEAN DEFAULT FALSE
);

-- Table buvette_membres (Association Utilisateurs - Buvettes - Rôles)
CREATE TABLE buvette_membres (
                                 id INT AUTO_INCREMENT PRIMARY KEY,
                                 user_id INT NOT NULL,
                                 buvette_id INT NOT NULL,
                                 role_id INT NOT NULL,
                                 FOREIGN KEY (user_id) REFERENCES users(ine) ON DELETE CASCADE,
                                 FOREIGN KEY (buvette_id) REFERENCES buvettes(id) ON DELETE CASCADE,
                                 FOREIGN KEY (role_id) REFERENCES Roles(id) ON DELETE RESTRICT,
                                 UNIQUE KEY unique_user_buvette (user_id, buvette_id)
);

-- Table Produits
CREATE TABLE Produits (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          nom VARCHAR(100) NOT NULL UNIQUE,
                          prix DECIMAL(10, 2) NOT NULL,
                          image VARCHAR(255)
);

-- Table stocks (Stocks de produits par Buvette)
CREATE TABLE stocks (
                        buvette_id INT NOT NULL,
                        produit_id INT NOT NULL,
                        quantite INT NOT NULL DEFAULT 0,
                        seuil_alerte INT NOT NULL DEFAULT 0,
                        PRIMARY KEY (buvette_id, produit_id),
                        FOREIGN KEY (buvette_id) REFERENCES buvettes(id) ON DELETE CASCADE,
                        FOREIGN KEY (produit_id) REFERENCES Produits(id) ON DELETE CASCADE,
                        CHECK (quantite >= 0),
                        CHECK (seuil_alerte >= 0)
);

-- Table Commandes
CREATE TABLE Commandes (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           user_id INT NOT NULL,
                           buvette_id INT NOT NULL,
                           statut ENUM('en_attente', 'en_preparation', 'prete', 'completee', 'annulee') NOT NULL DEFAULT 'en_attente',
                           date_commande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           prix_total DECIMAL(10, 2) NOT NULL,
                           FOREIGN KEY (user_id) REFERENCES users(ine) ON DELETE RESTRICT,
                           FOREIGN KEY (buvette_id) REFERENCES buvettes(id) ON DELETE RESTRICT,
                           CHECK (prix_total >= 0)
);

-- Table details_commande (Détail des articles dans une Commande)
CREATE TABLE details_commande (
                                  id INT AUTO_INCREMENT PRIMARY KEY,
                                  commande_id INT NOT NULL,
                                  produit_id INT NOT NULL,
                                  quantite INT NOT NULL,
                                  prix_unitaire DECIMAL(10, 2) NOT NULL,
                                  FOREIGN KEY (commande_id) REFERENCES Commandes(id) ON DELETE CASCADE,
                                  FOREIGN KEY (produit_id) REFERENCES Produits(id) ON DELETE RESTRICT,
                                  UNIQUE KEY unique_commande_produit (commande_id, produit_id),
                                  CHECK (quantite > 0),
                                  CHECK (prix_unitaire >= 0)
);

-- Table Inventaires (En-tête de l'inventaire)
CREATE TABLE Inventaires (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             date_inventaire DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             type VARCHAR(50) NOT NULL,
                             user_id INT NOT NULL,
                             buvette_id INT NOT NULL,
                             FOREIGN KEY (user_id) REFERENCES users(ine) ON DELETE RESTRICT,
                             FOREIGN KEY (buvette_id) REFERENCES buvettes(id) ON DELETE RESTRICT
);

-- Table ligne_inventaire (Détail des lignes d'inventaire)
CREATE TABLE ligne_inventaire (
                                  id INT AUTO_INCREMENT PRIMARY KEY,
                                  inventaire_id INT NOT NULL,
                                  produit_id INT NOT NULL,
                                  quantite_reelle INT,
                                  quantite_theorique INT,
                                  commentaire TEXT,
                                  FOREIGN KEY (inventaire_id) REFERENCES Inventaires(id) ON DELETE CASCADE,
                                  FOREIGN KEY (produit_id) REFERENCES Produits(id) ON DELETE RESTRICT
);