-- Todo App Database Schema
-- Run this file to set up the database

CREATE DATABASE IF NOT EXISTS todo_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE todo_app;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#6366f1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Seed default categories
INSERT INTO categories (name, color) VALUES
    ('Personal', '#f59e0b'),
    ('Work', '#3b82f6'),
    ('Shopping', '#10b981'),
    ('Health', '#ef4444');

-- Seed sample tasks
INSERT INTO tasks (title, description, category_id, priority, status, due_date) VALUES
    ('Buy groceries', 'Milk, eggs, bread, vegetables', 3, 'low', 'pending', CURDATE()),
    ('Finish project report', 'Complete Q4 analysis report for the team', 2, 'high', 'in_progress', DATE_ADD(CURDATE(), INTERVAL 2 DAY)),
    ('Morning workout', '30 min cardio + weights', 4, 'medium', 'completed', CURDATE()),
    ('Read a book', 'Continue reading Atomic Habits', 1, 'low', 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY));
