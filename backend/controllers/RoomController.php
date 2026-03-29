<?php
// backend/controllers/RoomController.php

require_once __DIR__ . '/../repositories/RoomRepository.php';

class RoomController {
    private $repository;

    public function __construct() {
        // Le contrôleur instancie son repository [cite: 80]
        $this->repository = new RoomRepository();
    }

    // Récupère la liste de toutes les salles
    public function getAll() {
        try {
            // On appelle le repository au lieu d'écrire du SQL [cite: 121]
            return $this->repository->getAll();
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => "Erreur lors de la récupération : " . $e->getMessage()];
        }
    }

    // Crée une nouvelle salle
    public function create($data) {
        try {
            // Validation minimale côté serveur [cite: 167]
            if (empty($data->name) || empty($data->capacity)) {
                http_response_code(400);
                return ["message" => "Données incomplètes."];
            }

            $this->repository->add($data);
            return ["message" => "Salle ajoutée avec succès !"];
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => $e->getMessage()];
        }
    }

    // Supprime une salle
    public function delete($id) {
        try {
            $this->repository->delete($id);
            return ["message" => "Salle supprimée avec succès."];
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => $e->getMessage()];
        }
    }

    public function update($id, $data) {
    try {
        $this->repository->update($id, $data);
        return ["message" => "La salle a été mise à jour !"];
    } catch (Exception $e) {
        http_response_code(500);
        return ["error" => $e->getMessage()];
    }
}
}