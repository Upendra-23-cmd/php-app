# TaskFlow — PHP Todo Manager

A full-stack PHP Todo application with MySQL database and Tailwind CSS frontend.

---

## Tech Stack

- **Frontend**: HTML, Tailwind CSS (CDN), Vanilla JavaScript
- **Backend**: PHP 8+ with MySQLi
- **Database**: MySQL
- **Server**: Apache2 (Ubuntu)

---

## Project Structure

```
/var/www/html/todo-app/
├── index.php           ← Main frontend UI
├── tasks.php           ← Tasks REST API (CRUD)
├── categories.php      ← Categories REST API
├── database.php        ← MySQL connection config
├── schema.sql          ← Database schema + seed data
└── README.md
```

---

## Setup Instructions (Ubuntu + Apache)

### Step 1 — Install Dependencies

```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysqli -y
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql
```

### Step 2 — Copy Files to Apache Web Root

```bash
sudo cp -r ~/php-app/* /var/www/html/todo-app/
sudo chown -R www-data:www-data /var/www/html/todo-app
sudo chmod -R 755 /var/www/html/todo-app
```

### Step 3 — Set Up the Database

```bash
sudo mysql < /var/www/html/todo-app/schema.sql
```

This creates the `todo_app` database, tables, and sample data automatically.

### Step 4 — Configure Database Credentials

```bash
sudo nano /var/www/html/todo-app/database.php
```

Update these values:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'todo_app');
```

> If MySQL uses `auth_socket` (default on Ubuntu), create a dedicated user instead:
> ```bash
> sudo mysql -e "CREATE USER 'todouser'@'localhost' IDENTIFIED BY 'yourpassword';
> GRANT ALL ON todo_app.* TO 'todouser'@'localhost'; FLUSH PRIVILEGES;"
> ```
> Then set `DB_USER=todouser` and `DB_PASS=yourpassword` in `database.php`.

### Step 5 — Restart Apache

```bash
sudo systemctl restart apache2
```

### Step 6 — Open in Browser

```
http://<your-server-ip>/todo-app/
```

---

## Testing the Backend

### Test API endpoints via curl

```bash
# Get all tasks
curl http://localhost/todo-app/tasks.php

# Get all categories
curl http://localhost/todo-app/categories.php

# Get stats
curl "http://localhost/todo-app/tasks.php?action=stats"

# Create a task (POST)
curl -X POST http://localhost/todo-app/tasks.php \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Task","priority":"high","status":"pending"}'

# Update a task (PUT)
curl -X PUT http://localhost/todo-app/tasks.php \
  -H "Content-Type: application/json" \
  -d '{"id":1,"status":"completed"}'

# Delete a task (DELETE)
curl -X DELETE "http://localhost/todo-app/tasks.php?id=1"
```

### Expected responses

| Response | Meaning |
|----------|---------|
| `{"success":true,"tasks":[...]}` | ✅ Backend + DB working |
| `{"error":"Connection failed"}` | ❌ Wrong DB credentials |
| `500 Internal Server Error` | ❌ PHP error — check logs |
| `404 Not Found` | ❌ Files not in web root |

### Check Apache error logs

```bash
sudo tail -f /var/log/apache2/error.log
```

---

## API Reference

### Tasks — `tasks.php`

| Method | Query Params | Body | Description |
|--------|-------------|------|-------------|
| GET | `status`, `priority`, `category_id`, `search` | — | List tasks with filters |
| GET | `action=stats` | — | Get task statistics |
| POST | — | JSON task object | Create a task |
| PUT | — | JSON with `id` | Update a task |
| DELETE | `id` | — | Delete a task |

**Task object fields:**

```json
{
  "title": "Buy groceries",
  "description": "Milk, eggs, bread",
  "priority": "low | medium | high",
  "status": "pending | in_progress | completed",
  "category_id": 1,
  "due_date": "2025-12-31"
}
```

### Categories — `categories.php`

| Method | Query Params | Body | Description |
|--------|-------------|------|-------------|
| GET | — | — | List all categories |
| POST | — | `{name, color}` | Create a category |
| DELETE | `id` | — | Delete a category |

---

## Features

- Create, edit, and delete tasks
- Priority levels — Low, Medium, High
- Status tracking — Pending, In Progress, Completed
- Custom categories with color labels
- Due dates with overdue highlighting
- Filter by status, priority, and category
- Live search
- Task statistics dashboard
- Dark UI with Tailwind CSS

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `Connection failed` | Check `database.php` credentials |
| `404 Not Found` | Run `ls /var/www/html/todo-app/` — files may not be copied |
| `php-mysqli` missing | Run `sudo apt install php-mysqli -y && sudo systemctl restart apache2` |
| Blank page | Run `sudo tail /var/log/apache2/error.log` |
| MySQL access denied | Create a dedicated DB user (see Step 4 above) |
| Port 80 in use | Check `sudo systemctl status apache2` |


last is used check webhook trigger
