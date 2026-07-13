<?php
// api/preview_file.php
// Serves files with proper headers for inline preview (no Chrome blocking)

session_start();
require_once '../config/database.php';
require_once '../config/helper.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Unauthorized');
}

$file = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($file)) {
    http_response_code(400);
    die('No file specified');
}

// Sanitize: prevent directory traversal
$file = basename($file);
$filepath = realpath(__DIR__ . '/../uploads/' . $file);
$uploads_dir = realpath(__DIR__ . '/../uploads');

// Security: ensure file is within uploads directory
if ($filepath === false || strpos($filepath, $uploads_dir) !== 0) {
    http_response_code(404);
    die('File not found');
}

if (!file_exists($filepath)) {
    http_response_code(404);
    die('File not found');
}

// Determine MIME type
$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
$mime_types = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'bmp'  => 'image/bmp',
    'svg'  => 'image/svg+xml',
    'txt'  => 'text/plain',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
];

$content_type = isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';

// Clear any previous output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Set headers for inline display (NOT download)
header('Content-Type: ' . $content_type);
header('Content-Disposition: inline; filename="' . $file . '"');
header('Content-Length: ' . filesize($filepath));

// Remove headers that block iframe embedding
header_remove('X-Frame-Options');
header('X-Content-Type-Options: nosniff');

// Allow embedding in same origin
header('Content-Security-Policy: frame-ancestors \'self\'');

// Cache for 1 hour
header('Cache-Control: public, max-age=3600');
header('Accept-Ranges: bytes');

readfile($filepath);
exit;
?>
