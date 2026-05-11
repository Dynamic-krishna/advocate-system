<?php
require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Trim inputs
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $enrollment = trim($_POST['enrollment_number'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $pin_code = trim($_POST['pin_code'] ?? '');

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

    // Mobile validation (Indian: 10 digits starting with 6-9)
    if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        $errors[] = "Mobile number must be a valid Indian number: 10 digits starting with 6-9";
    }

    // Email validation (Gmail only)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $errors[] = "Only Gmail addresses are allowed (e.g., example@gmail.com)";
    }

    // PIN code validation (optional, but if provided must be 6 digits)
    if (!empty($pin_code) && !preg_match('/^[0-9]{6}$/', $pin_code)) {
        $errors[] = "PIN code must be 6 digits if provided";
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

                // Insert data (state, district, pin can be null)
                $stmt = $pdo->prepare("INSERT INTO advocate_registration (name, password, enrollment_number, mobile, email, state, district, pin_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $name,
                    $hashed_password,
                    $enrollment,
                    $encrypted_mobile,
                    $encrypted_email,
                    $state ?: null,
                    $district ?: null,
                    $pin_code ?: null
                ]);

                $success = "Registration successful! Please <a href='login.php'>login</a>";
            }
        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advocate Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            background: white;
            max-width: 550px;
            width: 100%;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 28px;
            position: relative;
        }

        h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: #667eea;
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .error-box, .success-box {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: shake 0.3s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-box {
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }

        .success-box {
            background: #efe;
            color: #080;
            border: 1px solid #cfc;
        }

        .error-box p {
            margin: 5px 0;
        }

        .form-group {
            margin-bottom: 18px;
            animation: fadeIn 0.5s ease forwards;
            opacity: 0;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }
        .form-group:nth-child(7) { animation-delay: 0.7s; }
        .form-group:nth-child(8) { animation-delay: 0.8s; }
        .form-group:nth-child(9) { animation-delay: 0.9s; }

        label {
            display: block;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            font-size: 14px;
        }

        label .optional {
            font-weight: 400;
            color: #999;
            font-size: 12px;
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            background: white;
        }

        input.error {
            border-color: #f44336;
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.2);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .footer-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .footer-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .footer-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Advocate Registration</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <?php foreach ($errors as $error): ?>
                    <p>⚠ <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-box">✅ <?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password (min 6 characters)</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
            </div>

            <div class="form-group">
                <label for="enrollment_number">Enrollment Number</label>
                <input type="text" id="enrollment_number" name="enrollment_number" placeholder="e.g., A1234AB1234" required pattern="[A-Za-z][0-9]{4}[A-Za-z]{2}[0-9]{4}" title="Format: Letter + 4 digits + 2 letters + 4 digits" value="<?php echo htmlspecialchars($_POST['enrollment_number'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="mobile">Mobile Number (Indian)</label>
                <input type="tel" id="mobile" name="mobile" placeholder="10 digits starting with 6-9" required pattern="[6-9][0-9]{9}" maxlength="10" title="Enter a valid Indian mobile number" value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email (Gmail only)</label>
                <input type="email" id="email" name="email" placeholder="example@gmail.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="state">State <span class="optional">(optional)</span></label>
                <input type="text" id="state" name="state" placeholder="Enter state" value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="district">District <span class="optional">(optional)</span></label>
                <input type="text" id="district" name="district" placeholder="Enter district" value="<?php echo htmlspecialchars($_POST['district'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="pin_code">PIN Code <span class="optional">(optional)</span></label>
                <input type="text" id="pin_code" name="pin_code" placeholder="6 digits" pattern="[0-9]{6}" maxlength="6" title="Enter a 6-digit PIN code" value="<?php echo htmlspecialchars($_POST['pin_code'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn">Register</button>
        </form>

        <div class="footer-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        // Optional: real-time validation feedback
        document.getElementById('mobile').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });

        document.getElementById('pin_code').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });

        document.getElementById('enrollment_number').addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 12);
        });

        // Password match check on submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const pwd = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (pwd !== confirm) {
                alert('Passwords do not match!');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>