<?php
// Log viewer of logs.eszleles.hu. Independent of the Yii app, so it works when the app is down.
// It has no login of its own: the Apache vhost protects it.

// 'start' matches the first line of an entry, other lines belong to the previous entry.
$logs = [
    'access' => ['label' => 'Kérések', 'file' => '/var/log/eszleles/access.log', 'start' => '.'],
    'app' => ['label' => 'Yii hibák', 'file' => '/var/www/eszleles.hu/runtime/logs/app.log', 'start' => '^\d{4}-\d\d-\d\d '],
    'error' => ['label' => 'Apache/PHP hibák', 'file' => '/var/log/eszleles/error.log', 'start' => '^\['],
];
const CHUNK = 262144;

function mask($text)
{
    return preg_replace([
        // credentials in the variables dumped by Yii
        "/('[^'\n]*(?:password|csrf|token|_identity|PHPSESSID|HTTP_COOKIE)[^'\n]*' => )'.*'/i",
        // session id in the message prefix: [ip][user][session]
        '/^(\d{4}-\d\d-\d\d \S+ \[[^\]\n]*\]\[[^\]\n]*\])\[[^\]\n]*\]/m',
        // tokens in URLs
        '/([?&][^=&\s]*token=)[^&\s"]+/i',
    ], ['$1\'***\'', '$1[***]', '$1***'], $text);
}

header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; connect-src 'self'");
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

$key = $_GET['log'] ?? 'access';
if (!is_string($key) || !isset($logs[$key])) {
    http_response_code(404);
    exit;
}

if (isset($_GET['offset'])) {
    header('Content-Type: application/json');
    $handle = @fopen($logs[$key]['file'], 'rb');
    if (!$handle) {
        echo json_encode(['error' => 'A naplófájl nem olvasható: ' . $logs[$key]['file']]);
        exit;
    }

    $stat = fstat($handle);
    $offset = (int) $_GET['offset'];
    $first = $offset < 0;
    if ($first) {
        $offset = max(0, $stat['size'] - CHUNK);
    } elseif ((int) ($_GET['inode'] ?? 0) !== $stat['ino'] || $offset > $stat['size']) {
        $offset = 0; // the file was rotated
    }

    fseek($handle, $offset);
    $text = (string) fread($handle, CHUNK);
    // Only whole lines, unless a single line fills the chunk.
    $end = strrpos($text, "\n");
    if ($end !== false) {
        $text = substr($text, 0, $end + 1);
    } elseif (strlen($text) < CHUNK) {
        $text = '';
    }
    $next = $offset + strlen($text);
    if ($first && $offset > 0) {
        $text = (string) substr($text, (int) strpos($text, "\n") + 1);
    }

    echo json_encode(
        ['offset' => $next, 'inode' => $stat['ino'], 'text' => mask($text)],
        JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}
?>
<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $logs[$key]['label'] ?> – naplók</title>
    <style>
        body { margin: 0; background: #14171c; color: #d5d9e0; font: 13px/1.5 ui-monospace, Menlo, Consolas, monospace; }
        header { position: sticky; top: 0; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; padding: 10px 16px; background: #20252d; }
        a { color: #8ab4f8; text-decoration: none; }
        a.active { color: #fff; border-bottom: 2px solid #8ab4f8; }
        input[type=search] { flex: 1; min-width: 160px; padding: 5px 8px; font: inherit; color: inherit; background: #14171c; border: 1px solid #454c58; }
        #status { color: #f28b82; }
        pre { margin: 0; padding: 4px 16px; white-space: pre-wrap; overflow-wrap: anywhere; border-bottom: 1px solid #20252d; font: inherit; }
        .bad { color: #f28b82; }
        .warn { color: #fdd663; }
        [hidden] { display: none; }
    </style>
</head>
<body>
<header>
    <?php foreach ($logs as $name => $log): ?>
        <a href="?log=<?= $name ?>"<?= $name === $key ? ' class="active"' : '' ?>><?= $log['label'] ?></a>
    <?php endforeach; ?>
    <a href="stats">Statisztika</a>
    <input type="search" id="filter" placeholder="Szűrés…">
    <?php if ($key === 'access'): ?>
        <label><input type="checkbox" id="static" checked> képek, css, js elrejtése</label>
    <?php endif; ?>
    <label><input type="checkbox" id="pause"> szünet</label>
    <span id="status"></span>
</header>
<main id="entries"></main>
<script>
    const log = <?= json_encode($key) ?>, start = new RegExp(<?= json_encode($logs[$key]['start']) ?>);
    const el = id => document.getElementById(id), entries = el('entries');
    const asset = /"[A-Z]+ [^" ?]+\.(css|js|png|jpe?g|gif|svg|ico|woff2?|map)[? ]/i;
    let offset = -1, inode = 0;

    function show(pre) {
        const text = pre.textContent;
        pre.hidden = !text.toLowerCase().includes(el('filter').value.toLowerCase())
            || (el('static') !== null && el('static').checked && asset.test(text));
    }

    function color(pre) {
        const text = pre.textContent, status = log === 'access' ? (text.match(/" (\d)\d\d /) || [])[1] : null;
        if (status === '5' || (log !== 'access' && /\[(error|\w+:error)\]|Fatal error/.test(text))) pre.className = 'bad';
        else if (status === '4' || /\[(warning|\w+:warn)\]/.test(text)) pre.className = 'warn';
    }

    // Newest on top. Log content comes from visitors, so it is only ever added as text.
    function add(text) {
        const changed = new Set();
        for (const line of text.split('\n').slice(0, -1)) {
            if (start.test(line) || !entries.firstChild) {
                entries.prepend(document.createElement('pre'));
                entries.firstChild.textContent = line;
            } else {
                entries.firstChild.textContent += '\n' + line;
            }
            changed.add(entries.firstChild);
        }
        changed.forEach(pre => { color(pre); show(pre); });
        while (entries.childElementCount > 3000) entries.lastChild.remove();
    }

    async function poll() {
        try {
            if (!el('pause').checked) {
                const response = await fetch('?log=' + log + '&offset=' + offset + '&inode=' + inode);
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();
                if (data.error) throw new Error(data.error);
                ({offset, inode} = data);
                add(data.text);
                el('status').textContent = '';
            }
        } catch (e) {
            el('status').textContent = e.message;
        }
        setTimeout(poll, 2000);
    }

    el('filter').addEventListener('input', () => [...entries.children].forEach(show));
    if (el('static')) el('static').addEventListener('change', () => [...entries.children].forEach(show));
    poll();
</script>
</body>
</html>
