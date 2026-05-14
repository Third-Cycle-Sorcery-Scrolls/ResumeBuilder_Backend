# Resume Builder Backend API

A RESTful Resume Builder backend built with **Pure PHP** and **MySQL**.
This project allows users to create, manage, preview, and download professional resumes in PDF format.

The backend provides secure authentication, resume management, template selection, PDF generation, and modular CRUD operations for resume sections such as education, work experience, and skills.

---

## Features

### Authentication & User Management

* User registration
* User login/logout
* Secure password hashing (`password_hash`)
* Token-based authentication (JWT/session token)
* Profile update
* Profile picture upload

### Resume Management

* Create resumes
* Update resumes
* Delete resumes
* View all resumes
* Resume templates support
* Resume preview
* Download resume as PDF

### Resume Sections

* Education CRUD
* Work Experience CRUD
* Skills CRUD

### Security

* Prepared statements using PDO
* Input validation & sanitization
* Secure authentication
* File upload validation
* Proper HTTP status codes & JSON responses

---

# Tech Stack

| Technology     | Usage            |
| -------------- | ---------------- |
| PHP            | Backend API      |
| MySQL          | Database         |
| PDO            | Secure DB access |
| React.js       | Frontend client  |
| JWT / Tokens   | Authentication   |
| Dompdf / TCPDF | PDF generation   |

---

# Project Structure

```bash
resume-builder-backend/
│
├── api/
│   ├── user/
│   ├── resume/
│   └── ...
│
├── config/
│   └── database.php
│
├── models/
│   ├── User.php
│   ├── Resume.php
│   ├── Education.php
│   ├── WorkExperience.php
│   └── Skill.php
│
├── controllers/
│
├── helpers/
│
├── uploads/
│   └── profile_pictures/
│
└── vendor/
```

---

# System Architecture

```text
Frontend (React.js)
        │
        ▼
REST API (Pure PHP Backend)
        │
        ▼
MySQL Database
```

---

# Functional Requirements (MVP)

## User Authentication

* Register account
* Login
* Logout
* Update profile
* Upload profile picture

## Resume Management

* Create resume
* Edit resume
* Delete resume
* List resumes

## Resume Sections

* Education management
* Work experience management
* Skills management

## Resume Templates

* Choose template
* Preview template

## Resume Download

* Export resume as PDF

---

# API Endpoints

## Authentication Endpoints

| Endpoint               | Method | Description    |
| ---------------------- | ------ | -------------- |
| `/user/register`       | POST   | Register user  |
| `/user/login`          | POST   | Login user     |
| `/user/logout`         | POST   | Logout user    |
| `/user/profile`        | GET    | Get profile    |
| `/user/profile/update` | PUT    | Update profile |

---

## Resume Endpoints

| Endpoint                         | Method | Description          |
| -------------------------------- | ------ | -------------------- |
| `/resume`                        | POST   | Create resume        |
| `/resume`                        | GET    | Get user resumes     |
| `/resume/{id}`                   | GET    | Get resume details   |
| `/resume/{id}`                   | PUT    | Update resume        |
| `/resume/{id}`                   | DELETE | Delete resume        |
| `/resume/upload-profile-picture` | POST   | Upload profile image |

---

## Education Endpoints

| Endpoint                 | Method |
| ------------------------ | ------ |
| `/resume/{id}/education` | POST   |
| `/resume/{id}/education` | PUT    |
| `/resume/{id}/education` | DELETE |

---

## Work Experience Endpoints

| Endpoint            | Method |
| ------------------- | ------ |
| `/resume/{id}/work` | POST   |
| `/resume/{id}/work` | PUT    |
| `/resume/{id}/work` | DELETE |

---

## Skills Endpoints

| Endpoint              | Method |
| --------------------- | ------ |
| `/resume/{id}/skills` | POST   |
| `/resume/{id}/skills` | PUT    |
| `/resume/{id}/skills` | DELETE |

---

## Template & Download Endpoints

| Endpoint                | Method | Description    |
| ----------------------- | ------ | -------------- |
| `/resume/{id}/template` | POST   | Set template   |
| `/resume/{id}/preview`  | GET    | Preview resume |
| `/resume/{id}/download` | GET    | Download PDF   |

---

# Database Design

## Users Table

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Resumes Table

```sql
CREATE TABLE resumes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(255),
    template VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## Education Table

```sql
CREATE TABLE education (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT,
    institution VARCHAR(255),
    degree VARCHAR(255),
    field_of_study VARCHAR(255),
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id)
);
```

---

## Work Experience Table

```sql
CREATE TABLE work_experience (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT,
    company VARCHAR(255),
    position VARCHAR(255),
    start_date DATE,
    end_date DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id)
);
```

---

## Skills Table

```sql
CREATE TABLE skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT,
    skill_name VARCHAR(255),
    proficiency VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id)
);
```

---

# JSON Response Format

```json
{
  "code": 200,
  "success": true,
  "message": "Operation successful",
  "data": {},
  "error": {}
}
```

---

# Security Practices

* PDO Prepared Statements
* Password hashing with bcrypt
* Token-based authentication
* Input validation
* XSS prevention
* File upload validation
* Proper error handling

---

# HTTP Status Codes

| Code | Meaning               |
| ---- | --------------------- |
| 200  | OK                    |
| 201  | Resource Created      |
| 400  | Bad Request           |
| 401  | Unauthorized          |
| 403  | Forbidden             |
| 404  | Not Found             |
| 500  | Internal Server Error |

---

# Installation

## 1. Clone Repository

```bash
git clone <repository-url>
cd resume-builder-backend
```

---

## 2. Configure Database

Create a MySQL database:

```sql
CREATE DATABASE resume_builder;
```

Update database credentials inside:

```bash
/config/database.php
```

---

## 3. Run Server

Using PHP built-in server:

```bash
php -S localhost:8000
```

---



# Participants

| Name | GitHub Username | Student ID |
|---|---|---|
| Ahlam Ahmed | Ahlamv | ETS115/30 |
| Alehegne Geta | Alehegne | ETS0130/16 |
| Amanawit Behailu | Amanawit22 | ETS0135/16 |
| Amanuel Ayele | Manu3lde | ETS0140/16 |
| Amanuel Getachew | Amanuel-Getachew-K | ETS0148/16 |
| Amanuel Habtamu | AmanuelHab | ETS0149/16 |

---

# Future Improvements

* Multiple resume themes
* Resume sharing links
* AI-assisted resume suggestions
* Export to DOCX

---

# License

This project is developed for educational and academic purposes.

---
