<?php

class ActivityLog {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function insert($user_id, $action, $entity_type, $entity_id, $metadata, $ip_address, $user_agent) {
        $stmt = $this->conn->prepare("
            INSERT INTO activity_logs 
                (user_id, action, entity_type, entity_id, metadata, ip_address, user_agent)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $action,
            $entity_type,
            $entity_id,
            $metadata ? json_encode($metadata) : null,
            $ip_address,
            $user_agent
        ]);
    }

    public function getAll($filters = []) {
        $sql = "SELECT * FROM activity_logs WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $sql .= " AND entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}