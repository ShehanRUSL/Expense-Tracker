<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

include '../includes/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=expenses.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['Date', 'Category', 'Description', 'Amount']);

$stmt = $pdo->prepare("
    SELECT e.expense_date, c.name AS category, e.description, e.amount
    FROM expenses e
    JOIN categories c ON e.category_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.expense_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($data as $row) {
    fputcsv($output, [$row['expense_date'], $row['category'], $row['description'], $row['amount']]);
}

fclose($output);
exit;
