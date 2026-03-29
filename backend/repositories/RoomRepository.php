<?php
// backend/repositories/RoomRepository.php
class RoomRepository {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM rooms ORDER BY name ASC")->fetchAll();
    }

    public function add($data) {
        $stmt = $this->pdo->prepare("INSERT INTO rooms (name, capacity, type) VALUES (?, ?, ?)");
        return $stmt->execute([$data->name, $data->capacity, $data->type]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM rooms WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function update($id, $data) {
    $sql = "UPDATE rooms SET name = :name, capacity = :capacity, type = :type WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
        ':name'     => $data->name,
        ':capacity' => (int)$data->capacity,
        ':type'     => $data->type,
        ':id'       => (int)$id
    ]);
}
}