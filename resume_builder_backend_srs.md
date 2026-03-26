# Resume Builder – Backend Technical Implementation Plan With pure PHP

## 1. Project Overview

The Resume Builder project aims to create a web application that allows users to easily create, customize, and download their resumes. The backend will be responsible for handling user authentication, resume data management, and generating downloadable resume files in various formats (only pdf support).

## 2. System Overview

- The system follows a client-server architecture:
  - Frontend: React.js application that interacts with the backend via RESTful APIs.
  - Backend: Pure PHP application that handles all server-side logic, including user authentication, resume data management, and resume generation.
  - Database: MySQL database to store user information and resume data securely.

## 3. Scope of Work

1. **User Authentication**: Implement a secure user authentication system that allows users to register, log in, and manage their accounts.
2. **Resume Data Management**: Create a system for users to input and manage their resume data, including personal information, work experience, education, skills, and other relevant sections.
3. **Resume Generation**: Develop a feature that generates a downloadable resume file in PDF format based on the user's input.
4. **API Endpoints**: Design and implement RESTful API endpoints for all functionalities, including user authentication, resume data management, and resume generation.
5. **Database Design**: Create a database schema to store user information and resume data securely.
6. **Security Measures**: Implement security best practices to protect user data and prevent unauthorized access.

## 4. Functional Requirements(For MVP Backend)

4. 1. **User Management**:

- FR-1: Users can register with a unique email and password.
- FR-2: Users can log in using their email and password.
- FR-3: Users can log out of their accounts.
- FR-4: Users can update their profile information (name, email, password).
- FR-5: upload profile picture and store it in the database or a file storage system(optional but recommended to fullfill the teacher's requirement of file upload).

4. 2. **Resume Management**:
   - FR-5: Users can create a new resume by providing necessary information (personal details, work experience, education, skills). system will generate a unique resume ID for each resume.
   - FR-6: Users can view a list of their created resumes.
   - FR-7: Users can edit and update their existing resumes.
   - FR-8: Users can delete their resumes.
5. 3. **Education, work experiance, skills sections management**:
   - FR-9: Users can add, edit, and delete entries in the education section of their resume.
   - FR-10: Users can add, edit, and delete entries in the work experience section of their resume.
   - FR-11: Users can add, edit, and delete entries in the skills section of their resume.
6. 4. **Resume Templates**:
   - FR-12: Users can choose from a selection of predefined resume templates when creating or editing their resumes.
   - FR-13: Users can preview their resume in the selected template before downloading using their own data.
7. 5. **Resume Download**:
   - FR-14: Users can download their resume in PDF format.

## 5. Non-Functional Requirements

- NFR-1: The backend should be developed using pure PHP without any frameworks.
- NFR-2: The backend should use MySQL for data storage.
- NFR-3: The backend should follow RESTful API design principles.
- NFR-4: The backend should implement security best practices, including password hashing and input validation.
- NFR-5: The backend should be scalable to handle multiple users and resumes efficiently
- NFR-6: The backend should have proper error handling and return appropriate HTTP status codes for different scenarios.

## Api Endpoints(For MVP Backend)

### User Authentication Endpoints

```
Endpoint                 Method       description
/user/register           POST         Register a new user
/user/login              POST         Log in a user and return an authentication token
/user/logout             POST         Log out a user and invalidate the authentication token
/user/profile            GET          Get the authenticated user's profile information
/user/profile/update     PUT          Update the authenticated user's profile information

```

### Resume Management Endpoints

```
Endpoint                 Method       description
/resume                  POST         Create a new resume
/resume                  GET          Get a list of the authenticated user's resumes
/resume/{id}             GET          Get details of a specific resume by ID
/resume/{id}             PUT          Update a specific resume by ID
/resume/{id}             DELETE       Delete a specific resume by ID
/resume/upload-profile-picture POST         Upload a profile picture for the user (optional)

```

### Education, Work Experience, Skills Management Endpoints

```
Endpoint                 Method       description
/resume/{id}/education   POST         Add an education entry to a specific resume
/resume/{id}/education   PUT          Update an education entry in a specific resume
/resume/{id}/education   DELETE       Delete an education entry from a specific resume
/resume/{id}/work        POST         Add a work experience entry to a specific resume
/resume/{id}/work        PUT          Update a work experience entry in a specific resume
/resume/{id}/work        DELETE       Delete a work experience entry from a specific resume
/resume/{id}/skills      POST         Add a skill entry to a specific resume
/resume/{id}/skills      PUT          Update a skill entry in a specific resume
/resume/{id}/skills      DELETE       Delete a skill entry from a specific resume
```

### Resume Template and Download Endpoints

```
Endpoint                 Method       description
/resume/{id}/template    POST         Set a resume template for a specific resume
/resume/{id}/preview     GET          Preview a specific resume with the selected template
/resume/{id}/download    GET          Download a specific resume in PDF format
```

## 6. Database Design

- Mysql Database accessed using PDO for secure database interactions.
- Database schema for the Resume Builder application:

```
Users Table:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR(255))
- email (VARCHAR(255), UNIQUE)
- password (VARCHAR(255))
- profile_picture (VARCHAR(255)) (optional)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
Resumes Table:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY referencing Users(id))
- title (VARCHAR(255)) -- title of the resume: "Software Engineer Resume", "Marketing Manager Resume", etc.
- template (VARCHAR(255)) -- template name or identifier for the resume like "template1", "template2", etc.
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
Education Table:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- resume_id (INT, FOREIGN KEY referencing Resumes(id))
- institution (VARCHAR(255))
- degree (VARCHAR(255))
- field_of_study (VARCHAR(255))
- start_date (DATE)
- end_date (DATE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
Work Experience Table:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- resume_id (INT, FOREIGN KEY referencing Resumes(id))
- company (VARCHAR(255))
- position (VARCHAR(255))
- start_date (DATE)
- end_date (DATE)
- description (TEXT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
Skills Table:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- resume_id (INT, FOREIGN KEY referencing Resumes(id))
- skill_name (VARCHAR(255))
- proficiency (VARCHAR(255))
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### ERD Diagram:

```
1. Users
   - pk: id
   - name
   - email
   - password
   - profile_picture(optional)
   - created_at
   - updated_at
2. Resumes
   - pk: id
   - fk: user_id -> Users(id)
   - title
   - template
   - created_at
   - updated_at
   relationship: one user can have many resumes (1:N)
3. Education
   - pk: id
   - fk: resume_id -> Resumes(id)
   - institution
   - degree
   - field_of_study
   - start_date
   - end_date
   - created_at
   - updated_at
   relationship: one resume can have many education entries (1:N)
4. Work Experience
   - pk: id
   - fk: resume_id -> Resumes(id)
   - company
   - position
   - start_date
   - end_date
   - description
   - created_at
   - updated_at
   relationship: one resume can have many work experience entries (1:N)
5. Skills
   - pk: id
   - fk: resume_id -> Resumes(id)
   - skill_name
   - proficiency
   - created_at
   - updated_at
   relationship: one resume can have many skills entries (1:N)
```

## 7. Data Requirement

- resume(title, template)
- education (institution, degree, field of study, start date, end date)
- work experience (company, position, start date, end date, description)
- skills (skill name, proficiency)
- user profile information (name, email, password, profile picture)

## 8. Security Measures

- Passwords will be hashed using a secure hashing algorithm (e.g., bcrypt) before storing in the database.
- Input validation will be implemented to prevent SQL injection and other common security vulnerabilities.
- Authentication tokens will be used to secure API endpoints and ensure that only authenticated users can access their data.
- Proper error handling will be implemented to prevent information leakage and ensure that sensitive data is not exposed in error messages.

## 9. Recommended Folder Structure for the Backend

```
/resume-builder-backend/
│
├── /api/                     # Public API endpoints (accessible via HTTP)
│   ├── /user/
│   │   ├── register.php      # Register a new user
│   │   ├── login.php         # User login (returns auth token)
│   │   ├── logout.php        # Log out a user / invalidate token
│   │   ├── profile.php       # Get authenticated user profile
│   │   └── profile-update.php # Update user profile info
│   │
│   ├── /resume/
│   │   ├── create.php        # Create a new resume
│   │   ├── list.php          # Get list of user's resumes
│   │   ├── get.php           # Get resume details by ID
│   │   ├── update.php        # Update resume by ID
│   │   ├── delete.php        # Delete resume by ID
│   │   ├── upload-profile-picture.php # Upload user photo
│   │   ├── /education/
│   │   │   ├── add.php       # Add education entry
│   │   │   ├── update.php    # Update education entry
│   │   │   └── delete.php    # Delete education entry
│   │   ├── /work/
│   │   │   ├── add.php       # Add work experience
│   │   │   ├── update.php    # Update work experience
│   │   │   └── delete.php    # Delete work experience
│   │   ├── /skills/
│   │   │   ├── add.php       # Add skill
│   │   │   ├── update.php    # Update skill
│   │   │   └── delete.php    # Delete skill
│   │   ├── template.php      # Set resume template
│   │   ├── preview.php       # Preview resume
│   │   └── download.php      # Download resume PDF
│
├── /config/                  # Configuration files
│   └── database.php           # Database connection (PDO recommended)
│
├── /models/                  # Database interaction / business logic
│   ├── User.php               # User model
│   ├── Resume.php             # Resume model
│   ├── Education.php          # Education model
│   ├── WorkExperience.php     # Work experience model
│   └── Skill.php              # Skills model
│
├── /controllers/             # Optional: API logic / request handling
│   ├── UserController.php
│   └── ResumeController.php
│
├── /helpers/                 # Utility functions
│   ├── response.php           # JSON response helpers
│   ├── validation.php         # Input validation functions
│   ├── auth.php               # Authentication helper (token handling)
│   └── file_upload.php        # File upload helper
│
├── /uploads/                 # Store uploaded profile photos
│   └── /profile_pictures/
│
└── /vendor/                  # Optional: third-party libraries (e.g., Dompdf, TCPDF)
```

- Folder Notes:-

```
| Folder            | Purpose                                                                                                      |
| ----------------- | ------------------------------------------------------------------------------------------------------------ |
| **/api/**         | Public-facing endpoints. Handles all incoming requests. Organized by resource (`user`, `resume`).            |
| **/config/**      | Centralized configuration, e.g., database connection, constants.                                             |
| **/models/**      | Handles all database operations (CRUD). Each resource has a separate model.                                  |
| **/controllers/** | Optional separation of business logic from models. Useful if logic grows beyond CRUD.                        |
| **/helpers/**     | Reusable utility functions: JSON formatting, validation, authentication, file handling.                      |
| **/uploads/**     | Stores user-uploaded files (e.g., profile photos). Use `.htaccess` or move outside public root for security. |
| **/vendor/**      | For third-party libraries (PDF generation). Optional in MVP but good for later enhancements.                 |
```

- Addational Notes:
  - Routing: For Now, each endpoint can be a separate PHP file in the `/api/` directory.
  - Database Access: Use PDO for secure database interactions. Each model can have methods for CRUD operations.
  - JSON Responses: All API endpoints should return JSON responses with appropriate HTTP status codes. this is the general structure of the JSON response:

  ```
  {
    "code": 200, // HTTP status code
    "success": true/false,
    "message": "Descriptive message about the result",
    "data": {} // Optional, contains relevant data for successful responses
    "error": {} // Optional, contains error details for failed responses
  }
  ```

  - security:
    - use prepared statements with PDO to prevent SQL injection.
    - Hash passwords using `password_hash()` and verify with `password_verify()`.
    - Implement token-based authentication (e.g., JWT) for securing API endpoints.
    - Validate all user inputs to prevent XSS and other vulnerabilities.
    - For file uploads, validate file types and sizes, and store files securely (e.g., outside the web root or with restricted access).

- Error Codes:
  - 200 OK: Successful request.
  - 201 Created: Resource successfully created (e.g., new resume).
  - 400 Bad Request: Invalid input or missing required fields.
  - 401 Unauthorized: Authentication required or invalid token.
  - 403 Forbidden: User does not have permission to access the resource.
  - 404 Not Found: Resource not found (e.g., resume ID does not exist).
  - 500 Internal Server Error: Unexpected server error.

## 10. Conclusion

This technical implementation plan outlines the backend development of the Resume Builder application using pure PHP. The plan covers the functional and non-functional requirements, API endpoints, database design, data requirements, and security measures necessary to build a secure and efficient backend for the application. By following this plan, we can ensure that the backend is developed in a structured and organized manner, meeting the needs of the users while adhering to best practices in web development.

# Task classification For 6 developers(dev-1,...,dev-6)

## Dev-1: User Authentication & Profile

```
FR-1: User registration (/user/register)
FR-2: User login (/user/login)
FR-3: User logout (/user/logout)
FR-4: Update profile info (/user/profile-update)
FR-5: Upload profile picture (/resume/upload-profile-picture)

Focus: Auth, tokens, password hashing, profile management.
```

## Dev-2: Resume CRUD Core

```
FR-5: Create a new resume (/resume/create)
FR-6: Get list of resumes (/resume)
FR-7: Update resume (/resume/{id}/update)
FR-8: Delete resume (/resume/{id}/delete)
FR-14: Download resume PDF (/resume/{id}/download)

Focus: Resume entity, PDF generation, basic CRUD.
```

## Dev-3: Education Section

```
FR-9: Add education entry (/resume/{id}/education/add)
FR-9: Update education entry (/resume/{id}/education/update)
FR-9: Delete education entry (/resume/{id}/education/delete)

Focus: Education table, CRUD methods, validations.
```

## Dev-4: Work Experience Section

```
FR-10: Add work experience entry (/resume/{id}/work/add)
FR-10: Update work experience entry (/resume/{id}/work/update)
FR-10: Delete work experience entry (/resume/{id}/work/delete)

Focus: WorkExperience table, CRUD methods, validations.
```

## Dev-5: Skills Section

```
FR-11: Add skill entry (/resume/{id}/skills/add)
FR-11: Update skill entry (/resume/{id}/skills/update)
FR-11: Delete skill entry (/resume/{id}/skills/delete)

Focus: Skills table, CRUD methods, validation, proficiency levels.
```

## Dev-6: Resume Templates & Preview

```
FR-12: Choose / set resume template (/resume/{id}/template)
FR-13: Preview resume (/resume/{id}/preview)

Focus: Frontend integration, template HTML rendering, dynamic data insertion.
```

- Scheduling Notes:
  - Dev-1 and Dev-2 can start immediately as they cover core functionalities.
  - Dev-3, Dev-4, and Dev-5 can begin once the resume creation endpoint is functional, as they depend on the resume ID.
  - Dev-6 can start after the basic resume data structure is in place to ensure templates can access the necessary data for rendering.

### Final Note From the project lead:

- Ensure that you are following the coding standards and best practices for PHP development.
- Regularly commit your code to the version control system and provide clear commit messages.
- Communicate with the team if you encounter any issues or need clarification on the requirements.
- Aim to complete your assigned tasks within the given timeline, but prioritize quality and security in your implementation.
- Be prepared to assist other team members if they need help with their tasks, especially if there are dependencies between your work and theirs.
- Remember to write clean, maintainable code and include comments where necessary to explain complex logic or decisions.
- Finally, ensure that you are testing your code thoroughly before submitting it for review to catch any bugs or issues early on.

**Thank you all for your hard work and dedication to this project. Let's build a great Resume Builder application together!**
