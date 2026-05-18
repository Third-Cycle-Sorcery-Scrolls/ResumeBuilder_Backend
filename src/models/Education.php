<?php
  // // EDUCATION
    // $pdo->exec("
    //     CREATE TABLE IF NOT EXISTS education (
    //         id INT AUTO_INCREMENT PRIMARY KEY,
    //         resume_id INT,
    //         institution VARCHAR(255),
    //         degree VARCHAR(255),
    //         field_of_study VARCHAR(255),
    //         start_date DATE,
    //         end_date DATE,
    //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    //         FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
    //     );
    // ");
    class Education {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

        public function create($resume_id, $institution, $degree, $field_of_study, $start_date, $end_date, $description)
    {
        $sql = "INSERT INTO education (resume_id, institution, degree, field_of_study, start_date, end_date) 
                VALUES (:resume_id, :institution, :degree, :field_of_study, :start_date, :end_date)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'resume_id' => $resume_id,
            'institution' => $institution,
            'degree' => $degree,
            'field_of_study' => $field_of_study,
            'start_date' => $start_date,
            'end_date' => $end_date,
            // 'description' => $description
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getAll()
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM education
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM education WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $institution, $degree, $field_of_study, $start_date, $end_date, $description)
    {
        $stmt = $this->pdo->prepare("
            UPDATE education SET institution = ?, degree = ?, field_of_study = ?, start_date = ?, end_date = ?, description = ?
            WHERE id = ?
        ");
        return $stmt->execute([$institution, $degree, $field_of_study, $start_date, $end_date, $description, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM education WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }


    }