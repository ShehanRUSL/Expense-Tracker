<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php'; // Adjust path if needed

// Calculate total expenses for current month for logged-in user
$totalExpense = 0;
$currentMonth = date('F Y');

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM expenses 
        WHERE user_id = ? AND DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $totalExpense = $stmt->fetchColumn();
} ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Expense Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .navbar-gradient {
            background: linear-gradient(-45deg, #007bff, #6610f2, #6f42c1, #20c997);
            background-size: 400% 400%;
            animation: gradientMove 10s ease infinite;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 2rem;
            /* bigger */
            letter-spacing: 2px;
            color: white !important;
        }

        .profile-btn {
            font-weight: 600;
            padding: 0.6rem 1rem;
            /* bigger buttons */
            font-size: 1rem;
        }

        .total-expense {
            font-weight: 600;
            font-size: 1rem;
            color: #ffc107;
            margin-right: 1.5rem;
            white-space: nowrap;
            display: flex;
            align-items: center;
        }



        body {
            background-color: #ffffffff;
            /* Calm Darya-like light blue */
            font-family: 'Segoe UI', sans-serif;
        }

        .card,
        .table {
            background-color: #ffffff !important;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        h2,
        h4 {
            color: #34495e;
        }

        .progress {
            background-color: #cce5ff;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-gradient navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/expense-tracker/user/dashboard.php">ExpenseTracker</a>

            <div class="d-flex ms-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>


                    <a href="profile.php" class="btn btn-outline-light profile-btn me-2">
                        <i class="bi bi-person-circle me-1"></i> My Profile
                    </a>

                    <a href="/expense-tracker/logout.php" class="btn btn-outline-light profile-btn">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light profile-btn">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-4">