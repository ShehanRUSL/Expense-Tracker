<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$success = '';
$errors = [];

$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (!$username || !$email) {
        $errors[] = "Name and email cannot be empty.";
    } else {
        $updateStmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $updateStmt->execute([$username, $email, $user_id]);
        $_SESSION['username'] = $username;
        $success = "Profile updated successfully.";
        $user['username'] = $username;
        $user['email'] = $email;
    }
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$current_password || !$new_password || !$confirm_password) {
        $errors[] = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $userPass = $stmt->fetchColumn();

        if (!password_verify($current_password, $userPass)) {
            $errors[] = "Current password is incorrect.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $updatePass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updatePass->execute([$hashed, $user_id]);
            $success = "Password changed successfully.";
        }
    }
}
?>

<div class="container mt-4" style="max-width: 600px;">
    <h2>User Profile</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e):
                echo "<p>$e</p>";
            endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="card p-3 mb-4 shadow-sm">
        <h5>Edit Profile</h5>
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="form-control"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control"
                required>
        </div>

        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
    </form>

    <form method="post" class="card p-3 shadow-sm">
        <h5>Change Password</h5>
        <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
        </div>

        <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>