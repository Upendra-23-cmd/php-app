<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$conn = getConnection();

switch ($method) {
    case 'GET':
        getCategories($conn);
        break;
    case 'POST':
        createCategory($conn, $input);
        break;
    case 'DELETE':
        deleteCategory($conn);
        break;
    default:
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

$conn->close();

function getCategories($conn) {
    $result = $conn->query("
        SELECT c.*, COUNT(t.id) as task_count 
        FROM categories c 
        LEFT JOIN tasks t ON c.id = t.category_id 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $categories = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'categories' => $categories]);
}

function createCategory($conn, $input) {
    if (empty($input['name'])) {
        echo json_encode(['error' => 'Name is required']);
        return;
    }
    $name = trim($input['name']);
    $color = $input['color'] ?? '#6366f1';

    $stmt = $conn->prepare("INSERT INTO categories (name, color) VALUES (?, ?)");
    $stmt->bind_param('ss', $name, $color);
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        echo json_encode(['success' => true, 'category' => ['id' => $id, 'name' => $name, 'color' => $color, 'task_count' => 0]]);
    } else {
        echo json_encode(['error' => 'Failed to create category']);
    }
}

function deleteCategory($conn) {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['error' => 'Category ID is required']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete category']);
    }
}
