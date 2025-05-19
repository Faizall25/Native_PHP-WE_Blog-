<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$article_id = $_GET['id'] ?? null;
$article = null;
$selected_categories = [];

if ($article_id) {
    // Ambil data artikel
    $stmt = $conn->prepare("SELECT * FROM article WHERE article_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $article_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set
    $article = $result->fetch_assoc(); // Now fetch_assoc works on the result set
    $stmt->close();

    if ($article) {
        // Ambil kategori terkait
        $stmt = $conn->prepare("SELECT category_id FROM article_category WHERE article_id = ?");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $selected_categories[] = $row['category_id'];
        }
        $stmt->close();
    }
}

// Ambil nama penulis berdasarkan user_id
$user_id = $_SESSION['user_id'];
$author_stmt = $conn->prepare("SELECT name FROM author WHERE author_id = ?");
$author_stmt->bind_param("i", $user_id);
$author_stmt->execute();
$author_result = $author_stmt->get_result();
$author_name = $author_result->fetch_assoc()['name'] ?? 'Pengguna Anonim';
$author_stmt->close();

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $publish_date = $_POST['publish_date'];
    $categories = $_POST['categories'] ?? [];

    // Handle file upload
    $image_url = $article['image_url']; // Gunakan gambar lama jika tidak ada unggahan baru
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/';
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image_url = $image_name; // Ganti dengan nama file baru
            // Opsional: Hapus gambar lama jika ada
            if ($article['image_url'] && file_exists($upload_dir . $article['image_url'])) {
                unlink($upload_dir . $article['image_url']);
            }
        } else {
            $error = "Gagal mengunggah gambar.";
        }
    }

    // Update artikel
    $stmt = $conn->prepare("UPDATE article SET title = ?, content = ?, publish_date = ?, image_url = ? WHERE article_id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $title, $content, $publish_date, $image_url, $article_id, $_SESSION['user_id']); // Fixed parameter types
    $stmt->execute();
    $stmt->close();

    // Hapus relasi lama
    $stmt = $conn->prepare("DELETE FROM article_category WHERE article_id = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $stmt->close();

    // Simpan relasi kategori baru
    $stmt = $conn->prepare("INSERT INTO article_category (article_id, category_id) VALUES (?, ?)");
    foreach ($categories as $category_id) {
        $stmt->bind_param("ii", $article_id, $category_id);
        $stmt->execute();
    }
    $stmt->close();

    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel - Blog Dinamis</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .form-section img {
            display: block;
            margin: 10px auto;
            max-width: 100%;
            border-radius: 8px;
        }

        .category-select {
            appearance: none;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            width: 100%;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 12px center;
            background-size: 12px;
            cursor: pointer;
        }

        .category-select:focus {
            border-color: #004aad;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 74, 173, 0.3);
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="create-article-container">
            <h1>Edit Artikel</h1>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if ($article): ?>
                <form action="update.php?id=<?php echo $article_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <label for="title">Judul</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                    </div>

                    <div class="form-section">
                        <label for="image">Unggah Gambar Baru</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if ($article['image_url']): ?>
                            <img src="assets/images/<?php echo htmlspecialchars($article['image_url']); ?>" alt="Gambar Saat Ini" width="200">
                        <?php endif; ?>
                    </div>

                    <div class="form-section">
                        <label for="content">Isi Artikel</label>
                        <textarea id="content" name="content" rows="15" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                    </div>

                    <div class="form-section form-meta">
                        <div class="meta-field">
                            <label for="publish_date">Tanggal Publikasi</label>
                            <input type="date" id="publish_date" name="publish_date" value="<?php echo $article['publish_date']; ?>" required>
                        </div>
                        <div class="meta-field">
                            <label>Penulis</label>
                            <input type="text" value="<?php echo htmlspecialchars($author_name); ?>" readonly>
                        </div>
                        <div class="meta-field">
                            <label for="categories">Kategori</label>
                            <select id="categories" name="categories" class="category-select" required>
                                <option value="" disabled <?php echo empty($selected_categories) ? 'selected' : ''; ?>>Pilih Kategori</option>
                                <?php
                                $result = $conn->query("SELECT category_id, name FROM category");
                                while ($row = $result->fetch_assoc()) {
                                    $selected = in_array($row['category_id'], $selected_categories) ? 'selected' : '';
                                    echo "<option value='{$row['category_id']}' $selected>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="submit">Simpan Perubahan</button>
                </form>
            <?php else: ?>
                <p>Artikel tidak ditemukan atau Anda tidak memiliki akses.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>