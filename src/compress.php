<?php
header('Content-Type: application/json');

// Koneksi Database
$host = 'db';
$db   = 'pdf_app';
$user = 'user';
$pass = 'userpassword';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file'])) {
    $file = $_FILES['pdf_file'];

    if ($file['type'] != 'application/pdf') {
        echo json_encode(['success' => false, 'message' => 'File harus berupa PDF.']);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $originalName = basename($file['name']);
    $uniqueId = time() . '_' . rand(1000, 9999);
    $inputFile = $uploadDir . 'in_' . $uniqueId . '.pdf';
    $outputFile = $uploadDir . 'out_' . $uniqueId . '.pdf';

    if (move_uploaded_file($file['tmp_name'], $inputFile)) {
        $originalSize = filesize($inputFile);

        $qualityMode = $_POST['quality'] ?? 'recommended';

        switch ($qualityMode) {
            case 'extreme':
                $pdfSettings = '/screen'; // Kualitas rendah, file sangat kecil
                break;
            case 'less':
                $pdfSettings = '/printer'; // Kualitas tinggi, file lebih besar
                break;
            case 'recommended':
            default:
                $pdfSettings = '/ebook'; // Kualitas menengah, seimbang
                break;
        }

        // Eksekusi Ghostscript
        $gsCommand = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS={$pdfSettings} -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($outputFile) . " " . escapeshellarg($inputFile);
        exec($gsCommand, $output, $returnVar);

        if ($returnVar === 0 && file_exists($outputFile)) {
            $compressedSize = filesize($outputFile);

            // Simpan ke Database
            $stmt = $pdo->prepare("INSERT INTO compress_history (filename, original_size, compressed_size) VALUES (?, ?, ?)");
            $stmt->execute([$originalName, $originalSize, $compressedSize]);

            $savings = round((($originalSize - $compressedSize) / $originalSize) * 100);
            unlink($inputFile); // Hapus input asli

            $downloadLink = "download.php?file=" . urlencode('out_' . $uniqueId . '.pdf') . "&name=" . urlencode("compressed_$originalName");

            // Berikan respons sukses
            echo json_encode([
                'success' => true,
                'original_size' => number_format($originalSize / 1024, 2),
                'compressed_size' => number_format($compressedSize / 1024, 2),
                'savings' => $savings,
                'download_link' => $downloadLink
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengompresi PDF.']);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid.']);
exit;
