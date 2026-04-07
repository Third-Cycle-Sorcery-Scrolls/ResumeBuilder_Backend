<?
// user seeding file
return function($pdo){
    $pdo->exec("
        INSERT INTO users (name, email, password) VALUES 
        ('John Doe', 'john.doe@example.com', 'password123'),
        ('Jane Smith', 'jane.smith@example.com', 'password456')
    ");
};