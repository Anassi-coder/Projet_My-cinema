<?php
// 1. Autoriser les requêtes du frontend (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Gestion du pré-vol (Preflight) pour le navigateur
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 2. Chargement de la configuration et des outils
require_once __DIR__ . '/config/database.php';

// 3. Import automatique des Models (Indispensable pour PDO::FETCH_CLASS)
require_once __DIR__ . '/models/Movie.php';
require_once __DIR__ . '/models/Room.php';
require_once __DIR__ . '/models/Screening.php';

// 4. Import des Repositories et Controllers
require_once __DIR__ . '/repositories/MovieRepository.php';
require_once __DIR__ . '/repositories/RoomRepository.php';
require_once __DIR__ . '/repositories/ScreeningRepository.php';

require_once __DIR__ . '/controllers/MovieController.php';
require_once __DIR__ . '/controllers/RoomController.php';
require_once __DIR__ . '/controllers/ScreeningController.php';

// 5. Récupération des paramètres de la requête
$resource = $_GET['resource'] ?? null;
$id = $_GET['id'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

// Lecture du JSON envoyé par le JS (si existant)
$jsonData = json_decode(file_get_contents("php://input"));
$data = !empty($_POST) ? (object)$_POST : $jsonData;

// 6. Routage vers le bon contrôleur
$controller = null;

switch ($resource) {
    case 'movies':
        $controller = new MovieController();
        break;
    case 'rooms':
        $controller = new RoomController();
        break;
    case 'shows':
        $controller = new ScreeningController();
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Ressource non trouvée"]);
        exit;
}

// 7. Traitement de la requête
$result = null;

try {
    switch ($method) {
        case 'GET':
            $result = $controller->getAll();
            break;
        case 'POST':
            if (isset($data->id) && !empty($data->id)) {
                $result = $controller->update($data->id, $data);
            } else {
                $result = $controller->create($data);
            }
            break;
        case 'DELETE':
            $result = $controller->delete($id);
            break;
    }

    // Envoi de la réponse JSON finale
    if ($result !== null) {
        echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}