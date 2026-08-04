<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>异常 - <?= htmlspecialchars($type ?? 'Error') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa; color: #333; padding: 20px;
        }
        .exception-header {
            background: #fff; border: 1px solid #e1e4e8; border-radius: 8px; padding: 16px 20px;
            margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .exception-header h3 { font-size: 18px; color: #d73a49; word-break: break-all; }
        .exception-header .meta { font-size: 13px; color: #666; margin-top: 6px; }
        .exception-header .meta span { margin-right: 12px; }
        .label {
            display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
        }
        .label-danger { background: #ffdce0; color: #86181d; }
    </style>
</head>
<body>
    <div class="exception-header">
        <h3><?= htmlspecialchars($type ?? 'Exception') ?></h3>
        <div class="meta">
            <span>文件: <?= htmlspecialchars($file ?? '') ?></span>
            <span>行: <?= $line ?? '' ?></span>
        </div>
        <p style="font-size:14px;color:#a00;margin-top:8px;"><?= htmlspecialchars($message ?? '') ?></p>
    </div>

    <?= $html ?? '' ?>
</body>
</html>
