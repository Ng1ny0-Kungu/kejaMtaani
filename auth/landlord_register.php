<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_functions.php';

redirectIfLoggedInUserOnly();

$db = (new Database())->getConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = sanitizeInput($_POST['full_name'] ?? '');
    $email       = sanitizeInput($_POST['email'] ?? '');
    $phone       = validatePhone($_POST['phone'] ?? '');
    $national_id = sanitizeInput($_POST['national_id'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    
    if (strlen($full_name) < 3) $errors['full_name'] = 'Full name is required';
    if (!validateEmail($email)) $errors['email'] = 'Invalid email address';
    elseif (emailExists($db, $email)) $errors['email'] = 'Email already registered';

    if (!$phone) $errors['phone'] = 'Invalid phone format (use 07... or 254...)';
    elseif (phoneExists($db, $phone)) $errors['phone'] = 'Phone already registered';

    if (strlen($national_id) < 6) $errors['national_id'] = 'Invalid ID number';
    if (!validatePassword($password)) $errors['password'] = 'Password must be at least 8 characters';
    if ($password !== $confirm) $errors['confirm'] = 'Passwords do not match';
    
    
    if (empty($_FILES['documents']['name'][0])) $errors['documents'] = 'At least one document is required';

    
    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $password_hash = hashPassword($password);
            $names = explode(' ', $full_name, 2);
            $first = $names[0];
            $last  = $names[1] ?? '';

            $verification_token = bin2hex(random_bytes(32));
            $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            
            $stmt = $db->prepare("
                INSERT INTO users (email, phone_number, password_hash, first_name, last_name, user_type, is_verified, verification_token, token_expiry, last_login, is_active) 
                VALUES (:email, :phone, :password, :first, :last, 'landlord', 0, :token, :expiry, NOW(), 1)
            ");

            $stmt->execute([
                ':email'    => $email,
                ':phone'    => $phone,
                ':password' => $password_hash,
                ':first'    => $first,
                ':last'     => $last,
                ':token'    => $verification_token,
                ':expiry'   => $token_expiry
            ]);

            $user_id = $db->lastInsertId();

            
            $stmtProfile = $db->prepare("INSERT INTO landlord_profiles (user_id, full_name, national_id) VALUES (:uid, :fname, :nid)");
            $stmtProfile->execute([':uid' => $user_id, ':fname' => $full_name, ':nid' => $national_id]);

            // 3. Handle Document Uploads
            $upload_dir = __DIR__ . '/../uploads/verification_docs/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            foreach ($_FILES['documents']['tmp_name'] as $i => $tmp) {
                if (!is_uploaded_file($tmp)) continue;
                
                $filename = uniqid('doc_') . '_' . basename($_FILES['documents']['name'][$i]);
                if (move_uploaded_file($tmp, $upload_dir . $filename)) {
                    $stmtDoc = $db->prepare("INSERT INTO verification_documents (landlord_id, document_type, document_path) VALUES (:lid, 'others', :path)");
                    $stmtDoc->execute([':lid' => $user_id, ':path' => $filename]);
                }
            }

            $db->commit();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = 'landlord';

            header('Location: verify_account.php');
            exit;


        } catch (Throwable $e) {
            $db->rollBack();
            
            $errors['general'] = "Database Error: " . $e->getMessage(); 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Landlord Registration | kejaMtaani</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-bg">

<div class="auth-container">

    <div class="auth-card">
        <div class="auth-header">
            <img src="../assets/images/logo.png" alt="kejaMtaani logo">
            <h1>keja<span>Mtaani</span></h1>
            <p>Landlord Account Registration</p>
        </div>

        <form method="POST" enctype="multipart/form-data" class="auth-form">
            <?php if (!empty($errors)): ?>
                    <div style="background: #fee; color: #b00; padding: 10px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #fcc;">
                     <strong>Please fix the following:</strong>
                        <ul style="margin-top: 5px;">
                            <?php foreach ($errors as $error): ?>
                               <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-group">
                <label>National ID Number</label>
                <input type="text" name="national_id" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="example@gmail.com" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="07xxxxxxxx" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>

            <div class="upload-box">
                <label>Upload Utility Documents</label>
                <p>Accepted: Images or PDF: <br>Electricity Bill Receipt Photos, Water  Bill Receipt Photos or Rent receipts</p>
                <input type="file" name="documents[]" multiple accept="image/*,.pdf">
            </div>

            <button type="submit" class="btn-primary">
                Verify Account
            </button>

            <div class="auth-footer">
                <p>Already have an account?
                    <a href="login.php">Login</a>
                </p>
            </div>

        </form>
    </div>

</div>

</body>
</html>
