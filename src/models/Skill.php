<?php
class Skill {
    private $conn;
    private $table = "skills";

    public function __construct($db) {
        $this->conn = $db;
    }

    // FR-11: Add skill
    public function add($resume_id, $skill_name, $proficiency) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (resume_id, skill_name, proficiency) 
                      VALUES (:resume_id, :name, :level)";

            $stmt = $this->conn->prepare($query);

            return $stmt->execute([
                ':resume_id' => (int)$resume_id,
                ':name' => $skill_name,
                ':level' => $proficiency
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    // FR-11: Update skill
    public function update($id, $skill_name, $proficiency) {
        try {
            $query = "UPDATE {$this->table} 
                      SET skill_name = :name, proficiency = :level 
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            return $stmt->execute([
                ':id' => (int)$id,
                ':name' => $skill_name,
                ':level' => $proficiency
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    // FR-11: Delete skill
    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            return $stmt->execute([
                ':id' => (int)$id
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }
}
?>

