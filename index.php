<?php
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_blog'])) {
    $title = trim($_POST['title'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title === '' || $date === '' || $author === '' || $content === '') {
        $error = 'All fields are required.';
    } else {
        $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($title));
        $filename = time() . '_' . $safeTitle . '.txt';
        $filepath = __DIR__ . '/articles/' . $filename;
        $articleText = "Title: $title\nDate: $date\nAuthor: $author\n\n$content\n";
        if (file_put_contents($filepath, $articleText) !== false) {
            $success = 'Blog added!';
        } else {
            $error = 'Could not save the blog. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Blog</title>
    <link rel="stylesheet" href="style.css">
    <style>
    .modal-bg { display: none; position: fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index: 1000; }
    .modal-bg.active { display: block; }
    .modal-form { background: #fff; max-width: 400px; margin: 80px auto; border-radius: 10px; box-shadow: 0 2px 16px rgba(0,0,0,0.18); padding: 30px 25px 25px 25px; position: relative; }
    .modal-form .close-btn { position: absolute; top: 10px; right: 18px; font-size: 22px; color: #888; cursor: pointer; }
    .top-right-actions { position: fixed; top: 24px; right: 36px; z-index: 2000; display: flex; justify-content: flex-end; align-items: center; }
    </style>
</head>
<body>
    <div class="top-right-actions">
        <button class="new-article-btn" id="openModalBtn">+ Add a New Blog</button>
    </div>
    <h1>Simple Blog</h1>
    <div class="main-layout">
        <div class="blog-list">
            <?php
            if ($success) echo '<div class="success-message">' . $success . '</div>';
            if ($error) echo '<div class="error-message">' . $error . '</div>';
            $dir = __DIR__ . '/articles';
            $files = glob($dir . '/*.txt');
            usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
            foreach ($files as $file) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                $title = $date = $author = $content = '';
                foreach ($lines as $line) {
                    if (strpos($line, 'Title:') === 0) $title = trim(substr($line, 6));
                    elseif (strpos($line, 'Date:') === 0) $date = trim(substr($line, 5));
                    elseif (strpos($line, 'Author:') === 0) $author = trim(substr($line, 7));
                    elseif ($line === '') break;
                }
                $contentStart = false;
                $contentLines = [];
                foreach ($lines as $line) {
                    if ($contentStart) $contentLines[] = $line;
                    if ($line === '') $contentStart = true;
                }
                $excerpt = htmlspecialchars(implode(' ', array_slice($contentLines, 0, 2)));
                $id = basename($file);
                echo '<div class="blog-item">';
                echo '<h2><a href="article.php?id=' . urlencode($id) . '">' . htmlspecialchars($title) . '</a></h2>';
                echo '<div class="meta">' . htmlspecialchars($date) . ' | ' . htmlspecialchars($author) . '</div>';
                echo '<p>' . $excerpt . '...</p>';
                echo '</div>';
            }
            ?>
        </div>
        <aside class="sidebar"></aside>
    </div>
    <div class="modal-bg" id="modalBg">
        <form method="post" class="modal-form" id="addBlogForm" action="">
            <span class="close-btn" id="closeModalBtn">&times;</span>
            <h2>Add a New Blog</h2>
            <input type="text" name="title" placeholder="Title" required>
            <input type="date" name="date" required>
            <input type="text" name="author" placeholder="Author" required>
            <textarea name="content" placeholder="Content" required></textarea>
            <button type="submit" name="add_blog">Save Blog</button>
        </form>
    </div>
    <script>
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    const modalBg = document.getElementById('modalBg');
    openBtn.onclick = () => { modalBg.classList.add('active'); };
    closeBtn.onclick = () => { modalBg.classList.remove('active'); };
    window.onclick = (e) => { if (e.target === modalBg) modalBg.classList.remove('active'); };
    </script>
</body>
</html> 