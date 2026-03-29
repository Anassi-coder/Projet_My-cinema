<?php
// backend/controllers/MovieController.php

require_once __DIR__ . '/../repositories/MovieRepository.php';

class MovieController {
    private $repository;

    public function __construct() {
        $this->repository = new MovieRepository();
    }

    public function getAll() {
        try {
            return $this->repository->getAll(); 
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => $e->getMessage()];
        }
    }

    public function create($data) {
        try {
            $imageUrl = $this->handleFileUpload();
            $this->repository->add($data, $imageUrl);
            return ["message" => "Film ajouté avec succès !"];
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => $e->getMessage()];
        }
    }

    public function update($id, $data) {
        try {
            $imageUrl = $this->handleFileUpload();
            // On ne passe l'URL de l'image que si une nouvelle image a été chargée
            $this->repository->update($id, $data, $imageUrl);
            return ["message" => "Film mis à jour avec succès !"];
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => $e->getMessage()];
        }
    }

    public function delete($id) {
        if (!$id) {
            http_response_code(400);
            return ["error" => "ID manquant"];
        }

        try {
            $count = $this->repository->countShows($id);
            if ($count > 0) {
                http_response_code(400);
                return ["message" => "Impossible de supprimer : ce film est lié à $count séance(s)."];
            }

            $this->repository->delete($id);
            return ["message" => "Le film a été supprimé."];
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => $e->getMessage()];
        }
    }

    // Gère l'upload de l'image de l'affiche
    private function handleFileUpload() {
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['poster']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['poster']['tmp_name'], $targetPath)) {
                return 'uploads/' . $fileName; // Chemin relatif pour la BDD
            }
        }
        return null;
    }
}