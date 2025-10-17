<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$kode_proyek = $_GET['kode_proyek'] ?? '';

if (empty($kode_proyek)) {
    echo json_encode(['success' => false, 'message' => 'Kode proyek required']);
    exit();
}

// Fetch approved proposals for the selected project
$stmt = $conn->prepare("SELECT id_proposal, judul_proposal, pj, date 
    FROM proposal 
    WHERE kode_proyek = ? AND status = 'approved' 
    ORDER BY created_at DESC");
$stmt->bind_param("s", $kode_proyek);
$stmt->execute();
$result = $stmt->get_result();

$proposals = [];
while ($row = $result->fetch_assoc()) {
    $proposals[] = $row;
}

echo json_encode([
    'success' => true,
    'proposals' => $proposals
]);
?>

