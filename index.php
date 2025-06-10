<?php
$dir = __DIR__ . '/data';
$files = array_filter(glob("$dir/*"), 'is_file');
$basename_files = array_map('basename', $files);

$article = isset($_GET['p']) ? basename($_GET['p']) : null;
$encoding = isset($_GET['enc']) ? $_GET['enc'] : 'UTF-8';
$filepath = $article ? "$dir/$article" : null;

$enc_options = ['UTF-8', 'SJIS', 'EUC-JP', 'ISO-2022-JP'];

function is_text_file($path) {
    $sample = file_get_contents($path, false, null, 0, 1024);
    return strpos($sample, "\0") === false;
}

if ($article && in_array($article, $basename_files) && file_exists($filepath) && is_text_file($filepath)) {
    $raw = file_get_contents($filepath);
    $converted = mb_convert_encoding($raw, 'UTF-8', $encoding);
    $escaped = htmlspecialchars($converted, ENT_QUOTES, 'UTF-8');
    $paragraphs = preg_split('/\R{2,}/u', $escaped);
    $html = '';

    $html .= '<form method="get"><input type="hidden" name="p" value="' . htmlspecialchars($article) . '">';
    $html .= '<h1><a href="index.php">./</a>' . $article . '</h1>';
    $html .= '<label for="enc">     Encode: </label><select name="enc" onchange="this.form.submit()">';

    foreach ($enc_options as $enc) {
        $selected = $enc === $encoding ? 'selected' : '';
        $html .= "<option value=\"$enc\" $selected>$enc</option>";
    }
    $html .= '</select></form>';

    foreach ($paragraphs as $para) {
        $html .= '<p>' . nl2br(trim($para)) . '</p>' . PHP_EOL;
    }

    $pageTitle = htmlspecialchars($article) . ' - Text Library';
} else {

    $query = isset($_GET['q']) ? $_GET['q'] : '';
    $filtered = array_filter($basename_files, function($f) use ($query, $dir) {
        return (stripos($f, $query) !== false) && is_text_file("$dir/$f");
    });
    sort($filtered);

    ob_start();
    echo "<h1>Text Library</h1>";
    echo '<form method="get"><input type="text" name="q" value="' . htmlspecialchars($query) . '" placeholder="..."><button type="submit">Search</button></form>';
    if (empty($filtered)) {
        echo "<p>Not Found</p>";
    } else {
        echo '<ul class="file-list">';
        foreach ($filtered as $file) {
            $name = basename($file);
            $fullPath = "$dir/$file";
            $ctime = filectime($fullPath);
            $mtime = filemtime($fullPath);
            echo "<li>";
            echo "<a href=\"?p=" . urlencode($name) . "\">" . htmlspecialchars($name) . "</a><br>";
            echo "<span class=\"meta\">Update: " . date('Y-m-d H:i', $mtime) . " / Create: " . date('Y-m-d H:i', $ctime) . "</span>";
            echo "</li>";
        }
        echo '</ul>';
    }
    $html = ob_get_clean();
    $pageTitle = 'Text Library';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Segoe UI", "Hiragino Kaku Gothic ProN", sans-serif;
            padding: 2em;
            max-width: 800px;
            margin: auto;
            background: #fff;
            color: #111;
            line-height: 1.8;
            font-size: 1.1em;
        }
        a {
            color: #000;
            text-decoration: underline dotted;
        }
        a:hover {
            background: #eee;
        }
        h1 {
            font-size: 1.6em;
            border-bottom: 2px solid #ccc;
            padding-bottom: 0.2em;
            margin-bottom: 1em;
        }
        form {
            margin-bottom: 1.5em;
        }
        input[type="text"] {
            padding: 0.5em;
            font-size: 1em;
            width: 65%;
            max-width: 300px;
            border: 1px solid #999;
            background: #fafafa;
            color: #000;
        }
        button, select {
            padding: 0.5em 1em;
            font-size: 1em;
            background: #333;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover, select:hover {
            background: #000;
        }
        .file-list {
            list-style: none;
            padding: 0;
        }
        .file-list li {
            margin: 1em 0;
            padding: 0.5em;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .file-list .meta {
            display: block;
            font-size: 0.9em;
            color: #666;
            margin-top: 0.3em;
        }
        p {
            margin: 1.2em 0;
        }
    </style>
</head>
<body>
<?= $html ?>
</body>
</html>
