<?php

function up(PDO $pdo)
{
    // USERS if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            email VARCHAR(255) UNIQUE,
            password VARCHAR(255),
            profile_picture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
    ");
    //role field for user roles like admin, regular user etc. default to regular "user"
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'
    ");
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    if (!$exists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'user'");
    }

    //insert admin@gmail user if not exists with password pass123 (hashed) and role admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@gmail.com'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn();
    if (!$adminExists) {
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@gmail.com', '" . password_hash('pass123', PASSWORD_DEFAULT) . "', 'admin')");
    }


    // RESUMES
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS resumes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(255),
            template VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");
    //personal info for each resume
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS personal_info (
            id INT AUTO_INCREMENT PRIMARY KEY,
            resume_id INT,
            full_name VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(255),
            address VARCHAR(255),
            photo_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            resume_id INT,
            title VARCHAR(255),
            description TEXT,
            link VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
        );
    ");

    // EDUCATION
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS education (
            id INT AUTO_INCREMENT PRIMARY KEY,
            resume_id INT,
            institution VARCHAR(255),
            degree VARCHAR(255),
            field_of_study VARCHAR(255),
            start_date DATE,
            end_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
        );
    ");
    //add description field once to education table for additional details about the education entry
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'education' AND COLUMN_NAME = 'description'
    ");
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    if (!$exists) {
        $pdo->exec("ALTER TABLE education ADD COLUMN description TEXT");
        }
    


    // WORK EXPERIENCE
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS work_experience (
            id INT AUTO_INCREMENT PRIMARY KEY,
            resume_id INT,
            company VARCHAR(255),
            position VARCHAR(255),
            start_date DATE,
            end_date DATE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
        );
    ");

    // SKILLS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            resume_id INT,
            skill_name VARCHAR(255),
            proficiency VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
        );
    ");

    // Log Table (for tracking user actions and system events):
    // - id (INT, PRIMARY KEY, AUTO_INCREMENT)
    // - user_id (INT, FOREIGN KEY referencing Users(id), nullable for system events)
    // - action (VARCHAR(255)) -- description of the action performed (e.g., "User registered", "Resume created", "Resume updated", etc.)
    // - timestamp (TIMESTAMP) -- when the action occurred
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            action VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );
    ");


    echo "Migration done \n";

}