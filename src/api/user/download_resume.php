<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/Logger.php';
require_once __DIR__ . '/../../models/Resume.php';

// CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    jsonResponse(405, false, 'Method Not Allowed');
    exit();
}

// Authenticate user
try {
    $user = authenticateUser();
    $userId = $user['userId'];
} catch (Exception $e) {
    header('Content-Type: application/json');
    jsonResponse(401, false, 'Unauthorized: Authentication required');
    exit();
}

$resumeId = $_GET['resume_id'] ?? null;

if (!$resumeId) {
    header('Content-Type: application/json');
    jsonResponse(400, false, 'resume_id query parameter is required');
    exit();
}

try {
    // Use Resume model to get all resume data
    $resumeModel = new Resume($conn);
    $resumeData = $resumeModel->_getAllResumeData($resumeId, $userId);
    
    if (!$resumeData) {
        header('Content-Type: application/json');
        jsonResponse(404, false, 'Resume not found or access denied');
        exit();
    }
    
    // Build text content for PDF
    $lines = [];
    
    // Header with title and user info
    $lines[] = str_repeat("=", 70);
    $lines[] = strtoupper($resumeData['title'] ?? 'Resume');
    $lines[] = str_repeat("=", 70);
    $lines[] = "";
    
    // Personal Information
    if (!empty($resumeData['personal_info'])) {
        $personal = $resumeData['personal_info'];
        $lines[] = "CONTACT INFORMATION";
        $lines[] = str_repeat("-", 70);
        
        if (!empty($personal['full_name'])) {
            $lines[] = "Name: " . $personal['full_name'];
        }
        if (!empty($personal['email'])) {
            $lines[] = "Email: " . $personal['email'];
        }
        if (!empty($personal['phone'])) {
            $lines[] = "Phone: " . $personal['phone'];
        }
        if (!empty($personal['address'])) {
            $lines[] = "Address: " . $personal['address'];
        }
        $lines[] = "";
    }
    
    // Education Section
    if (!empty($resumeData['education']) && is_array($resumeData['education'])) {
        $lines[] = "EDUCATION";
        $lines[] = str_repeat("-", 70);
        
        foreach ($resumeData['education'] as $edu) {
            if (!empty($edu['institution'])) {
                $lines[] = $edu['institution'];
                
                $degreeInfo = [];
                if (!empty($edu['degree'])) {
                    $degreeInfo[] = $edu['degree'];
                }
                if (!empty($edu['field_of_study'])) {
                    $degreeInfo[] = "(" . $edu['field_of_study'] . ")";
                }
                if (!empty($degreeInfo)) {
                    $lines[] = "  " . implode(" ", $degreeInfo);
                }
                
                if (!empty($edu['start_date']) && !empty($edu['end_date'])) {
                    $lines[] = "  " . $edu['start_date'] . " - " . $edu['end_date'];
                }
                
                if (!empty($edu['description'])) {
                    $lines[] = "  " . $edu['description'];
                }
                $lines[] = "";
            }
        }
    }
    
    // Experience Section
    if (!empty($resumeData['experience']) && is_array($resumeData['experience'])) {
        $lines[] = "WORK EXPERIENCE";
        $lines[] = str_repeat("-", 70);
        
        foreach ($resumeData['experience'] as $exp) {
            if (!empty($exp['company'])) {
                $lines[] = $exp['company'];
                
                if (!empty($exp['position'])) {
                    $lines[] = "  Position: " . $exp['position'];
                }
                
                if (!empty($exp['start_date']) && !empty($exp['end_date'])) {
                    $lines[] = "  " . $exp['start_date'] . " - " . $exp['end_date'];
                }
                
                if (!empty($exp['description'])) {
                    $lines[] = "  " . $exp['description'];
                }
                $lines[] = "";
            }
        }
    }
    
    // Skills Section
    if (!empty($resumeData['skills']) && is_array($resumeData['skills'])) {
        $lines[] = "SKILLS";
        $lines[] = str_repeat("-", 70);
        
        $skillsList = [];
        foreach ($resumeData['skills'] as $skill) {
            if (!empty($skill['skill_name'])) {
                $skillEntry = $skill['skill_name'];
                if (!empty($skill['proficiency'])) {
                    $skillEntry .= " (" . $skill['proficiency'] . ")";
                }
                $skillsList[] = $skillEntry;
            }
        }
        
        if (!empty($skillsList)) {
            $lines[] = implode(", ", $skillsList);
        }
        $lines[] = "";
    }
    
    // Projects Section
    if (!empty($resumeData['projects']) && is_array($resumeData['projects'])) {
        $lines[] = "PROJECTS";
        $lines[] = str_repeat("-", 70);
        
        foreach ($resumeData['projects'] as $proj) {
            if (!empty($proj['title'])) {
                $lines[] = $proj['title'];
                
                if (!empty($proj['description'])) {
                    $lines[] = "  " . $proj['description'];
                }
                
                if (!empty($proj['link'])) {
                    $lines[] = "  Link: " . $proj['link'];
                }
                $lines[] = "";
            }
        }
    }
    
    $lines[] = str_repeat("=", 70);
    
    if (count($lines) <= 5) {
        $lines[] = "No resume details available.";
    }
    
    $textContent = implode("\n", $lines);
    
    // Try to use TCPDF if available, otherwise return text
    if (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
        try {
            Logger::info(Logger::CAT_RESUME, 'RESUME_EXPORT', 'Resume exported as PDF', [
                'user_id'      => $userId,
                'resume_id'    => $resumeId,
                'resume_title' => $resumeData['title'] ?? null,
                'template'     => $resumeData['template'] ?? null,
                'format'       => 'pdf',
            ]);
            generatePdfWithTCPDF($resumeData, $textContent);
        } catch (Exception $e) {
            Logger::error(Logger::CAT_ERROR, 'PDF_GENERATION_FAILED', 'PDF generation failed, falling back to text', [
                'user_id'   => $userId,
                'resume_id' => $resumeId,
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);
            // Fall back to text if TCPDF fails
            returnTextOutput($textContent);
        }
    } else {
        Logger::info(Logger::CAT_RESUME, 'RESUME_EXPORT', 'Resume exported as text', [
            'user_id'      => $userId,
            'resume_id'    => $resumeId,
            'resume_title' => $resumeData['title'] ?? null,
            'template'     => $resumeData['template'] ?? null,
            'format'       => 'text',
        ]);
        // No TCPDF, return as text
        returnTextOutput($textContent);
    }
    exit();
    
} catch (Exception $e) {
    Logger::error(Logger::CAT_ERROR, 'API_REQUEST_FAILED', 'Resume download endpoint failed', [
        'user_id'   => $userId ?? null,
        'resume_id' => $resumeId ?? null,
        'message'   => $e->getMessage(),
        'file'      => $e->getFile(),
        'line'      => $e->getLine(),
    ]);
    header('Content-Type: application/json');
    jsonResponse(500, false, 'Error generating resume', null, $e->getMessage());
    exit();
}

/**
 * Generate PDF using TCPDF library
 * @param array $resumeData Complete resume data
 * @param string $textContent Formatted text content
 */
function generatePdfWithTCPDF($resumeData, $textContent) {
    require_once __DIR__ . '/../../../../vendor/autoload.php';
    
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Resume Builder');
    $pdf->SetAuthor('Resume Builder');
    $pdf->SetTitle($resumeData['title'] ?? 'Resume');
    $pdf->SetSubject('Professional Resume');
    
    // Set default font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font for header
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 15, strtoupper($resumeData['title'] ?? 'Resume'), 0, 1, 'C');
    
    // Reset color
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    
    // Add horizontal line
    $pdf->SetDrawColor(30, 58, 138);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(15, 35, 195, 35);
    
    // Add content
    $pdf->SetY(40);
    $pdf->MultiCell(0, 5, $textContent, 0, 'L');
    
    // Add footer with template and date
    $pdf->SetY(-25);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 10, 'Template: ' . ($resumeData['template'] ?? 'modern') . ' | Generated: ' . date('Y-m-d H:i:s') . ' | Page: ' . $pdf->getAliasNumPage(), 0, 0, 'C');
    
    // Output PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="resume_' . ($resumeData['id'] ?? 'download') . '.pdf"');
    $pdf->Output('resume.pdf', 'D');
}

/**
 * Return resume content as plain text file
 * @param string $content Formatted text content
 */
function returnTextOutput($content) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="resume.txt"');
    echo $content;
}
?>
