<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/response.php';

function escapePdfString(string $text): string {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function buildPdf(string $content): string {
    $lines = preg_split('/\r\n|\n|\r/', trim($content));
    $textStream = "BT\n/F1 12 Tf\n40 760 Td\n";
    foreach ($lines as $index => $line) {
        if ($index > 0) {
            $textStream .= "T* ";
        }
        $textStream .= '(' . escapePdfString($line) . ') Tj\n';
    }
    $textStream .= "ET";

    $objects = [];
    $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
    $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    $objects[] = "5 0 obj\n<< /Length " . strlen($textStream) . " >>\nstream\n" . $textStream . "\nendstream\nendobj\n";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    $position = strlen($pdf);
    foreach ($objects as $object) {
        $offsets[] = $position;
        $pdf .= $object;
        $position += strlen($object);
    }

    $xref = "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    foreach ($offsets as $index => $offset) {
        if ($index === 0) {
            continue;
        }
        $xref .= sprintf('%010d 00000 n \n', $offset);
    }

    $trailer = "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $position . "\n%%EOF";
    $pdf .= $xref . $trailer;

    return $pdf;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    jsonResponse(404, false, 'Route not found.', null, 'The requested endpoint does not exist.');
}

$userId = $_GET['user_id'] ?? null;
$resumeId = $_GET['resume_id'] ?? null;

if (!$userId || !$resumeId) {
    header('Content-Type: application/json');
    jsonResponse(400, false, 'user_id and resume_id are required.', null, 'Missing required query parameters.');
}

$stmt = $conn->prepare('SELECT r.id, r.title, r.template, u.name AS user_name, u.email AS user_email FROM resumes r JOIN users u ON u.id = r.user_id WHERE r.id = ? AND r.user_id = ?');
$stmt->execute([$resumeId, $userId]);
$resume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resume) {
    header('Content-Type: application/json');
    jsonResponse(404, false, 'Resume not found.', null, 'No resume was found for the provided user_id and resume_id.');
}

$education = [];
$work = [];
$skills = [];

$stmt = $conn->prepare('SELECT institution, degree, field_of_study, start_date, end_date FROM education WHERE resume_id = ? ORDER BY start_date DESC');
$stmt->execute([$resumeId]);
$education = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare('SELECT company, position, start_date, end_date, description FROM work_experience WHERE resume_id = ? ORDER BY start_date DESC');
$stmt->execute([$resumeId]);
$work = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare('SELECT skill_name, proficiency FROM skills WHERE resume_id = ? ORDER BY skill_name');
$stmt->execute([$resumeId]);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

$lines = [];
$lines[] = $resume['title'] ?: 'Resume';
$lines[] = 'Generated for: ' . ($resume['user_name'] ?: $resume['user_email']);
$lines[] = 'Template: ' . ($resume['template'] ?: 'default');
$lines[] = str_repeat('-', 60);
if (count($education) > 0) {
    $lines[] = 'Education:';
    foreach ($education as $item) {
        $lines[] = sprintf('%s | %s (%s - %s)', $item['institution'], $item['degree'], $item['start_date'], $item['end_date']);
        if (!empty($item['field_of_study'])) {
            $lines[] = 'Field: ' . $item['field_of_study'];
        }
        $lines[] = '';
    }
}

if (count($work) > 0) {
    $lines[] = 'Work Experience:';
    foreach ($work as $item) {
        $lines[] = sprintf('%s | %s (%s - %s)', $item['company'], $item['position'], $item['start_date'], $item['end_date']);
        if (!empty($item['description'])) {
            $lines[] = 'Description: ' . $item['description'];
        }
        $lines[] = '';
    }
}

if (count($skills) > 0) {
    $lines[] = 'Skills:';
    foreach ($skills as $item) {
        $lines[] = sprintf('%s - %s', $item['skill_name'], $item['proficiency']);
    }
}

if (count($lines) === 4) {
    $lines[] = 'No resume details were found for this entry.';
}

$documentText = implode("\n", $lines);
$pdf = buildPdf($documentText);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="resume_' . $resumeId . '.pdf"');
echo $pdf;
exit;
