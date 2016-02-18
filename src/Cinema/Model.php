<?php

namespace Cinema;

/**
 * Représente le "Model", c'est à dire l'accès à la base de
 * données pour l'application cinéma basé sur MySQL
 */
class Model
{
    protected $pdo;

    public function __construct($host, $database, $user, $password)
    {
        try {
            $this->pdo = new \PDO(
                'mysql:dbname='.$database.';host='.$host,
                $user,
                $password
            );
        } catch (\PDOException $error) {
            die('Unable to connect to database.');
        }
        $this->pdo->exec('SET CHARSET UTF8');
    }

    protected function execute(\PDOStatement $query, array $variables = array())
    {
        if (!$query->execute($variables)) {
            $errors = $query->errorInfo();
            throw new ModelException($errors[2]);
        }

        return $query;
    }

    /**
     * Récupère un résultat exactement
     */
    protected function fetchOne(\PDOStatement $query)
    {
        if ($query->rowCount() != 1) {
            return false;
        } else {
            return $query->fetch();
        }
    }

    /**
     * Base de la requête pour obtenir un film
     */
    protected function getFilmSQL()
    {
        return
            'SELECT films.image, films.id, films.nom, films.description, genres.nom as genre_nom FROM films 
             INNER JOIN genres ON genres.id = films.genre_id ';
    }

    /**
     * Récupère la liste des films
     */
    public function getFilms()
    {
        $sql = $this->getFilmSQL();

        return $this->execute($this->pdo->prepare($sql));
    }

    /**
     * Récupère un film
     */
    public function getFilm($id)
    {
        $sql = 
            $this->getFilmSQL() . 
            'WHERE films.id = ?'
            ;

        $query = $this->pdo->prepare($sql);
        $this->execute($query, array($id));

        return $this->fetchOne($query);
    }

    /**
     * Récupérer les acteurs d'un film
     */
	 
	 protected function getCastingSQL()
	 {
        return 
            'SELECT acteurs.nom, acteurs.prenom ,acteurs.image, roles.role FROM roles
            INNER JOIN acteurs ON roles.acteur_id = acteurs.id
            INNER JOIN films ON roles.film_id = films.id ';           
    }
	 
    public function getCasting($filmId)
    {
        $sql = $this->getCastingSQL().
		'WHERE roles.film_id = :film_id'
            ;
		$query = $this->pdo->prepare($sql);
        $query->execute(array('film_id' => $filmId));

        return $query;
    }
	
	
	
	/**
	*Critiques
	*/
	
	protected function getCritiquesSQL()
	{
		return
		'SELECT critiques.nom, critiques.commentaire, critiques.note FROM critiques
		INNER JOIN films ON critiques.film_id = films.id '; 
	}
	
	public function getCritiques($filmId)
	{
		$sql = $this->getCritiquesSQL().
		'WHERE critiques.film_id = :film_id'
			;
		
		$query = $this->pdo->prepare($sql);
		$query->execute(array('film_id' => $filmId));
		
		return $query;
	}
	
	/**
	* Envoyer une critique
	*/
	public function setCritiques($post,$filmId){

		$nom = "";
        $note = "";
        $commentaires = "";
		
		foreach ($post as $key => $value) {
            if($key == 'nom'){
                $nom = $value;
            }
            if($key == 'note'){
                $note = $value;
            }
            if($key == 'critique'){
                $commentaires = $value;
            }            
        }
    
        $sql =

            "INSERT INTO critiques (nom,commentaire,note,film_id) VALUES ('".$nom."','".$commentaires."', '".$note."','".$filmId."')";
        $req = $this->pdo->prepare($sql); 

        $req->execute(array(
            'nom' => $nom, 
            'commentaire' => $commentaires,
            'note' => $note,
            'film_id' => $filmId
            ));

        $data = $req->fetchAll();

	}

        /** 
		$nom = "";
        $note = "";
        $critiques = "";   
		
		$sql = $this->pdo->prepare("INSERT INTO critiques (nom,commentaires,note) VALUES ('".$nom."','".$critiques."','".$note."')");
        }
		*/

    /**
     * Genres
     */
    public function getGenres()
    {
        $sql = 
            'SELECT genres.nom, COUNT(*) as nb_films FROM genres '.
            'INNER JOIN films ON films.genre_id = genres.id '.
            'GROUP BY genres.id'
            ;

        return $this->execute($this->pdo->prepare($sql));
    }
	
}
