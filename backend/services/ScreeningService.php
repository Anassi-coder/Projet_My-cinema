<?php
// backend/services/ScreeningService.php

class ScreeningService {
    private $repository;

    public function __construct($repository) {
        $this->repository = $repository;
    }

    // Vérifie si une salle est disponible pour un créneau donné
    public function isRoomAvailable($roomId, $startTime, $durationMinutes, $excludeId = null) {
    $newStart = strtotime($startTime);
    $newEnd = $newStart + ($durationMinutes * 60);

    $existingShowings = $this->repository->findByRoom($roomId);

    foreach ($existingShowings as $show) {
        // SI on modifie une séance, on ignore son propre ancien créneau
        if ($excludeId && $show['id'] == $excludeId) continue;

        $existingStart = strtotime($show['start_time']);
        $existingEnd = $existingStart + ($show['movie_duration'] * 60);

        if ($newStart < $existingEnd && $newEnd > $existingStart) {
            return false;
        }
    }
    return true;
}
}