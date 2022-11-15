<?php
session_start(); 
//var_dump($_SERVER); 
//die(); die permet d'arreter l'exécution du script

// Si les données du formulaire ont été envoyées via la méthode "POST"
if( $_SERVER['REQUEST_METHOD'] == "POST")
{
    $post_clean = [];
    $create_form_errors = [];


    //Protéger le serveur contre les failles de type XSS
    foreach ($_POST as $key => $value)
    {
        $post_clean[$key] = strip_tags(trim($value));
    }
    
    
    //Mettre en place les contraintes de validations des données du formulaire
    
    // Pour le nom du film
    if ( isset($post_clean['name']) ) //required
    {
        if ( empty($post_clean['name']) )
        {
            $create_form_errors['name'] = "Le nom du film est obligatoire.";
        }
        else if( mb_strlen( $post_clean['name']) > 255) // max:255
        {
            $create_form_errors['name'] = "Le nom du film doit contenir au maximum 255 caractères.";
        }
    }
    
    // Pour le nom du ou des acteurs
    if ( isset($post_clean['actors']) )
    {
        if (empty($post_clean['actors'])) // required
        {
            $create_form_errors['actors'] = "Le nom du ou des acteurs est obligatoire.";
        }
        else if( mb_strlen($post_clean['actors']) > 255 ) // max:255
        {
            $create_form_errors['actors'] = "Le nom du ou des acteurs doit contenir au maximum 255 caractères." ;
        }
    }
    
    // Pour la note
    if ( isset($post_clean['review']) )
    {   if ( is_string($post_clean['review']) && ($post_clean['revieiw'] == '') )
        {
            $create_form_errors['review'] = "La note est obligatoire.";
        }
        else if ( empty($post_clean['review']) && ($post_clean['review'] != 0) )
        {
            $create_form_errors['review'] = "La note est obligatoirement obligatoire.";
        }
        else if ( ! is_numeric($post_clean['review']) )
        {
            $create_form_errors['review'] = "La note doit être un nombre.";
        }
        else if ( ($post_clean['review'] < 0) || ($post_clean['review'] > 5) )
        {
            $create_form_errors['review'] = "La note doit être comprise entre 0 et 5.";
        }
    }   
  
    // Si il y a des erreurs
    if (count($create_form_errors) > 0)
    {
        // Stocker les messages d'erreurs en session
        $_SESSION['create_form_errors'] = $create_form_errors;

        // Stocker les données provenant du formulaire en session
        $_SESSION['old'] = $post_clean;
        // Rediriger l'utilisateur vers la page a partirde laquelle proviennent les données
        header("location: " .$_SERVER['HTTP_REFERER']);
        // J'arrete l'exécution du script
        
    }
    
    // Dans le cas contraire,
    // Protéger le serveur contre les failles de type XSS une seconde fois
    $final_post_clean = [];
    foreach ($post_clean as $key => $value)
    {
        $final_post_clean[$key] = htmlspecialchars($value);
    }

    $film_name   = $final_post_clean["name"];
    $film_actors = $final_post_clean["actors"];
    $film_review = $final_post_clean["review"];


    // Arrondir la note à un chiffre après la virgule
    $film_review_rounded = round($film_review, 1);
    
    // Etablir une connexion avec la base de données
    require __DIR__ . "/db/connection.php";
    
    // Effectuer la requête d'insertion des données dans la table "film" de la base données
    $req = $db->prepare("INSERT INTO film (name, actors, review, created_at, updated_at)  VALUES (:name, :actors, :review, now(), now() ) ");

    $req->bindValue(":name",            $film_name);
    $req->bindValue(":actors",          $film_actors);
    $req->bindValue(":review",          $film_review_rounded);
    
    $req->execute();
    $req->closeCursor();

    // Rediriger l'utilisateur vers la page d'accueil
    // Arrêter l'execution du script.
    return header("location: index.php");
    
}

?>
<!---------------------------------------- View -------------------------------------------------->

<?php $title = "Ajout d'un nouveau film"; ?>

<?php include "partials/head.php"; ?>

<?php include "partials/nav.php"; ?>

<!-- Le main représente le contenu spécifique à cette page -->
<main class="container-fluid">
    <h1> Nouveau film </h1>

    <?php if( isset($_SESSION['create_form_errors']) && !empty($_SESSION ['create_form_errors'])) : ?>
        <div class="alert-danger" role="alert">
            <ul>
                <?php foreach($_SESSION['create_form_errors'] as $error) : ?>
                    <li>- <?= $error ?></li>
                    <?php endforeach ?>
            </ul>
        </div>
        <?php unset($_SESSION['create_form_errors']); ?>
        <?php endif ?>

    <div class="form-container">
        <form method="POST">
            <div class="mb-3">
                <label for="name_film">Nom du film</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php isset($_SESSION['old']['name']) ? $_SESSION['old']['name'] : ""; unset($_SESSION['old']['name']); ?>" >
            </div>

            <div class="mb-3">
                <label for="actors_film">Nom du ou des acteurs</label>
                <input type="text" name="actors" id="actors_film" class="form-control" value="<?php isset($_SESSION['old']['actors']) ? $_SESSION['old']['actors'] : ""; unset($_SESSION['old']['actors']); ?>" >
            </div>

            <div class="mb-3">
                <label for="review_film">La note sur 5</label>
                <input type="text" name="review" id="review_film" class="form-control" value="<?php isset($_SESSION['old']['review']) ? $_SESSION['old']['review'] : ""; unset($_SESSION['old']['review']); ?>" >
            </div>

            <div class="mb-3">
                <input type="submit" class="btn btn-primary">
            </div>
        </form>
    </div>
</main>

<?php include "partials/footer.php"; ?>
<?php include "partials/foot.php"; ?>