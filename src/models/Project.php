<?php
// projects Table:
// - id (INT, PRIMARY KEY, AUTO_INCREMENT)
// - resume_id (INT, FOREIGN KEY referencing Resumes(id))
// - title (VARCHAR(255))
// - description (TEXT)
// - link (VARCHAR(255))
// - created_at (TIMESTAMP)
// - updated_at (TIMESTAMP)

class Project
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($resume_id, $title, $description, $link)
    {
        $sql = "INSERT INTO projects (resume_id, title, description, link, created_at, updated_at) 
                VALUES (:resume_id, :title, :description, :link, NOW(), NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'resume_id' => $resume_id,
            'title' => $title,
            'description' => $description,
            'link' => $link
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getByResumeId($resume_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE resume_id = ?");
        $stmt->execute([$resume_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $title, $description, $link)
    {
        $stmt = $this->pdo->prepare("UPDATE projects SET title = ?, description = ?, link = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$title, $description, $link, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = ?");
        return $stmt->execute([$id]);
    }
}