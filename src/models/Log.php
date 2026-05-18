<?php
// Log Table (for tracking user actions and system events):
// - id (INT, PRIMARY KEY, AUTO_INCREMENT)
// - user_id (INT, FOREIGN KEY referencing Users(id), nullable for system events)
// - action (VARCHAR(255)) -- description of the action performed (e.g., "User registered", "Resume created", "Resume updated", etc.)
// - timestamp (TIMESTAMP) -- when the action occurred

class Log {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    public function create($user_id=null, $action)
    {
        $sql = "INSERT INTO logs (user_id, action, timestamp) 
                VALUES (:user_id, :action, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'action' => $action
        ]);
        return $this->pdo->lastInsertId();
    }
    public function getAll()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM logs ORDER BY timestamp DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}