<?php
// backend/models/Screening.php

class Screening {
    public $id;
    public $movie_id;
    public $room_id;
    public $start_time;
    // Propriétés de jointure pour l'affichage (via SQL JOIN)
    public $movie_title;
    public $room_name;
    public $movie_duration;
}