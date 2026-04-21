<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../database.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

$conn = getConnection();

switch ($method) {
    case 'GET':
        if ($action === 'stats') {
            getStats($conn);
        } else {
            getTasks($conn);
        }
        break;
    case 'POST':
        createTask($conn, $input);
        break;
    case 'PUT':
        updateTask($conn, $input);
        break;
    case 'DELETE':
        deleteTask($conn);
        break;
    default:
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

$conn->close();

function getTasks($conn) {
    $where = [];
    $params = [];
    $types = '';

    if (!empty($_GET['status'])) {
        $where[] = 't.status = ?';
        $params[] = $_GET['status'];
        $types .= 's';
    }
    if (!empty($_GET['priority'])) {
        $where[] = 't.priority = ?';
        $params[] = $_GET['priority'];
        $types .= 's';
    }
    if (!empty($_GET['category_id'])) {
        $where[] = 't.category_id = ?';
        $params[] = (int)$_GET['category_id'];
        $types .= 'i';
    }
    if (!empty($_GET['search'])) {
        $where[] = '(t.title LIKE ? OR t.description LIKE ?)';
        $params[] = '%' . $_GET['search'] . '%';
        $params[] = '%' . $_GET['search'] . '%';
        $types .= 'ss';
    }

    $sql = "SELECT t.*, c.name as category_name, c.color as category_color 
            FROM tasks t 
            LEFT JOIN categories c ON t.category_id = c.id";

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY 
        CASE t.status WHEN "completed" THEN 1 ELSE 0 END,
        CASE t.priority WHEN "high" THEN 0 WHEN "medium" THEN 1 ELSE 2 END,
        t.due_date ASC, t.created_at DESC';

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'tasks' => $tasks]);
}

function getStats($conn) {
    $stats = [];

    $result = $conn->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
    $statusCounts = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
    while ($row = $result->fetch_assoc()) {
        $statusCounts[$row['status']] = (int)$row['count'];
    }
    $stats['by_status'] = $statusCounts;
    $stats['total'] = array_sum($statusCounts);

    $result = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE due_date < CURDATE() AND status != 'completed'");
    $row = $result->fetch_assoc();
    $stats['overdue'] = (int)$row['count'];

    echo json_encode(['success' => true, 'stats' => $stats]);
}

function createTask($conn, $input) {
    if (empty($input['title'])) {
        echo json_encode(['error' => 'Title is required']);
        return;
    }

    $title = trim($input['title']);
    $description = trim($input['description'] ?? '');
    $category_id = !empty($input['category_id']) ? (int)$input['category_id'] : null;
    $priority = $input['priority'] ?? 'medium';
    $status = $input['status'] ?? 'pending';
    $due_date = !empty($input['due_date']) ? $input['due_date'] : null;

    $stmt = $conn->prepare("INSERT INTO tasks (title, description, category_id, priority, status, due_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssisss', $title, $description, $category_id, $priority, $status, $due_date);

    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $result = $conn->query("SELECT t.*, c.name as category_name, c.color as category_color FROM tasks t LEFT JOIN categories c ON t.category_id = c.id WHERE t.id = $id");
        $task = $result->fetch_assoc();
        echo json_encode(['success' => true, 'task' => $task]);
    } else {
        echo json_encode(['error' => 'Failed to create task']);
    }
}

function updateTask($conn, $input) {
    if (empty($input['id'])) {
        echo json_encode(['error' => 'Task ID is required']);
        return;
    }

    $id = (int)$input['id'];

    // Handle quick status toggle
    if (isset($input['status']) && count($input) === 2) {
        $status = $input['status'];
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        if ($stmt->execute()) {
            $result = $conn->query("SELECT t.*, c.name as category_name, c.color as category_color FROM tasks t LEFT JOIN categories c ON t.category_id = c.id WHERE t.id = $id");
            echo json_encode(['success' => true, 'task' => $result->fetch_assoc()]);
        } else {
            echo json_encode(['error' => 'Failed to update task']);
        }
        return;
    }

    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $category_id = !empty($input['category_id']) ? (int)$input['category_id'] : null;
    $priority = $input['priority'] ?? 'medium';
    $status = $input['status'] ?? 'pending';
    $due_date = !empty($input['due_date']) ? $input['due_date'] : null;

    $stmt = $conn->prepare("UPDATE tasks SET title=?, description=?, category_id=?, priority=?, status=?, due_date=? WHERE id=?");
    $stmt->bind_param('ssisssi', $title, $description, $category_id, $priority, $status, $due_date, $id);

    if ($stmt->execute()) {
        $result = $conn->query("SELECT t.*, c.name as category_name, c.color as category_color FROM tasks t LEFT JOIN categories c ON t.category_id = c.id WHERE t.id = $id");
        echo json_encode(['success' => true, 'task' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['error' => 'Failed to update task']);
    }
}

function deleteTask($conn) {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['error' => 'Task ID is required']);
        return;
    }
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task deleted']);
    } else {
        echo json_encode(['error' => 'Failed to delete task']);
    }
}
