<?php

$loader = include('vendor/autoload.php');
$loader->add('', 'src');

$app = new Silex\Application;
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Fait remonter les erreurs
$app['debug'] = true;

$app['model'] = new Cinema\Model(
    'localhost',  // Hôte
    'cinema',    // Base de données
    'root',    // Utilisateur
    ''     // Mot de passe
);

// Page d'accueil
$app->match('/', function() use ($app) {
    return $app['twig']->render('home.html.twig');
})->bind('home');

// Liste des films
$app->match('/films', function() use ($app) {
    return $app['twig']->render('films.html.twig', array(
        'films' => $app['model']->getFilms()
    ));
})->bind('films');

// Fiche film

$app->match('/film/{id}', function($id) use ($app) {
    $request = $app['request'];
    if ($request->getMethod() == 'POST') {
        $post = $request->request;
        if ($post->has('nom') && $post->has('note') && $post->has('commentaire')) {
			$nom = htmlspecialchars($_POST['nom']);
			$note = htmlspecialchars($_POST['note']);
				if($note >= 0 && $note < 6) {
				   $commentaire = htmlspecialchars($_POST['commentaire']);
				   $app['model']->setCritiques($nom,$note,$commentaire,$id);
				}
			
		}
	}


    return $app['twig']->render('film.html.twig', array(
        'film' => $app['model']->getFilm($id),
        'casting' => $app['model']->getCasting($id),
		'critiques' => $app['model']->getCritiques($id)
    ));
})->bind('film');


// Genres
$app->match('/genres', function() use ($app) {
    return $app['twig']->render('genres.html.twig', array(
        'genres' => $app['model']->getGenres()
    ));
})->bind('genres');

//Film par genres
$app->match('/filmGenre/{id}', function($id) use ($app) {
	 return $app['twig']->render('filmGenre.html.twig', array(
        'filmGenre' => $app['model']->getFilmGenre($id),
        'casting' => $app['model']->getCasting($id)
    ));
})->bind('filmGenre');


//Top Film

$app->match('/topFilm', function() use ($app) {
    return $app['twig']->render('topFilm.html.twig', array(
        'topFilm' => $app['model']->getTopfilm()
    ));
})->bind('topFilm');

//Ajout film

$app->match('/ajoutFilm', function() use ($app) {
    $request = $app['request'];
    if ($request->getMethod() == 'POST') {
        $post = $request->request;
        if ($post->has('nom') && $post->has('description') && $post->has('annee') && $post->has('genre_id') && $post->has('image')){
			$nom = htmlspecialchars($_POST['description']);
			$description = htmlspecialchars($_POST['description']);
			$annee = htmlspecialchars($_POST['annee']);
			$genre_id = htmlspecialchars($_POST['genre_id']);
			$image = htmlspecialchars($_POST['image']);
            $app['model']->addFilm($nom,$description,$annee,$genre_id,$image);
			
        }
    }
    return $app['twig']->render('ajoutFilm.html.twig');
})->bind('ajoutFilm');



$app->run();
