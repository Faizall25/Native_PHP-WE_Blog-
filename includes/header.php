<header class="navbar">
    <div class="container">
        <a href="index.php" class="logo">WE Blog!</a>
        <nav>
            <a href="index.php">Beranda</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle">Profil</a>
                    <div class="dropdown-menu">
                        <a href="profile.php">Lihat Profil</a>
                        <a href="create.php">Tulis Artikel</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>