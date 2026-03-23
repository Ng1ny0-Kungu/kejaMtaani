<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

$db = (new Database())->getConnection();


if (isset($_GET['undo'])) {
    $userId = (int) $_GET['undo'];

    $stmt = $db->prepare("
        UPDATE users
        SET is_verified = 0
        WHERE user_id = ?
          AND user_type = 'landlord'
    ");
    $stmt->execute([$userId]);

    header("Location: admin_dashboard.php");
    exit;
}


$stats = [
    'tenants' => $db->query("SELECT COUNT(*) FROM users WHERE user_type='tenant'")->fetchColumn(),
    'landlords' => $db->query("SELECT COUNT(*) FROM users WHERE user_type='landlord'")->fetchColumn(),
    'pending_landlords' => $db->query("SELECT COUNT(*) FROM users WHERE user_type='landlord' AND is_verified=0")->fetchColumn(),
];


$landlords = $db->query("
    SELECT u.user_id, 
           u.email, 
           u.phone_number, 
           u.is_verified, 
           lp.full_name, 
           lp.national_id
    FROM users u
    JOIN landlord_profiles lp ON u.user_id = lp.user_id
    WHERE u.user_type='landlord'
    ORDER BY u.is_verified ASC
")->fetchAll();


$tenants = $db->query("
    SELECT user_id, first_name, last_name, email, phone_number
    FROM users
    WHERE user_type='tenant'
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | kejaMtaani</title>
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
            <span class="admin-name">Welcome, Admin</span>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<div class="admin-wrapper">

    
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-info">
                <h3>Total Tenants</h3>
                <p><?= $stats['tenants'] ?></p>
            </div>
            <div class="stat-icon">👤</div>
        </div>

        <div class="stat-card blue">
            <div class="stat-info">
                <h3>Total Landlords</h3>
                <p><?= $stats['landlords'] ?></p>
            </div>
            <div class="stat-icon">🏠</div>
        </div>

        <div class="stat-card red">
            <div class="stat-info">
                <h3>Pending Verifications</h3>
                <p><?= $stats['pending_landlords'] ?></p>
            </div>
            <div class="stat-icon">🔔</div>
        </div>
    </div>

   
    <div class="section-card">
        <div class="section-header">
            <h2> Landlord Management</h2>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>   
                        <th>ID No.</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($landlords as $l): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($l['full_name']) ?></td>
                        <td><?= htmlspecialchars($l['email']) ?></td>
                        <td><?= htmlspecialchars($l['phone_number']) ?></td> 
                        <td><?= htmlspecialchars($l['national_id']) ?></td>

                        <td>
                            <?php if ($l['is_verified']): ?>
                                <span class="badge verified">Verified</span>
                            <?php else: ?>
                                <span class="badge pending">Pending</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="action-links">

                                
                                <a href="verify_landlord.php?id=<?= $l['user_id'] ?>" 
                                   class="view-link">
                                   View Documents
                                </a>

                                <br>
                                <a href="verify_listings.php?landlord_id=<?= $l['user_id'] ?>" 
                                   class="view-link">
                                   Verify Listings
                                </a>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>


    <div class="section-card">
        <div class="section-header">
            <h2>👤 Tenant Directory</h2>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($tenants as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?></td>
                        <td><?= htmlspecialchars($t['email']) ?></td>
                        <td><?= htmlspecialchars($t['phone_number']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

</body>
</html>
