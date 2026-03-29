<?php
// backend/controllers/ScreeningController.php

require_once __DIR__ . '/../repositories/ScreeningRepository.php';
require_once __DIR__ . '/../services/ScreeningService.php';
require_once __DIR__ . '/../repositories/MovieRepository.php';

class ScreeningController {
    private $repository;
    private $service;
    private $movieRepo;

    public function __construct() {
        $this->repository = new ScreeningRepository();
        $this->service = new ScreeningService($this->repository);
        $this->movieRepo = new MovieRepository();
    }

    // Récupère toutes les séances pour l'affichage
    public function getAll() {
        try {
            return $this->repository->getAll();
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => "Erreur de récupération des séances : " . $e->getMessage()];
        }
    }

    // Crée une séance avec vérification de conflit via le Service
    public function create($data) {
        try {
            // Sécurité : on s'assure que les données obligatoires sont là
            if (empty($data->movie_id) || empty($data->room_id) || empty($data->start_time)) {
                http_response_code(400);
                return ["message" => "Données incomplètes pour la création de séance."];
            }

            // 1. Récupérer le film pour connaître sa durée
            $movie = $this->movieRepo->findById($data->movie_id);
            if (!$movie) {
                http_response_code(404);
                return ["error" => "Le film sélectionné n'existe pas."];
            }

            // 2. Vérifier la disponibilité de la salle via le Service
            // (Logique métier : pas deux films en même temps dans la même salle)
            $isAvailable = $this->service->isRoomAvailable(
                $data->room_id, 
                $data->start_time, 
                $movie->duration
            );

            if (!$isAvailable) {
                http_response_code(400);
                return ["message" => "Conflit d'horaire : la salle est déjà occupée sur ce créneau."];
            }

            // 3. Enregistrement en base de données
            $this->repository->add($data);
            return ["message" => "Séance programmée avec succès !"];

        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => $e->getMessage()];
        }
    }

    // Supprime une séance (Soft Delete via le Repository)
    public function delete($id) {
        if (!$id) {
            http_response_code(400);
            return ["error" => "ID de séance manquant."];
        }

        try {
            $this->repository->delete($id);
            return ["message" => "Séance annulée avec succès."];
        } catch (Exception $e) {
            http_response_code(500);
            return ["error" => $e->getMessage()];
        }
    }

    public function update($id, $data) {
    try {
        $movie = $this->movieRepo->findById($data->movie_id);
        
        // Vérification des conflits (on ignore la séance actuelle pour ne pas s'auto-bloquer)
        $isAvailable = $this->service->isRoomAvailable($data->room_id, $data->start_time, $movie->duration, $id);

        if (!$isAvailable) {
            http_response_code(400);
            return ["message" => "Conflit d'horaire : la salle est occupée."];
        }

        $this->repository->update($id, $data);
        return ["message" => "Séance modifiée avec succès !"];
    } catch (Exception $e) {
        http_response_code(500);
        return ["error" => $e->getMessage()];
    }
}
}