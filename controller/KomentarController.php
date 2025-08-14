<?php
// controller/KomentarController.php

function getTestimonials($pdo)
{
    try {
        $sql = "SELECT * FROM tb_komentar WHERE isShow = 'true' ORDER BY id_komentar DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function submitKomentar($pdo)
{
    try {
        $nama = trim($_POST['nama'] ?? '');
        $instansi = trim($_POST['instansi'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        $komentar = trim($_POST['komentar'] ?? '');

        // Validasi input
        if (empty($nama) || empty($instansi) || empty($komentar)) {
            throw new Exception('Semua field harus diisi');
        }

        if ($rating < 1 || $rating > 5) {
            throw new Exception('Rating harus antara 1-5 bintang');
        }

        if (strlen($komentar) < 10) {
            throw new Exception('Komentar minimal 10 karakter');
        }

        // Insert komentar dengan isShow = 'false' (menunggu moderasi)
        $sql = "INSERT INTO tb_komentar (nama, instansi, rating, komentar, isShow) VALUES (?, ?, ?, ?, 'false')";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$nama, $instansi, $rating, $komentar]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Komentar berhasil dikirim dan akan ditampilkan setelah moderasi'
            ];
        } else {
            throw new Exception('Gagal menyimpan komentar');
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Handle AJAX request untuk submit komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_komentar') {
    header('Content-Type: application/json');

    if (!isset($pdo)) {
        include '../db/koneksi.php';
    }

    $result = submitKomentar($pdo);
    echo json_encode($result);
    exit;
}
