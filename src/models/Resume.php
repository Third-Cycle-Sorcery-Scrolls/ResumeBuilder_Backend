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
    public function _getAllResumeData($resume_id)
    {
        $response = [];
        $stmt = $this->pdo->prepare("SELECT * FROM resumes WHERE id = ?");
        $stmt->execute([$resume_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $resume_id = $res['id'];
        $resume_title = $res['title'] ?? "Untitled Resume";

        // PERSONAL INFO
        $stmt_pers = $this->pdo->prepare("SELECT * FROM personal_info WHERE resume_id = ?");
        $stmt_pers->execute([$resume_id]);
        $personal = $stmt_pers->fetch(PDO::FETCH_ASSOC);

        // EDUCATION
        $stmt_edu = $this->pdo->prepare("SELECT * FROM education WHERE resume_id = ?");
        $stmt_edu->execute([$resume_id]);
        $educations = $stmt_edu->fetchAll(PDO::FETCH_ASSOC);

        $educationArr = array_map(function ($edu) {
            return [
                "id" => $edu['id'],
                "institution" => $edu['institution'],
                "degree" => $edu['degree'],
                "fieldOfStudy" => $edu['field_of_study'],
                "startDate" => $edu['start_date'],
                "endDate" => $edu['end_date'],
                "description" => $edu['description'] ?? null,
            ];
        }, $educations);

        // EXPERIENCE
        $stmt_work = $this->pdo->prepare("SELECT * FROM work_experience WHERE resume_id = ?");
        $stmt_work->execute([$resume_id]);
        $works = $stmt_work->fetchAll(PDO::FETCH_ASSOC);

        $experienceArr = array_map(function ($exp) {
            return [
                "id" => $exp['id'],
                "jobTitle" => $exp['job_title'],
                "company" => $exp['company'],
                "location" => $exp['location'],
                "startDate" => $exp['start_date'],
                "endDate" => $exp['end_date'],
                "description" => $exp['description'] ?? null,
            ];
        }, $works);

        // SKILLS
        $stmt_skill = $this->pdo->prepare("SELECT * FROM skills WHERE resume_id = ?");
        $stmt_skill->execute([$resume_id]);
        $skills = $stmt_skill->fetchAll(PDO::FETCH_ASSOC);

        $skillsArr = array_map(function ($skill) {
            return [
                "name" => $skill['name'],
                "proficiency" => (int)$skill['proficiency'],
            ];
        }, $skills);

        // PROJECTS
        $stmt_proj = $this->pdo->prepare("SELECT * FROM projects WHERE resume_id = ?");
        $stmt_proj->execute([$resume_id]);
        $projects = $stmt_proj->fetchAll(PDO::FETCH_ASSOC);

        $projectsArr = array_map(function ($proj) {
            return [
                "id" => $proj['id'],
                "title" => $proj['title'],
                "description" => $proj['description'],
                "link" => $proj['link'],
            ];
        }, $projects);

        // BUILD FINAL OBJECT
        $response[] = [
            "id" => $resume_id,
            "title"=> $resume_title,
            "personal" => [
                "fullName" => $personal['full_name'] ?? "",
                "email" => $personal['email'] ?? "",
                "phone" => $personal['phone'] ?? "",
                "address" => $personal['address'] ?? "",
                "photoUrl" => $personal['photo_url'] ?? null,
                "resumeTitle" => $res['title'] ?? "",
            ],
            "education" => $educationArr,
            "experience" => $experienceArr,
            "skills" => $skillsArr,
            "projects" => $projectsArr,
            "selectedTemplate" => $res['template'] ?? "modern",
        ];
        return $response;
    }

    public function getById($id)
    {
        $data = $this->_getAllResumeData($id);
        return $data ? $data[0] : null;
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

    public function getAllResumesByUserIdWithMetaData($user_id)
{
    $response = [];

    $stmt = $this->pdo->prepare("SELECT * FROM resumes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$resumes) {
        return [];
    }

    foreach ($resumes as $res) {
        $resume_id = $res['id'];
        $resume_title = $res['title'] ?? "Untitled Resume";

        // PERSONAL INFO
        $stmt_pers = $this->pdo->prepare("SELECT * FROM personal_info WHERE resume_id = ?");
        $stmt_pers->execute([$resume_id]);
        $personal = $stmt_pers->fetch(PDO::FETCH_ASSOC);

        // EDUCATION
        $stmt_edu = $this->pdo->prepare("SELECT * FROM education WHERE resume_id = ?");
        $stmt_edu->execute([$resume_id]);
        $educations = $stmt_edu->fetchAll(PDO::FETCH_ASSOC);

        $educationArr = array_map(function ($edu) {
            return [
                "id" => $edu['id'],
                "institution" => $edu['institution'],
                "degree" => $edu['degree'],
                "fieldOfStudy" => $edu['field_of_study'],
                "startDate" => $edu['start_date'],
                "endDate" => $edu['end_date'],
                "description" => $edu['description'] ?? null,
            ];
        }, $educations);

        // EXPERIENCE
        $stmt_work = $this->pdo->prepare("SELECT * FROM work_experience WHERE resume_id = ?");
        $stmt_work->execute([$resume_id]);
        $works = $stmt_work->fetchAll(PDO::FETCH_ASSOC);

        $experienceArr = array_map(function ($exp) {
            return [
                "id" => $exp['id'],
                "jobTitle" => $exp['job_title'],
                "company" => $exp['company'],
                "location" => $exp['location'],
                "startDate" => $exp['start_date'],
                "endDate" => $exp['end_date'],
                "description" => $exp['description'] ?? null,
            ];
        }, $works);

        // SKILLS
        $stmt_skill = $this->pdo->prepare("SELECT * FROM skills WHERE resume_id = ?");
        $stmt_skill->execute([$resume_id]);
        $skills = $stmt_skill->fetchAll(PDO::FETCH_ASSOC);

        $skillsArr = array_map(function ($skill) {
            return [
                "name" => $skill['name'],
                "proficiency" => (int)$skill['proficiency'],
            ];
        }, $skills);

        // PROJECTS
        $stmt_proj = $this->pdo->prepare("SELECT * FROM projects WHERE resume_id = ?");
        $stmt_proj->execute([$resume_id]);
        $projects = $stmt_proj->fetchAll(PDO::FETCH_ASSOC);

        $projectsArr = array_map(function ($proj) {
            return [
                "id" => $proj['id'],
                "title" => $proj['title'],
                "description" => $proj['description'],
                "link" => $proj['link'],
            ];
        }, $projects);

        // BUILD FINAL OBJECT
        $response[] = [
            "id" => $resume_id,
            "title"=> $resume_title,
            "personal" => [
                "fullName" => $personal['full_name'] ?? "",
                "email" => $personal['email'] ?? "",
                "phone" => $personal['phone'] ?? "",
                "address" => $personal['address'] ?? "",
                "photoUrl" => $personal['photo_url'] ?? null,
                "resumeTitle" => $res['title'] ?? "",
            ],
            "education" => $educationArr,
            "experience" => $experienceArr,
            "skills" => $skillsArr,
            "projects" => $projectsArr,
            "selectedTemplate" => $res['template'] ?? "modern",
        ];
    }

    return $response;
}

public function getAllResumeByUserId($user_id){
    $stmt = $this->pdo->prepare("SELECT id, title,template,created_at,updated_at FROM resumes WHERE user_id = ?");
    $stmt->execute([$user_id]);

    #front expection scheme:-
    // {
    //   id: 2,
    //   title: "Backend Developer Resume",
    //   template: "Classic",
    //   updated: "5 days ago",
    // },

    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = array_map(function($res){
        return [
            "id" => $res['id'],
            "title" => $res['title'] ?? "Untitled Resume",
            "template" => $res['template'] ?? "modern",
            "updated" => $res['updated_at'],
        ];
    }, $resumes);
    return $response;

}
}


