<?php
    class User {
        private $conn;

        private $id;
        private $email;
        private $password;
        private $username;
        private $role;
        private $profile_picture;

        public function __construct($db){
            $this->conn = $db;
        }

        public function findByEmailOrUsername($email, $username){
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
            $stmt->execute([':email' => $email, ':username' => $username]);
            $data = $stmt->fetch();

            if($data){
                $this->id = $data['id'];
                $this->email = $data['email'];
                $this->password = $data['password'];
                $this->username = $data['username'];
                $this->profile_picture = $data['profile_picture'];
                return $this;
            }
            return null;
        }
        public function findByEmail($email){
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $data = $stmt->fetch();

            if($data){
                $this->id = $data['id'];
                $this->email = $data['email'];
                $this->password = $data['password'];
                $this->username = $data['username'];
                $this->profile_picture = $data['profile_picture'];
                return $this;
            }
            return null;
        }

        public function create($username, $email, $passwordHash){
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([':username' => $username, ':email' => $email, ':password' => $passwordHash]);
            $data = $stmt->fetch();
            return $data;

            if($data){
                $this->id = $data['id'];
                $this->email = $data['email'];
                $this->password = $data['password'];
                $this->username = $data['username'];
                $this->profile_picture = $data['profile_picture'];
                return $this;
            }
            return null;
        }

        public function findById($id){
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();

            if($data){
                $this->id = $data['id'];
                $this->email = $data['email'];
                $this->password = $data['password'];
                $this->username = $data['username'];
                $this->profile_picture = $data['profile_picture'];
                return $this;
            }
            return null;
        }

        public function getUsername() {return $this->username;}
        public function getEmail() {return $this->email;}
        public function getId() {return $this->id;}
        public function getPassword() {return $this->password;}
        public function getRole() {return $this->role;}

        public function setUsername($username) {$this->username = $username;}
        public function setEmail($email) {$this->email = $email;}
    }

?>