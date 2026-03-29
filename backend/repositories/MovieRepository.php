<?php
// backend/repositories/MovieRepository.php
require_once __DIR__ . '/../models/Movie.php';

class MovieRepository {
    private $pdo;

    public function __construct() {
        global $pdo; 
        $this->pdo = $pdo;
    }

    // Récupère tous les films et les transforme en objets Movie
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM movies ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_CLASS, "Movie");
    }

    // Trouve un film par son ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM movies WHERE id = ?");
        $stmt->execute([(int)$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, "Movie");
        $movie = $stmt->fetch();
        return $movie ?: null;
    }

    // Ajoute un nouveau film
    public function add($data, $imageUrl = null) {
        $sql = "INSERT INTO movies (title, duration, genre, description, image_url) 
                VALUES (:title, :duration, :genre, :description, :image_url)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':title'       => $data->title,
            ':duration'    => (int)$data->duration,
            ':genre'       => $data->genre ?? null,
            ':description' => $data->description ?? null,
            ':image_url'   => $imageUrl
        ]);
    }

    // Met à jour un film existant
    public function update($id, $data, $imageUrl = null) {
        $sql = "UPDATE movies SET title = :title, duration = :duration, 
                genre = :genre, description = :description";
        
        $params = [
            ':title'       => $data->title,
            ':duration'    => (int)$data->duration,
            ':genre'       => $data->genre ?? null,
            ':description' => $data->description ?? null,
            ':id'          => (int)$id
        ];

        if ($imageUrl) {
            $sql .= ", image_url = :image_url";
            $params[':image_url'] = $imageUrl;
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Supprime un film
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM movies WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // Compte le nombre de séances liées à ce film (pour empêcher la suppression)
    public function countShows($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM shows WHERE movie_id = ?");
        $stmt->execute([(int)$id]);
        return (int)$stmt->fetchColumn();
    }
}