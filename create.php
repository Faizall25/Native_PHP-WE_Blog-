<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user yang sedang login
$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT username FROM user WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$username = $user_data['username'] ?? 'Pengguna Anonim';
$user_stmt->close();

// Cek apakah user ini sudah memiliki author record
$author_id = null;
$author_name = $username; // Default ke username

$author_stmt = $conn->prepare("SELECT author_id, name FROM author WHERE user_id = ?");
$author_stmt->bind_param("i", $user_id);
$author_stmt->execute();
$author_result = $author_stmt->get_result();
$author_data = $author_result->fetch_assoc();

if ($author_data) {
    $author_id = $author_data['author_id'];
    $author_name = $author_data['name'] ?? $username;
} else {
    // Jika tidak ada, buat record baru di tabel author
    $insert_author_stmt = $conn->prepare("INSERT INTO author (user_id, name) VALUES (?, ?)");
    $insert_author_stmt->bind_param("is", $user_id, $username);
    if ($insert_author_stmt->execute()) {
        $author_id = $conn->insert_id; // Ambil author_id yang baru dibuat
    }
    $insert_author_stmt->close();
}
$author_stmt->close();

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $publish_date = $_POST['publish_date'];
    $categories = isset($_POST['categories']) && is_array($_POST['categories']) ? $_POST['categories'] : [];

    // Handle file upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/';
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image_url = $image_name;
        } else {
            $error = "Gagal mengunggah gambar.";
        }
    }

    // Simpan artikel
    $stmt = $conn->prepare("INSERT INTO article (user_id, title, content, publish_date, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $title, $content, $publish_date, $image_url);
    $stmt->execute();
    $article_id = $conn->insert_id;
    $stmt->close();

    // Simpan relasi penulis (article_author)
    if ($author_id) {
        $stmt = $conn->prepare("INSERT INTO article_author (article_id, author_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $article_id, $author_id);
        $stmt->execute();
        $stmt->close();
    }

    // Simpan relasi kategori (article_category)
    if (!empty($categories)) {
        $stmt = $conn->prepare("INSERT INTO article_category (article_id, category_id) VALUES (?, ?)");
        foreach ($categories as $category_id) {
            if (is_numeric($category_id) && $category_id > 0) {
                $stmt->bind_param("ii", $article_id, $category_id);
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tulis Artikel - Blog Dinamis</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .editor-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .editor-toolbar button {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            background: #004aad;
            color: white;
            cursor: pointer;
            font-size: 0.9em;
            transition: background 0.3s ease, transform 0.1s ease;
        }

        .editor-toolbar button:hover {
            background: #003b8c;
            transform: translateY(-2px);
        }

        .editor-content {
            min-height: 300px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1.1em;
            line-height: 1.6;
            background: #fff;
            overflow-y: auto;
        }

        .editor-content:focus {
            outline: none;
            border-color: #004aad;
            box-shadow: 0 0 5px rgba(0, 74, 173, 0.3);
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
            <h1>Tulis Artikel Baru</h1>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="create.php" method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <label for="title">Judul</label>
                    <input type="text" id="title" name="title" required placeholder="Masukkan judul artikel...">
                </div>

                <div class="form-section">
                    <label for="image">Unggah Gambar Utama</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>

                <div class="form-section">
                    <label for="content">Isi Artikel</label>
                    <div class="editor-toolbar">
                        <button type="button" onclick="document.execCommand('bold', false, null);">B</button>
                        <button type="button" onclick="document.execCommand('italic', false, null);">I</button>
                        <button type="button" onclick="document.execCommand('underline', false, null);">U</button>
                        <button type="button" onclick="document.execCommand('createLink', false, prompt('Masukkan URL:', 'http://'));">Link</button>
                        <button type="button" onclick="document.execCommand('insertImage', false, prompt('Masukkan URL gambar:', ''));">Gambar</button>
                    </div>
                    <div id="editor" class="editor-content" contenteditable="true" oninput="updateTextarea()"></div>
                    <textarea id="content" name="content" style="display: none;"></textarea>
                </div>

                <div class="form-section form-meta">
                    <div class="meta-field">
                        <label for="publish_date">Tanggal Publikasi</label>
                        <input type="date" id="publish_date" name="publish_date" required>
                    </div>
                    <div class="meta-field">
                        <label>Penulis</label>
                        <input type="text" value="<?php echo htmlspecialchars($author_name); ?>" readonly>
                    </div>
                    <div class="meta-field">
                        <label for="categories">Kategori</label>
                        <select id="categories" name="categories[]" class="category-select" required>
                            <option value="" disabled selected>Pilih Kategori</option>
                            <?php
                            $result = $conn->query("SELECT category_id, name FROM category");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['category_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <button type="submit" name="submit">Publikasikan Artikel</button>
            </form>
        </div>
    </main>

    <script>
        function updateTextarea() {
            const editor = document.getElementById('editor');
            const textarea = document.getElementById('content');
            textarea.value = editor.innerHTML;
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>