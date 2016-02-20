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
        if ($post->has('nom') && $post->has('note') && $post->has('critique')) {
			$nom = html_escape($_POST['nom']);
			if(is_numeric($_POST['note']){
				$note = intval($_POST['note']);
				if($note >= 0 && $note < 6) {
				
				   $commentaires = html_escape($_POST['commentaire']);
				   $app['model']->setCritiques($nom,$note,$commentaires,$id);
				}
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

$app->run();
