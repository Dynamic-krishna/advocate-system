<?php
// Start session FIRST - before ANY output
session_start();

require_once 'config.php';

$error = '';

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        try {
             $stmt = $pdo->prepare("SELECT * FROM advocate_registration WHERE enrollment_number = ? OR name = ?");
             $stmt->execute([$username, $username]);
             $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['enrollment'] = $user['enrollment_number'];

                // Redirect - NO output before this line!
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Either Login ID or Password is wrong";
            }
        } catch (PDOException $e) {
            $error = "Login error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Advocate Login</title>
    <style>
        body {
            font-family: Arial;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        .error {
            color: red;
            margin: 10px 0;
            padding: 10px;
            background: #ffebee;
            border-radius: 4px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            width: 100%;
        }

        button:hover {
            background: #45a049;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
        }

        a {
            color: #2196F3;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Advocate Login</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Username (Enrollment Number or Name):</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>

</html>