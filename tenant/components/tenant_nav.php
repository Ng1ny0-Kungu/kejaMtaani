<nav class="tenant-nav">
    <div class="nav-left">
        <h2 class="logo">KejaMtaani</h2>
    </div>

    <div class="nav-right">
        <a href="tenant_dashboard.php">Home</a>

        <a href="saved_properties.php" class="nav-link">
            <i class="fa-solid fa-bookmark"></i> My Kejas
            <?php
            if (isset($_SESSION['user_id'])):
                try {
                    // 1. Identify the connection
                    $pdo = null;
                    if (isset($db)) {
                        if ($db instanceof PDO) {
                            $pdo = $db;
                        } else if (method_exists($db, 'getConnection')) {
                            $pdo = $db->getConnection();
                        }
                    }

                    // 2. Only run if we successfully found/created a connection
                    if ($pdo):
                        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM saved_properties WHERE user_id = ?");
                        $countStmt->execute([$_SESSION['user_id']]);
                        $count = $countStmt->fetchColumn();
                        
                        if($count > 0): ?>
                            <span style="background: #00bcd4; color: white; border-radius: 50%; padding: 2px 7px; font-size: 11px; margin-left: 5px; font-weight: bold;">
                                <?= $count ?>
                            </span>
                        <?php endif;
                    endif;
                } catch (Exception $e) {
                    // Silent fail
                }
            endif; ?>
        </a>

        <a href="tenant_profile.php" class="nav-link">
            <i class="fa-solid fa-user-circle"></i> Profile
        </a>

        <a href="../auth/logout.php" class="logout">Logout</a>
    </div>
</nav>