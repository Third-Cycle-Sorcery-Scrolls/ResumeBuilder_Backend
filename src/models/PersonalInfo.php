<?php
// personal_info Table:
// - id (INT, PRIMARY KEY, AUTO_INCREMENT)
// - resume_id (INT, FOREIGN KEY referencing Resumes(id))
// - full_name (VARCHAR(255))
// - email (VARCHAR(255))
// - phone (VARCHAR(255))
// - address (VARCHAR(255))
// - created_at (TIMESTAMP)
// - updated_at (TIMESTAMP)
// - photo_url (VARCHAR(255)) (optional, URL or path to the uploaded profile picture)
class PersonalInfo
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    public function getByResumeId($resume_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM personal_info WHERE resume_id = ?");
        $stmt->execute([$resume_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function createOrUpdate($resume_id, $full_name, $email, $phone, $address, $photo_url = null)
    {
        // Check if personal info already exists for this resume
        $existing = $this->getByResumeId($resume_id);
        if ($existing) {
            // Update existing record
            $stmt = $this->pdo->prepare("UPDATE personal_info SET full_name = ?, email = ?, phone = ?, address = ?, photo_url = ?, updated_at = NOW() WHERE resume_id = ?");
            return $stmt->execute([$full_name, $email, $phone, $address, $photo_url, $resume_id]);
        } else {
            // Create new record
            $stmt = $this->pdo->prepare("INSERT INTO personal_info (resume_id, full_name, email, phone, address, photo_url, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            return $stmt->execute([$resume_id, $full_name, $email, $phone, $address, $photo_url]);
        }
    }
    public function deleteByResumeId($resume_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM personal_info WHERE resume_id = ?");
        return $stmt->execute([$resume_id]);
    }
}