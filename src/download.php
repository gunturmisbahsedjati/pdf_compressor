<?php
if (isset($_GET['file']) && isset($_GET['name'])) {
    $filename = basename($_GET['file']);
    $originalName = basename($_GET['name']);
    $filepath = __DIR__ . '/uploads/' . $filename;

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);

        // Opsional: Hapus file setelah diunduh agar server tidak penuh
        // unlink($filepath); 
        exit;
    } else {
        echo "File tidak ditemukan.";
    }
}
