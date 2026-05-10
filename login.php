<?php

session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM advocate_registration WHERE enrollment_number = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['enrollment'] = $user['enrollment_number'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Either Login ID or Password is wrong";
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
        }

        .error {
            color: red;
            margin: 10px 0;
        }

        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
        }

        button {
            background: blue;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h2>Advocate Login</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Username (Enrollment Number):</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>

</html>