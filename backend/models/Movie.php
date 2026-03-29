<?php
// backend/models/Movie.php

class Movie {
    public $id;
    public $title;
    public $description;
    public $duration;
    public $genre;
    public $image_url;
    public $release_year;
    public $director;

    // On ajoute un constructeur vide ou optionnel pour éviter les erreurs d'instanciation
    public function __construct() {
    }
}