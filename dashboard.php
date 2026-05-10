<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM advocate_registration WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Decrypt mobile and email
$user['mobile'] = decryptData($user['mobile']);
$user['email'] = decryptData($user['email']);

// Get additional details
$stmt = $pdo->prepare("SELECT * FROM advocate_details WHERE advocate_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$details = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_details'])) {
    $dob = $_POST['dob'];
    $doe = $_POST['doe'];

    // Validate dates
    if (!empty($dob) && !strtotime($dob)) {
        $error = "Invalid date of birth";
    } elseif (!empty($doe) && !strtotime($doe)) {
        $error = "Invalid date of enrollment";
    } else {
        // Handle file upload
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $file_size = $_FILES['photo']['size'];

            if (!in_array($ext, $allowed)) {
                $error = "Only JPG files are allowed";
            } elseif ($file_size < 20 * 1024 || $file_size > 500 * 1024) {
                $error = "File size must be between 20KB and 500KB";
            } else {
                $upload_dir = 'uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $photo_path = $upload_dir . $_SESSION['user_id'] . '_' . time() . '.jpg';
                move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
            }
        }

        if (empty($error)) {
            if ($details) {
                $sql = "UPDATE advocate_details SET date_of_birth = ?, date_of_enrollment = ?";
                $params = [$dob ?: null, $doe ?: null];
                if ($photo_path) {
                    $sql .= ", photograph_path = ?";
                    $params[] = $photo_path;
                }
                $sql .= " WHERE advocate_id = ?";
                $params[] = $_SESSION['user_id'];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                $stmt = $pdo->prepare("INSERT INTO advocate_details (advocate_id, date_of_birth, date_of_enrollment, photograph_path) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $dob ?: null, $doe ?: null, $photo_path]);
            }
            $message = "Details saved successfully!";
            header('Refresh:0');
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Advocate Dashboard</title>
    <style>
        body {
            font-family: Arial;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .info-box {
            background: #f0f0f0;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .info-box p {
            margin: 5px 0;
        }

        .form-group {
            margin: 15px 0;
        }

        label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }

        input,
        button {
            padding: 8px;
            margin: 5px;
        }

        button {
            background: blue;
            color: white;
            border: none;
            cursor: pointer;
        }

        .logout {
            background: red;
        }

        .message {
            color: green;
            margin: 10px 0;
        }

        .error {
            color: red;
            margin: 10px 0;
        }

        .readonly-field {
            background: #e9ecef;
            padding: 8px;
            margin: 5px 0;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>

    <div class="info-box">
        <h3>Your Registration Information (Read Only)</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Enrollment Number:</strong> <?php echo htmlspecialchars($user['enrollment_number']); ?></p>
        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($user['mobile']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>State:</strong> <?php echo htmlspecialchars($user['state']); ?></p>
        <p><strong>District:</strong> <?php echo htmlspecialchars($user['district']); ?></p>
        <p><strong>PIN Code:</strong> <?php echo htmlspecialchars($user['pin_code']); ?></p>
    </div>

    <h3>Additional Information</h3>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Date of Birth:</label>
            <input type="date" name="dob" value="<?php echo $details ? $details['date_of_birth'] : ''; ?>">
        </div>

        <div class="form-group">
            <label>Date of Enrollment:</label>
            <input type="date" name="doe" value="<?php echo $details ? $details['date_of_enrollment'] : ''; ?>">
        </div>

        <div class="form-group">
            <label>Upload Photograph (JPG only, 20KB-500KB):</label>
            <input type="file" name="photo" accept=".jpg,.jpeg">
            <?php if ($details && $details['photograph_path']): ?>
                <div>Current photo: <img src="<?php echo $details['photograph_path']; ?>" height="50"></div>
            <?php endif; ?>
        </div>

        <button type="submit" name="save_details">Save Details</button>
        <a href="logout.php"><button type="button" class="logout">Logout</button></a>
    </form>
</body>

</html>