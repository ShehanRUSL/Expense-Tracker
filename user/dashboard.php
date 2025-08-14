<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_budget'])) {
    $budgetAmount = $_POST['budget_amount'];
    $month = date('Y-m');

    $check = $pdo->prepare("SELECT id FROM monthly_budget WHERE user_id = ? AND month_year = ?");
    $check->execute([$_SESSION['user_id'], $month]);

    if ($check->rowCount() > 0) {
        $update = $pdo->prepare("UPDATE monthly_budget SET amount = ? WHERE user_id = ? AND month_year = ?");
        $update->execute([$budgetAmount, $_SESSION['user_id'], $month]);
    } else {
        $insert = $pdo->prepare("INSERT INTO monthly_budget (user_id, month_year, amount) VALUES (?, ?, ?)");
        $insert->execute([$_SESSION['user_id'], $month, $budgetAmount]);
    }
}

$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');

$totalSpentStmt = $pdo->prepare("SELECT SUM(amount) FROM expenses WHERE user_id = ? AND expense_date BETWEEN ? AND ?");
$totalSpentStmt->execute([$_SESSION['user_id'], $monthStart, $monthEnd]);
$totalSpent = $totalSpentStmt->fetchColumn();
$totalSpent = $totalSpent ? $totalSpent : 0;

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $date = $_POST['expense_date'];

    if ($amount > 0 && $category_id && $date) {
        $stmt = $pdo->prepare("INSERT INTO expenses (user_id, category_id, amount, description, expense_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $category_id, $amount, $description, $date]);
    }
}

$catStmt = $pdo->query("SELECT * FROM categories");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$expStmt = $pdo->prepare("SELECT e.*, c.name AS category_name FROM expenses e 
                          JOIN categories c ON e.category_id = c.id 
                          WHERE e.user_id = ? ORDER BY expense_date DESC");
$expStmt->execute([$_SESSION['user_id']]);
$expenses = $expStmt->fetchAll(PDO::FETCH_ASSOC);

$chartStmt = $pdo->prepare("
    SELECT c.name AS category, SUM(e.amount) AS total 
    FROM expenses e
    JOIN categories c ON e.category_id = c.id
    WHERE e.user_id = ?
    GROUP BY c.name
");
$chartStmt->execute([$_SESSION['user_id']]);
$chartData = $chartStmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$totals = [];
foreach ($chartData as $row) {
    $labels[] = $row['category'];
    $totals[] = $row['total'];
}


$currentMonth = date('Y-m');

$totalMonthStmt = $pdo->prepare("
    SELECT SUM(amount) FROM expenses 
    WHERE user_id = ? AND DATE_FORMAT(expense_date, '%Y-%m') = ?
");
$totalMonthStmt->execute([$_SESSION['user_id'], $currentMonth]);
$totalThisMonth = $totalMonthStmt->fetchColumn();

if (!$totalThisMonth) {
    $totalThisMonth = 0;
}


$currentMonth = date('Y-m');
$budgetStmt = $pdo->prepare("SELECT amount FROM monthly_budget WHERE user_id = ? AND month_year = ?");
$budgetStmt->execute([$_SESSION['user_id'], $currentMonth]);
$budget = $budgetStmt->fetchColumn();
?>


<div class="container my-4"></div>
<h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
<hr>

<!-- Budget Progress -->
<?php if ($budget):
    $percent = min(100, ($totalSpent / $budget) * 100);
    ?>
    <div class="mb-4">
        <p><strong>Monthly Spending:</strong> Rs. <?= number_format($totalSpent, 2) ?> / Rs.
            <?= number_format($budget, 2) ?>
        </p>
        <div class="progress" style="height: 25px;">
            <div class="progress-bar <?= $percent >= 100 ? 'bg-danger' : 'bg-success' ?>" role="progressbar"
                style="width: <?= $percent ?>%">
                <?= round($percent) ?>%
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Budget Form -->
<h4>Set Monthly Budget</h4>
<form method="post" class="row mb-4">
    <div class="col-md-3">
        <input type="number" step="0.01" name="budget_amount" class="form-control" placeholder="Rs. 0.00"
            value="<?= $budget ? $budget : '' ?>" required>
    </div>
    <div class="col-md-2">
        <button class="btn btn-success w-100" name="save_budget">Save Budget</button>
    </div>
</form>

<!-- Pie Chart -->
<h4>Expenses by Category</h4>
<div style="max-width: 500px; margin: 0 auto;">
    <canvas id="expenseChart" width="300" height="300" class="mb-4"></canvas>
</div>



<!-- Add Expense Form -->
<h4>Add New Expense</h4>
<form method="post" class="card p-3 mb-4 shadow-sm">
    <input type="hidden" name="add_expense" value="1">
    <div class="row">
        <div class="col-md-2">
            <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="description" class="form-control" placeholder="Description">
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select" required>
                <option value="">-- Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="expense_date" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Add Expense</button>
        </div>
    </div>
</form>




<!-- Expense Table -->
<h4>Your Expenses</h4>
<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Date</th>
            <th>Category</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($expenses as $exp): ?>
            <tr>
                <td><?= $exp['expense_date'] ?></td>
                <td><?= htmlspecialchars($exp['category_name']) ?></td>
                <td><?= htmlspecialchars($exp['description']) ?></td>
                <td>Rs. <?= number_format($exp['amount'], 2) ?></td>
                <td>
                    <a href="edit.php?id=<?= $exp['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="dashboard.php?delete=<?= $exp['id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>



<div class="alert alert-info">
    <strong>Your Total expenses for <?= date('F Y') ?>:</strong> Rs. <?= number_format($totalThisMonth, 2) ?>
</div>



<!-- Export Button -->
<a href="export.php" class="btn btn-success mb-3">Export to CSV</a>



<?php include '../includes/footer.php'; ?>