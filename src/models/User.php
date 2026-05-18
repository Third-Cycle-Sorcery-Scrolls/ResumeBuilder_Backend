<?php
    class User {
        private $conn;
        private $table = 'users';

        public $id;
        public $email;
        public $password;
        public $name;
        public $profile_picture;

        public function __construct($conn){
            $this->conn = $conn;
        }

        public function create(){
            $query = "INSERT INTO " . $this->table . "(email, password) VALUES (:email, :password)";
            $stmt = $this->conn->prepare($query);

            $this->password = password_hash($this->password, PASSWORD_BCRYPT);

            return $stmt->execute([
                ':email' => $this->email,
                ':password' => $this->password
            ]);
        }

        //analytics:- by user id
        //total_resumes,last_updated,most used template
        //
        public function getAnalytics($userId) {
            $query = "SELECT 
                        (SELECT COUNT(*) FROM resumes WHERE user_id = :userId) AS total_resumes,
                        (SELECT MAX(updated_at) FROM resumes WHERE user_id = :userId) AS last_updated,
                        (SELECT template FROM resumes WHERE user_id = :userId GROUP BY template ORDER BY COUNT(*) DESC LIMIT 1) AS most_used_template
                    ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':userId' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

?>