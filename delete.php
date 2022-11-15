<?php
session_start();

    // Récupérer

    // Si aucun identifiant de film n'a été reçu via la méthode GET 
    if ( !isset($_GET['film_id']) || empty($_GET['film_id']) ) 
    {
        // Effectuer une redirection vers la page d'accueil 
        // Arrêter l'exécution du script
        return header("Location: index.php");
    }
    
 // Dans le cas contraire,
    // Récupérer l'identifiant du film de $_GET
    $film_id = strip_tags($_GET['film_id']);


    // Convertir l'identifiant du film pour être sûr de travailler avec un entier
    $film_id_converted = (int) $film_id;
    // $film_id_converted = intval($film_id);


    // Etablir une connexion avec la base de données
    require __DIR__ . "/db/connection.php";


    // Effectuer la requête de selection afin de vérifier 
    // si l'identifiant du film récupéré depuis la barre d'url 
    // correspond à celui d'un film de la table "film"
    $req = $db->prepare("SELECT * FROM film WHERE id = :id");
    $req->bindValue(":id", $film_id_converted);
    $req->execute();
    $count = $req->rowCount();


    // Si le nombre total d'enregistrements récupéré n'est pas égal à 1,
    // Rediriger l'utilisateur vers la page d'accueil
    // Arrêter automatiquement l'exécution du script
    if ( $count != 1 ) 
    {
        return header("Location: index.php");
    }

    // Dans le cas contraire
    // Récupèrer le film en question
    $film = $req->fetch();

    // Fermer le curseur
    $req->closeCursor();

    // Effectuer une seconde pour supprimer le film
    $delete_req = $db->prepare("DELETE FROM film WHERE id = :id");
    $delete_req->bindValue(":id", $film['id']);
    $delete_req->execute();
    $delete_req->closeCursor();

    // Générer le message flash
    $_SESSION["success"] = $film['name'] . " a été retiré de la liste.";

    // Effectuer la redirection vers la page d'accueil
    // Arreter l'éxécution du script
    return header("Location: index.php");
