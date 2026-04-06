<?php

class Resume
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($user_id, $title, $template)
    {
        $sql = "INSERT INTO resumes (user_id, title, template, created_at, updated_at) 
                VALUES (:user_id, :title, :template, NOW(), NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'title' => $title,
            'template' => $template  
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getAll()
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM resumes
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM resumes WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $title, $template)
    {
        $stmt = $this->pdo->prepare("
            UPDATE resumes SET title = ?, template = ?
            WHERE id = ?
        ");
        return $stmt->execute([$title, $template, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM resumes WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
}