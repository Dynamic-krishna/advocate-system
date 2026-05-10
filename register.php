<?php
require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validation
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $enrollment = trim($_POST['enrollment_number']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $state = trim($_POST['state']);
    $district = trim($_POST['district']);
    $pin_code = trim($_POST['pin_code']);

    // Name validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    // Password validation
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Enrollment number validation (format: x1234xx1234)
    if (!preg_match('/^[a-zA-Z][0-9]{4}[a-zA-Z]{2}[0-9]{4}$/', $enrollment)) {
        $errors[] = "Enrollment number must be in format: Letter + 4 digits + 2 letters + 4 digits (e.g., A1234AB1234)";
    }

    // Mobile validation
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Mobile number must be 10 digits";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // PIN code validation
    if (!preg_match('/^[0-9]{6}$/', $pin_code)) {
        $errors[] = "PIN code must be 6 digits";
    }

    if (empty($errors)) {
        try {
            // Check if enrollment number exists
            $stmt = $pdo->prepare("SELECT id FROM advocate_registration WHERE enrollment_number = ?");
            $stmt->execute([$enrollment]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Enrollment number already exists";
            } else {
                // Encrypt mobile and email
                $encrypted_mobile = encryptData($mobile);
                $encrypted_email = encryptData($email);

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert data
                $stmt = $pdo->prepare("INSERT INTO advocate_registration (name, password, enrollment_number, mobile, email, state, district, pin_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $hashed_password, $enrollment, $encrypted_mobile, $encrypted_email, $state, $district, $pin_code]);

                $success = "Registration successful! Please <a href='login.php'>login</a>";
            }
        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Advocate Registration</title>
    <style>
        body {
            font-family: Arial;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }

        .error {
            color: red;
            margin: 10px 0;
        }

        .success {
            color: green;
            margin: 10px 0;
        }

        input,
        select {
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
    <h2>Advocate Registration</h2>
    <?php if ($errors): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Password (min 6 characters):</label>
        <input type="password" name="password" required>

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>

        <label>Enrollment Number (Format: A1234AB1234):</label>
        <input type="text" name="enrollment_number" placeholder="A1234AB1234" required>

        <label>Mobile (10 digits):</label>
        <input type="tel" name="mobile" pattern="[0-9]{10}" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>State:</label>
        <input type="text" name="state" required>

        <label>District:</label>
        <input type="text" name="district" required>

        <label>PIN Code (6 digits):</label>
        <input type="text" name="pin_code" pattern="[0-9]{6}" required>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>

</html>