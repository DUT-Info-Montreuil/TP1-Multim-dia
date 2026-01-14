# 🍻 A LA COOL — Gestion de Buvettes

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![MVC](https://img.shields.io/badge/Architecture-MVC-orange?style=for-the-badge)

> **Projet Universitaire - BUT2 Informatique (SAE S3.01)**
> Développement d'une application web pour la gestion dynamique de buvettes et de files d'attente lors d'événements.

---

## 📋 Présentation

**A LA COOL** est une solution web complète permettant de fluidifier les commandes dans les buvettes. L'application connecte les clients, qui peuvent commander depuis leur smartphone, aux serveurs qui gèrent la préparation et le service en temps réel.

### 🌟 Fonctionnalités Clés

#### 👤 Espace Client
* **Exploration :** Liste des buvettes ouvertes/fermées avec moteur de recherche.
* **Commande :** Accès à la carte interactive et gestion simplifiée du panier.
* **Suivi :** Historique des commandes et suivi de l'état d'avancement en temps réel.
* **Histoire :** Page de présentation de l'association et de ses valeurs.

#### 🍹 Espace Staff (Barman/Serveur)
* **Dashboard Live :** Vue d'ensemble des commandes (En attente, En prépa, Prêt).
* **Vente au Comptoir (POS) :** Interface dédiée pour encaisser les clients sur place (Recherche client AJAX + Panier dynamique).
* **Workflow :** Changement de statut des commandes par interface intuitive.
* **Gestion des Stocks :** Structure de données prévue pour le suivi des inventaires.

---

## 🏗️ Architecture Technique

Le projet respecte scrupuleusement le patron de conception **MVC (Modèle-Vue-Contrôleur)** sans framework PHP, afin de garantir une maîtrise totale du code et des performances.

* **Frontend :** Bootstrap 5 pour une interface moderne et responsive.
* **Backend :** PHP natif pour la logique métier.
* **Base de données :** MySQL.
* **Interactivité :** Utilisation d'AJAX pour les recherches et les mises à jour sans rechargement de page.

---

## 🚀 Installation & Démarrage

1.  **Cloner le projet**
    ```bash
    git clone [https://github.com/votre-repo/S3.01-DevWeb.git](https://github.com/votre-repo/S3.01-DevWeb.git)
    ```

2.  **Configuration**
    * Vérifier le fichier `connexion.php` à la racine pour s'assurer que les identifiants BDD (hôte, utilisateur, mot de passe) sont corrects.

3.  **Lancement**
    * Placer le dossier dans votre serveur web local (WAMP, XAMPP, Laragon).
    * Accéder via : `http://localhost/S3.01-DevWeb/`

---

## 👥 L'Équipe de Développement

Ce projet a été réalisé avec rigueur par :

* **F. GUERREIRO MARQUES**
* **T. AUSOUSSEAU**
* **M. JEAN FORT**
* **E. PEREIRA**

---
*© 2025 - IUT Informatique - Tous droits réservés.*
