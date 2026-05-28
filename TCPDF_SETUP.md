# TCPDF Installation Guide

## Install TCPDF via Composer

This project uses TCPDF for professional PDF generation.

### Installation Steps

1. **Install Composer** (if not already installed):

   ```bash
   curl -sS https://getcomposer.org/installer | php
   mv composer.phar /usr/local/bin/composer
   ```

2. **Navigate to backend directory**:

   ```bash
   cd resume_builder_backend
   ```

3. **Install TCPDF**:

   ```bash
   composer require tecnickcom/tcpdf
   ```

4. **Verify installation**:
   ```bash
   ls vendor/tecnickcom/tcpdf/
   ```

### Using TCPDF

The PDF generation endpoint at `src/api/user/download_resume.php` will automatically use TCPDF if available.

Example usage in PHP:

```php
require_once 'vendor/autoload.php';

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetTitle('Resume');
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Your content here', 0, 1);
$pdf->Output('resume.pdf', 'D'); // Download
```

### Files Modified

- `src/api/user/download_resume.php` - Updated to use TCPDF when available

### Notes

- TCPDF is heavy (~50MB) but produces professional, styled PDFs
- Supports images, HTML, and advanced formatting
- Falls back to text format if TCPDF is not installed
