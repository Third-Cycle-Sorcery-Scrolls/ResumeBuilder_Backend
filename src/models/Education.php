<?php

class Education
{
    private $conn;
    private $table = 'education';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function sanitize($value)
    {
        return htmlspecialchars(strip_tags($value));
    }

    public function add($resume_id, $institution, $degree, $field_of_study, $start_date, $end_date = null)
    {
        try {
            $query = "INSERT INTO {$this->table} (resume_id, institution, degree, field_of_study, start_date, end_date, created_at, updated_at)\n                      VALUES (:resume_id, :institution, :degree, :field_of_study, :start_date, :end_date, NOW(), NOW())";
            $stmt = $this->conn->prepare($query);

            return $stmt->execute([
                ':resume_id' => (int)$resume_id,
                ':institution' => $this->sanitize($institution),
                ':degree' => $this->sanitize($degree),
                ':field_of_study' => $this->sanitize($field_of_study),
                ':start_date' => $this->sanitize($start_date),
                ':end_date' => $end_date ? $this->sanitize($end_date) : null
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($id, $resume_id, $institution, $degree, $field_of_study, $start_date, $end_date = null)
    {
        try {
            $query = "UPDATE {$this->table}\n                      SET institution = :institution,\n                          degree = :degree,\n                          field_of_study = :field_of_study,\n                          start_date = :start_date,\n                          end_date = :end_date,\n                          updated_at = NOW()\n                      WHERE id = :id AND resume_id = :resume_id";
            $stmt = $this->conn->prepare($query);

            return $stmt->execute([
                ':id' => (int)$id,
                ':resume_id' => (int)$resume_id,
                ':institution' => $this->sanitize($institution),
                ':degree' => $this->sanitize($degree),
                ':field_of_study' => $this->sanitize($field_of_study),
                ':start_date' => $this->sanitize($start_date),
                ':end_date' => $end_date ? $this->sanitize($end_date) : null
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id, $resume_id)
    {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id AND resume_id = :resume_id";
            $stmt = $this->conn->prepare($query);

            return $stmt->execute([
                ':id' => (int)$id,
                ':resume_id' => (int)$resume_id
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByResumeId($resume_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE resume_id = :resume_id ORDER BY start_date DESC");
        $stmt->execute([':resume_id' => (int)$resume_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
