<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    exit('Unauthorized');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Invalid request');
}

$db = (new Database())->getConnection();
$userId = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $stmt = $db->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ? AND user_type = 'landlord'");
        $stmt->execute([$userId]);
    }

    if (isset($_POST['reject'])) {
        $stmt = $db->prepare("UPDATE users SET is_verified = 0 WHERE user_id = ? AND user_type = 'landlord'");
        $stmt->execute([$userId]);
    }

    header("Location: admin_dashboard.php");
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
                <strong>Status:</strong>
                <?php if ($landlord['is_verified']): ?>
                    <span class="badge verified">Verified</span>
                <?php else: ?>
                    <span class="badge pending">Pending</span>
                <?php endif; ?>
            </p>
        </div>

        <div style="margin-bottom:30px;">
            <h3>Uploaded Documents</h3>
            <?php if ($documents): ?>
                <div class="documents-grid">
                    <?php foreach ($documents as $doc): ?>
                        <div class="document-card">
                            <p><strong>Type:</strong> <?= htmlspecialchars($doc['document_type']) ?></p>
                            <p><strong>Uploaded:</strong> <?= htmlspecialchars($doc['uploaded_at']) ?></p>
                            <p>
                                <strong>Status:</strong>
                                <span class="badge <?= $doc['verification_status'] ?>">
                                    <?= ucfirst($doc['verification_status']) ?>
                                </span>
                            </p>
                            <?php if ($doc['rejection_reason']): ?>
                                <p><strong>Reason:</strong> <?= htmlspecialchars($doc['rejection_reason']) ?></p>
                            <?php endif; ?>

                            <?php
                            $displayName = $doc['document_name'];
                            if (empty($displayName)) {
                                $parts = explode('_', $doc['document_path'], 2);
                                $displayName = $parts[1] ?? $doc['document_path'];
                            }
                            ?>
                            <p><strong>File:</strong> <?= htmlspecialchars($displayName) ?></p>
                            <a href="download_document.php?id=<?= $doc['document_id'] ?>" 
                               class="verify-btn" style="text-decoration:none; display:inline-block; margin-top:8px;">
                                Download
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No documents uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="action-links">
            <?php if ($landlord['is_verified']): ?>
                <span class="badge verified">✔ Fully Verified</span>
                <form method="POST" style="margin-top:15px;">
                    <button type="submit" name="reject" class="verify-btn" style="background:#c62828;">
                        Undo Verification
                    </button>
                </form>

            <?php elseif ($landlord['otp_status'] === 'passed'): ?>
                <div class="status-alert success">
                    <p><strong>Landlord verified the OTP!</strong> You can now grant the badge.</p>
                </div>
                <a href="final_approve.php?id=<?= $landlord['user_id'] ?>" 
                   class="verify-btn" style="display:inline-block; margin-top:15px;">
                    Grant Badge & Finalize Approval
                </a>

            <?php elseif ($landlord['otp_status'] === 'sent'): ?>
                <span class="badge info">OTP Sent (Wait for Landlord Entry)</span>
                <div style="margin-top:15px;">
                    <a href="send_otp.php?id=<?= $landlord['user_id'] ?>" 
                       class="verify-btn" style="background:#6c757d; display:inline-block;">
                        Resend OTP
                    </a>
                </div>

            <?php elseif ($landlord['otp_status'] === 'requested'): ?>
                <span class="badge badge-warning">Landlord Requested OTP</span>
                <div style="margin-top:15px;">
                    <a href="send_otp.php?id=<?= $landlord['user_id'] ?>" class="verify-btn">
                        Send OTP
                    </a>
                </div>

            <?php else: ?>
                <span class="badge badge-secondary">No OTP Request Yet</span>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>