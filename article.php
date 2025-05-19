<?php
session_start();
include 'includes/db_connect.php';

$article_id = $_GET['id'] ?? null;
$article = null;

if ($article_id) {
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
        LEFT JOIN article_category ac ON a.article_id = ac.article_id
        LEFT JOIN category c ON ac.category_id = c.category_id
        WHERE a.article_id = ?
        GROUP BY a.article_id
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article ? htmlspecialchars($article['title']) : 'Artikel Tidak Ditemukan'; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .article-detail img {
            display: block;
            margin: 20px auto;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <?php if ($article): ?>
            <div class="article-detail">
                <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="meta">
                    Oleh: <?php echo htmlspecialchars($article['username']); ?> |
                    <?php echo $article['publish_date']; ?> |
                    Penulis: <?php echo htmlspecialchars($article['authors'] ?? 'Tidak ada penulis'); ?> |
                    Kategori: <?php echo htmlspecialchars($article['categories'] ?? 'Tidak ada kategori'); ?>
                </div>
                <?php if ($article['image_url']): ?>
                    <img src="assets/images/<?php echo htmlspecialchars($article['image_url']); ?>" alt="Gambar Artikel">
                <?php endif; ?>
                <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
            </div>
        <?php else: ?>
            <p>Artikel tidak ditemukan.</p>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>