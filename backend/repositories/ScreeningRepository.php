<?php
// backend/repositories/ScreeningRepository.php

require_once __DIR__ . '/../models/Screening.php';

class ScreeningRepository {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Récupère toutes les séances actives avec les noms des films et des salles
    public function getAll() {
        // Ajout de "s.active = 1" pour respecter la consigne du bootstrap sur le soft delete
        $sql = "SELECT s.*, m.title as movie_title, r.name as room_name 
                FROM shows s
                JOIN movies m ON s.movie_id = m.id
                JOIN rooms r ON s.room_id = r.id
                WHERE s.active = 1
                ORDER BY s.start_time ASC";
        
        $stmt = $this->pdo->query($sql);
        // On retourne des objets Screening pour être cohérent avec les autres repositories
        return $stmt->fetchAll(PDO::FETCH_CLASS, "Screening");
    }

    // Trouve les séances d'une salle spécifique (utilisé pour les conflits par le Service)
    public function findByRoom($roomId) {
        $sql = "SELECT s.*, m.duration as movie_duration 
                FROM shows s 
                JOIN movies m ON s.movie_id = m.id 
                WHERE s.room_id = ? AND s.active = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$roomId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajoute une nouvelle séance
    public function add($data) {
        // On force active = 1 à l'insertion
        $stmt = $this->pdo->prepare("INSERT INTO shows (movie_id, room_id, start_time, active) VALUES (?, ?, ?, 1)");
        return $stmt->execute([
            $data->movie_id, 
            $data->room_id, 
            $data->start_time
        ]);
    }

    // Suppression (Soft Delete)
    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE shows SET active = 0 WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function update($id, $data) {
    $sql = "UPDATE shows SET movie_id = :movie_id, room_id = :room_id, start_time = :start_time WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
        ':movie_id'   => $data->movie_id,
        ':room_id'    => $data->room_id,
        ':start_time' => $data->start_time,
        ':id'         => (int)$id
    ]);
}
}