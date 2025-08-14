<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid expense ID.</div>";
    include '../includes/footer.php';
    exit;
}

$expense_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM expenses WHERE id = ? AND user_id = ?");
$stmt->execute([$expense_id, $_SESSION['user_id']]);
$expense = $stmt->fetch();

if (!$expense) {
    echo "<div class='alert alert-danger'>Expense not found.</div>";
    include '../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $date = $_POST['expense_date'];

    if ($amount > 0 && $category_id && $date) {
        $updateStmt = $pdo->prepare("UPDATE expenses SET category_id = ?, amount = ?, description = ?, expense_date = ? WHERE id = ? AND user_id = ?");
        $updateStmt->execute([$category_id, $amount, $description, $date, $expense_id, $_SESSION['user_id']]);

        header("Location: dashboard.php");
        exit;
    } else {
        echo "<div class='alert alert-warning'>Please fill in all required fields correctly.</div>";
    }
}

$catStmt = $pdo->query("SELECT * FROM categories");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-4">
    <h3>Edit Expense</h3>
    <form method="post" class="card p-4 shadow-sm">
        <div class="row mb-3">
            <div class="col-md-2">
                <input type="number" step="0.01" name="amount" class="form-control"
                    value="<?= htmlspecialchars($expense['amount']) ?>" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="description" class="form-control"
                    value="<?= htmlspecialchars($expense['description']) ?>">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $expense['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="expense_date" class="form-control" value="<?= $expense['expense_date'] ?>"
                    required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Update</button>
            </div>
        </div>
    </form>

    <a href="dashboard.php" class="btn btn-secondary mt-2">← Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>