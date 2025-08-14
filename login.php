<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Both email and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: user/dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Login - Expense Tracker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(to right, #20c997, #007bff);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <div class="login-card p-4 shadow">
        <h2 class="text-center mb-4">Login</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control form-control-sm" required>
            </div>

            <div class="mb-3 position-relative">
                <label>Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="loginPassword" class="form-control form-control-sm"
                        required>
                    <span class="input-group-text">
                        <i class="bi bi-eye-slash" id="toggleLoginPassword" style="cursor:pointer;"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Login</button>
            <a href="signup.php" class="btn btn-link d-block text-center mt-2">Don't have an account?</a>
        </form>
    </div>

    <script>
        const toggleLogin = document.getElementById("toggleLoginPassword");
        const loginInput = document.getElementById("loginPassword");

        toggleLogin.addEventListener("click", function () {
            const type = loginInput.getAttribute("type") === "password" ? "text" : "password";
            loginInput.setAttribute("type", type);
            this.classList.toggle("bi-eye");
            this.classList.toggle("bi-eye-slash");
        });
    </script>

</body>

</html>