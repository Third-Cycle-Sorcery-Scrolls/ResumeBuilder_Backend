<?php

function up(PDO $pdo)
{
    // USERS
    $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            email VARCHAR(255) UNIQUE,
            password VARCHAR(255),
            profile_picture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
    ");

    // RESUMES
    $pdo->exec("
        CREATE TABLE resumes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(255),
            template VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    // EDUCATION
    $pdo->exec("
        CREATE TABLE education (
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

    // WORK EXPERIENCE
    $pdo->exec("
        CREATE TABLE work_experience (
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
        CREATE TABLE skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            resume_id INT,
            skill_name VARCHAR(255),
            proficiency VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
        );
    ");

    echo "Migration done \n";
}