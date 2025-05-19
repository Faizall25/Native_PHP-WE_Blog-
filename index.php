<?php
session_start();
include 'includes/db_connect.php';

// Query untuk mengambil daftar artikel
$query = "
    SELECT 
        a.article_id,
        a.title,
        a.content,
        a.publish_date,
        a.image_url,
        u.username,
        GROUP_CONCAT(DISTINCT au.name) as authors,
        GROUP_CONCAT(DISTINCT c.name) as categories
    FROM article a
    LEFT JOIN user u ON a.user_id = u.user_id
    LEFT JOIN article_author aa ON a.article_id = aa.article_id
    LEFT JOIN author au ON aa.author_id = au.author_id
    LEFT JOIN article_category ac ON a.article_id = ac.category_id
    LEFT JOIN category c ON ac.category_id = c.category_id
    GROUP BY a.article_id
    ORDER BY a.publish_date DESC
";
$result = $conn->query($query);

// Query untuk artikel populer (dummy)
$popular_query = "
    SELECT 
        a.article_id,
        a.title,
        u.username
    FROM article a
    LEFT JOIN user u ON a.user_id = u.user_id
    ORDER BY a.created_at DESC
    LIMIT 3
";
$popular_result = $conn->query($popular_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Dinamis</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container main-content">
        <div class="content-wrapper">
            <!-- Daftar Artikel -->
            <div class="article-list">
                <h1>Beranda</h1>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card card-horizontal">
                        <div class="card-image">
                            <?php if ($row['image_url']): ?>
                                <img src="assets/images/<?php echo htmlspecialchars($row['image_url']); ?>" alt="Gambar Artikel">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x200" alt="Gambar Placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="card-content">
                            <div class="meta"><?php echo htmlspecialchars($row['categories'] ?? 'Tidak ada kategori'); ?></div>
                            <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                            <div class="meta">
                                Oleh: <?php echo htmlspecialchars($row['username'] ?? 'Tidak ada kategori'); ?> |
                                Penulis: <?php echo htmlspecialchars($row['authors'] ?? 'Tidak ada kategori'); ?> |
                                <?php echo $row['publish_date']; ?>
                            </div>
                            <p><?php echo htmlspecialchars(substr($row['content'], 0, 150)); ?>...</p>
                            <div class="card-stats">
                                <span>18 Suka</span> • <span>5 Komentar</span>
                            </div>
                            <a href="article.php?id=<?php echo $row['article_id']; ?>" class="read-more">Baca Selengkapnya</a>
                            <?php if ($row['image_url']): ?>
                                <a href="assets/images/<?php echo htmlspecialchars($row['image_url']); ?>" download class="download-link">Unduh Gambar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Sidebar -->
            <aside class="sidebar">
                <h2>Artikel Populer</h2>
                <?php while ($popular_row = $popular_result->fetch_assoc()): ?>
                    <div class="popular-item">
                        <h3><?php echo htmlspecialchars($popular_row['title']); ?></h3>
                        <div class="meta">Oleh: <?php echo htmlspecialchars($popular_row['username']); ?></div>
                        <div class="meta">Dibaca: <?php echo rand(100, 500); ?> kali</div>
                    </div>
                <?php endwhile; ?>
            </aside>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>