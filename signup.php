<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'includes/db.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$username, $email, $hashed])) {
                $success = "Signup successful! <a href='login.php' class='text-decoration-underline'>Click here to login</a>";
            } else {
                $errors[] = "Signup failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Sign Up - Expense Tracker</title>
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

        .signup-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <div class="signup-card p-4 shadow">
        <h2 class="text-center mb-4">Sign Up</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control form-control-sm" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control form-control-sm" required>
            </div>

            <div class="mb-3 position-relative">
                <label>Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="signupPassword" class="form-control form-control-sm"
                        required>
                    <span class="input-group-text">
                        <i class="bi bi-eye-slash" id="toggleSignupPassword" style="cursor:pointer;"></i>
                    </span>
                </div>
            </div>

            <div class="mb-3 position-relative">
                <label>Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm" id="confirmPassword" class="form-control form-control-sm"
                        required>
                    <span class="input-group-text">
                        <i class="bi bi-eye-slash" id="toggleConfirmPassword" style="cursor:pointer;"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
            <a href="login.php" class="btn btn-link d-block text-center mt-2">Already have an account?</a>
        </form>
    </div>

    <script>
        function setupToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            toggle.addEventListener("click", function () {
                const type = input.getAttribute("type") === "password" ? "text" : "password";
                input.setAttribute("type", type);
                this.classList.toggle("bi-eye");
                this.classList.toggle("bi-eye-slash");
            });
        }

        setupToggle("toggleSignupPassword", "signupPassword");
        setupToggle("toggleConfirmPassword", "confirmPassword");
    </script>

</body>

</html>