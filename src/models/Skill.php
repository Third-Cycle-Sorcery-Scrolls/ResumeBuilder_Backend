<?php
class Skill {
    private $conn;
    private $table = "skills";

    public function __construct($db) {
        $this->conn = $db;
    }

    // FR-11: Add skill
    public function add($resume_id, $skill_name, $proficiency) {
        $query = "INSERT INTO " . $this->table . " (resume_id, skill_name, proficiency) VALUES (:resume_id, :name, :level)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':resume_id' => $resume_id,
            ':name' => htmlspecialchars(strip_tags($skill_name)),
            ':level' => htmlspecialchars(strip_tags($proficiency))
        ]);
    }

    // FR-11: Update skill
    public function update($id, $skill_name, $proficiency) {
        $query = "UPDATE " . $this->table . " SET skill_name = :name, proficiency = :level WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':name' => htmlspecialchars(strip_tags($skill_name)),
            ':level' => htmlspecialchars(strip_tags($proficiency))
        ]);
    }

    // FR-11: Delete skill
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}

