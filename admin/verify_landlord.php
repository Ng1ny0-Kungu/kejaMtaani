<?php
session_start();
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../config/mail_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    exit('Unauthorized');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid request');
}

$db = (new Database())->getConnection();
$userId = (int) $_GET['id'];

// --- Handle Approval Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        
        $stmtDetails = $db->prepare("
            SELECT u.email, lp.full_name 
            FROM users u 
            JOIN landlord_profiles lp ON u.user_id = lp.user_id 
            WHERE u.user_id = ?
        ");
        $stmtDetails->execute([$userId]);
        $userToNotify = $stmtDetails->fetch();

        
        $stmt = $db->prepare("UPDATE users SET is_verified = 1, is_active = 1 WHERE user_id = ? AND user_type = 'landlord'");
        
        if ($stmt->execute([$userId])) {
            
            if ($userToNotify) {
                sendApprovalEmail($userToNotify['email'], $userToNotify['full_name']);
            }
        }
    }

    if (isset($_POST['reject'])) {
        
        $stmt = $db->prepare("UPDATE users SET is_verified = 0 WHERE user_id = ? AND user_type = 'landlord'");
        $stmt->execute([$userId]);
    }

    header("Location: admin_dashboard.php?action=success");
    exit;
}


$stmt = $db->prepare("
    SELECT u.user_id, u.email, u.phone_number, u.is_verified, 
           lp.full_name, lp.national_id, u.otp_status, u.otp_expiry
    FROM users u
    JOIN landlord_profiles lp ON u.user_id = lp.user_id
    WHERE u.user_id = ? AND u.user_type = 'landlord'
    LIMIT 1
");
$stmt->execute([$userId]);
$landlord = $stmt->fetch(); 

if (!$landlord) {
    exit('Landlord not found');
}


$docsStmt = $db->prepare("
    SELECT document_id, document_type, document_path, document_name, 
           verification_status, rejection_reason, uploaded_at
    FROM verification_documents
    WHERE landlord_id = ?
    ORDER BY uploaded_at DESC
");
$docsStmt->execute([$userId]);
$documents = $docsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Landlord | kejaMtaani Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body class="admin-bg">

<nav class="admin-nav">
    <div class="nav-content">
        <div class="brand">
            <h1>keja<span>Mtaani</span> Admin</h1>
        </div>
        <div class="nav-actions">
            <a href="admin_dashboard.php" class="view-link">← Back</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<div class="admin-wrapper">
    <div class="section-card">
        <div class="section-header">
            <h2>Landlord Verification Panel</h2>
        </div>

        <div style="margin-bottom:25px;">
            <p><strong>Name:</strong> <?= htmlspecialchars($landlord['full_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($landlord['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($landlord['phone_number']) ?></p>
            <p><strong>ID Number:</strong> <?= htmlspecialchars($landlord['national_id']) ?></p>
            <p>
                <strong>Verification Status:</strong>
                <?php if ($landlord['is_verified']): ?>
                    <span class="badge verified" style="background:#2e7d32; color:white; padding:4px 8px; border-radius:4px;">✔ Fully Verified</span>
                <?php else: ?>
                    <span class="badge pending" style="background:#f57c00; color:white; padding:4px 8px; border-radius:4px;">Pending Admin Approval</span>
                <?php endif; ?>
            </p>
        </div>

        <div style="margin-bottom:30px;">
            <h3>Uploaded Documents</h3>
            <?php if ($documents): ?>
                <div class="documents-grid">
                    <?php foreach ($documents as $doc): ?>
                        <div class="document-card" style="border:1px solid #ddd; padding:15px; border-radius:8px; margin-bottom:10px;">
                            <p><strong>Uploaded:</strong> <?= htmlspecialchars($doc['uploaded_at']) ?></p>
                            <?php
                            $displayName = $doc['document_name'];
                            if (empty($displayName)) {
                                $parts = explode('_', $doc['document_path'], 2);
                                $displayName = $parts[1] ?? $doc['document_path'];
                            }
                            ?>
                            <p><strong>File:</strong> <?= htmlspecialchars($displayName) ?></p>
                            <a href="download_document.php?id=<?= $doc['document_id'] ?>" 
                               class="verify-btn" style="text-decoration:none; display:inline-block; margin-top:8px; background:#2196F3; color:white; padding:5px 12px; border-radius:4px;">
                                View / Download Document
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No documents uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="action-links" style="border-top:1px solid #eee; padding-top:20px;">
            <?php if ($landlord['is_verified']): ?>
                <div style="background:#e8f5e9; padding:15px; border-radius:8px; border:1px solid #c8e6c9;">
                    <p style="color:#2e7d32; margin:0;"><strong>Account Verified:</strong> An activation email has been sent to the landlord.</p>
                </div>
                <form method="POST" style="margin-top:15px;">
                    <button type="submit" name="reject" class="verify-btn" style="background:#c62828; border:none; color:white; padding:10px 20px; border-radius:5px; cursor:pointer;">
                        Revoke Verification (Undo)
                    </button>
                </form>

            <?php elseif ($landlord['otp_status'] === 'passed'): ?>
                <div style="background:#fff3e0; padding:15px; border-radius:8px; border:1px solid #ffe0b2; margin-bottom:15px;">
                    <p style="color:#e65100; margin:0;"><strong>Step 1 Complete:</strong> The landlord successfully verified their email via OTP.</p>
                </div>
                
                <form method="POST">
                    <button type="submit" name="approve" class="verify-btn" style="background:#2e7d32; border:none; color:white; padding:12px 24px; border-radius:5px; cursor:pointer; font-weight:bold;">
                        Grant Verified Badge & Approve Account
                    </button>
                </form>

            <?php elseif ($landlord['otp_status'] === 'sent'): ?>
                <div style="background:#eceff1; padding:15px; border-radius:8px; border:1px solid #cfd8dc;">
                    <p style="color:#455a64; margin:0;"><strong>Waiting for Landlord:</strong> OTP has been sent. The landlord must enter the code before you can finalize approval.</p>
                </div>

            <?php else: ?>
                <span class="badge" style="background:#9e9e9e; color:white; padding:5px 10px; border-radius:4px;">No OTP Action Recorded</span>
            <?php endif; ?>
        </div>
    </div> 
</div>

</body>
</html>