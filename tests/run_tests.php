<?php
echo "🧪 Running Tests...\n\n";
$passed = 0;
$failed = 0;

function test($name, $result) {
    global $passed, $failed;
    if ($result) { echo "✅ PASS: $name\n"; $passed++; }
    else         { echo "❌ FAIL: $name\n"; $failed++; }
}

// Test 1: Env vars
test('DB_HOST set', !empty(getenv('DB_HOST')));
test('DB_USER set', !empty(getenv('DB_USER')));
test('DB_NAME set', !empty(getenv('DB_NAME')));

// Test 2: DB Connection
require_once '/var/www/html/database.php';
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
test('RDS connection', !$conn->connect_error);

if (!$conn->connect_error) {
    // Test 3: Tables
    $r = $conn->query("SHOW TABLES LIKE 'tasks'");
    test('tasks table exists', $r && $r->num_rows > 0);

    $r = $conn->query("SHOW TABLES LIKE 'categories'");
    test('categories table exists', $r && $r->num_rows > 0);

    // Test 4: CRUD
    $stmt = $conn->prepare("INSERT INTO tasks (title, priority, status) VALUES (?, 'low', 'pending')");
    $title = 'Jenkins CI Test Task';
    $stmt->bind_param('s', $title);
    test('Create task', $stmt->execute());
    $id = $conn->insert_id;

    $r = $conn->query("SELECT id FROM tasks WHERE id=$id");
    test('Read task', $r && $r->num_rows === 1);

    $conn->query("DELETE FROM tasks WHERE id=$id");
    test('Delete task', $conn->affected_rows === 1);
    $conn->close();
}

// Test 5: Files
test('index.php exists',      file_exists('/var/www/html/index.php'));
test('tasks.php exists',      file_exists('/var/www/html/tasks.php'));
test('categories.php exists', file_exists('/var/www/html/categories.php'));
test('database.php exists',   file_exists('/var/www/html/database.php'));

echo "\n──────────────────────\n";
echo "Passed : $passed ✅\n";
echo "Failed : $failed ❌\n";
echo "──────────────────────\n";
exit($failed > 0 ? 1 : 0);
