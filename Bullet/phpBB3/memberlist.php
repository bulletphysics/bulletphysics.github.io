<?php
// 1. Extract mode and user ID from anywhere in the request (Query string, URI, or filename)
$uri = $_SERVER['REQUEST_URI'] ?? '';
$query_string = $_SERVER['QUERY_STRING'] ?? '';

$mode = $_GET['mode'] ?? '';
$user_id = $_GET['u'] ?? '';

if (empty($mode) && preg_match('/mode=([a-zA-Z]+)/', $uri, $m)) {
    $mode = $m[1];
}
if (empty($user_id) && preg_match('/[?&amp;%3D]u=([0-9]+)/i', urldecode($uri), $m)) {
    $user_id = $m[1];
}
$user_id = preg_replace('/[^0-9]/', '', $user_id);

// 2. Check if a static crawled file exists on disk (trying all naming variants)
if ($mode === 'viewprofile' && !empty($user_id)) {
    $candidates = [
        "memberlist.php?mode=viewprofile&u=" . $user_id . ".html",
        "memberlist.php%3Fmode=viewprofile&u=" . $user_id . ".html",
        "memberlist.php%3Fmode%3Dviewprofile%26u%3D" . $user_id . ".html",
        "memberlist.php?mode%3Dviewprofile%26u%3D" . $user_id . ".html"
    ];
    
    foreach ($candidates as $f) {
        if (file_exists($f) && filesize($f) > 0) {
            include($f);
            exit;
        }
    }
}

// 3. If no static file exists on disk, query the MySQL database live to generate the profile
header("HTTP/1.0 200 OK");

$username = "Unknown User";
$joined = "Unknown";
$posts = "Unknown";
$location = "";
$website = "";

if (!empty($user_id) && file_exists('config.php')) {
    include('config.php');
    if (isset($dbhost, $dbuser, $dbpasswd, $dbname, $table_prefix)) {
        $conn = @mysqli_connect($dbhost, $dbuser, $dbpasswd, $dbname);
        if ($conn) {
            $safe_id = mysqli_real_escape_string($conn, $user_id);
            $res = mysqli_query($conn, "SELECT username, user_regdate, user_posts, user_from, user_website FROM {$table_prefix}users WHERE user_id = '$safe_id'");
            if ($res && $row = mysqli_fetch_assoc($res)) {
                $username = htmlspecialchars($row['username']);
                $joined = date('D M d, Y g:i a', $row['user_regdate']);
                $posts = $row['user_posts'];
                $location = htmlspecialchars($row['user_from'] ?? '');
                $website = htmlspecialchars($row['user_website'] ?? '');
            }
            mysqli_close($conn);
        }
    }
}

if (!empty($user_id) && $username !== "Unknown User") {
    $content = '
    <div class="navbar" role="navigation">
        <div class="inner">
            <ul id="nav-breadcrumbs" class="nav-breadcrumbs linklist navlinks" role="menubar">
                <li class="breadcrumbs">
                    <span class="crumb"><a href="index.php" data-navbar-reference="index">Board index</a></span>
                </li>
            </ul>
        </div>
    </div>
    <div id="page-body" class="page-body" role="main">
        <div class="panel bg1">
            <div class="inner">
                <h2>Profile &bull; ' . $username . '</h2>
                <div class="content">
                    <dl class="details">
                        <dt>Username:</dt> <dd><strong>' . $username . '</strong></dd>
                        <dt>User ID:</dt> <dd>' . $user_id . '</dd>
                        <dt>Joined:</dt> <dd>' . $joined . '</dd>
                        <dt>Total posts:</dt> <dd>' . $posts . '</dd>' .
                        (!empty($location) ? '<dt>Location:</dt> <dd>' . $location . '</dd>' : '') .
                        (!empty($website) ? '<dt>Website:</dt> <dd><a href="' . $website . '" target="_blank">' . $website . '</a></dd>' : '') .
                    '</dl>
                    <p class="archive-note"><br><em>Note: Profile generated dynamically from the forum database.</em></p>
                </div>
            </div>
        </div>
    </div>';
    $page_title = 'Profile &bull; ' . $username;
} else {
    $content = '
    <div class="navbar" role="navigation">
        <div class="inner">
            <ul id="nav-breadcrumbs" class="nav-breadcrumbs linklist navlinks" role="menubar">
                <li class="breadcrumbs">
                    <span class="crumb"><a href="index.php" data-navbar-reference="index">Board index</a></span>
                </li>
            </ul>
        </div>
    </div>
    <div id="page-body" class="page-body" role="main">
        <div class="panel">
            <div class="inner">
                <h2>Information</h2>
                <div class="content">
                    <p>The requested profile does not exist or requires login.</p>
                </div>
            </div>
        </div>
    </div>';
    $page_title = 'Information';
}

// Borrow header, banner, custom CSS, and search bar from a real working page
$sample_path = '';
foreach (scandir(__DIR__) as $file) {
    $full_path = __DIR__ . '/' . $file;
    if ((strpos($file, 'viewtopic.php') === 0 || strpos($file, 'viewforum.php') === 0) && is_file($full_path) && filesize($full_path) > 1000) {
        $sample_path = $full_path;
        break;
    }
}

if ($sample_path) {
    $html = file_get_contents($sample_path);
    $html = preg_replace('/<title>.*?<\/title>/is', '<title>' . $page_title . ' &bull; PyBullet Forum Archive</title>', $html);
    
    $body_pos = stripos($html, '<div id="page-body"');
    $footer_pos = stripos($html, '<div id="page-footer"');
    
    if ($body_pos !== false && $footer_pos !== false) {
        $header_part = substr($html, 0, $body_pos);
        $footer_part = substr($html, $footer_pos);
        
        echo $header_part . $content . "\n" . $footer_part;
        exit;
    }
}

echo '<!DOCTYPE html><html><head><title>' . $page_title . '</title><link rel="stylesheet" href="styles/prosilver/theme/stylesheet.css"></head><body id="phpbb"><div id="wrap" class="wrap">' . $content . '</div></body></html>';
?>

