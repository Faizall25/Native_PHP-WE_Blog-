<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "
    SELECT 
        a.article_id,
        a.title,
        a.publish_date,
        a.image_url,
        u.username
    FROM article a
    LEFT JOIN user u ON a.user_id = u.user_id
    WHERE a.user_id = ?
    ORDER BY a.publish_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fungsi untuk menghapus artikel
if (isset($_GET['delete_id']) && isset($_SESSION['user_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM article WHERE article_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    if ($stmt->execute()) {
        header("Location: profile.php");
        exit;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Blog Dinamis</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }

        .article-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 0;
        }

        .card {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            /* Full width of the container */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .card-image {
            flex: 0 0 200px;
            /* Fixed width for image */
            min-width: 200px;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px 0 0 12px;
        }

        .card-content {
            flex: 1;
            /* Take remaining space */
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-content h2 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #222;
        }

        .card-content .meta {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .card-actions a {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            min-width: 70px;
        }

        .card-actions .read-btn {
            background: #004aad;
            color: white;
        }

        .card-actions .edit-btn {
            background: #28a745;
            color: white;
        }

        .card-actions .delete-btn {
            background: #dc3545;
            color: white;
        }

        .card-actions a:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .card {
                flex-direction: column;
            }

            .card-image {
                flex: 0 0 auto;
                max-width: 100%;
                min-width: 100%;
            }

            .card-image img {
                border-radius: 12px 12px 0 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="profile-container">
            <div class="profile-info">
                <img src="https://via.placeholder.com/150" alt="Foto Profil">
                <h1><?php echo htmlspecialchars($_SESSION['username'] ?? 'Pengguna'); ?></h1>
                <div class="meta">Bergabung: 03 September 2024</div>
                <div class="meta">130 Poin</div>
                <p>Saya adalah seorang mahasiswa informatika yang suka menulis tentang teknologi dan pengalaman pribadi.</p>
            </div>
            <div class="profile-stats">
                <h2>Statistik</h2>
                <div class="stat-item">Artikel: <?php echo $result->num_rows; ?></div>
                <div class="stat-item">Tayangan: 500</div>
                <div class="stat-item">Suka: 150</div>
                <a href="create.php" class="read-more">Tulis Artikel Baru</a>
            </div>
        </div>
        <div class="article-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-image">
                        <?php if ($row['image_url']): ?>
                            <img src="assets/images/<?php echo htmlspecialchars($row['image_url']); ?>" alt="Gambar Artikel">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/200x200" alt="Gambar Placeholder">
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <div>
                            <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                            <div class="meta"><?php echo $row['publish_date']; ?></div>
                        </div>
                        <div class="card-actions">
                            <a href="article.php?id=<?php echo $row['article_id']; ?>" class="read-btn">Baca</a>
                            <a href="update.php?id=<?php echo $row['article_id']; ?>" class="edit-btn">Edit</a>
                            <a href="?delete_id=<?php echo $row['article_id']; ?>" class="delete-btn" onclick="return confirm('Yakin ingin menghapus artikel ini?')">Hapus</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>