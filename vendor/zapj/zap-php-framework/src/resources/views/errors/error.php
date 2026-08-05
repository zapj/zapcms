<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>错误 - Zap PHP Framework</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f7fa; color: #333; padding: 20px;
        }
        .header {
            background: #fff; border: 1px solid #e1e4e8; border-radius: 8px; padding: 16px 20px;
            margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .header h3 { font-size: 18px; color: #d73a49; }
        .header p { font-size: 13px; color: #666; margin-top: 6px; }
        .label {
            display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
            margin-right: 6px;
        }
        .label-danger { background: #ffdce0; color: #86181d; }
        .label-info { background: #dbedff; color: #0366d6; }
    </style>
</head>
<body>
    <div class="header">
        <h3>应用程序错误</h3>
        <p>
            <span class="label label-danger"><?= $status ?? 500 ?></span>
            HTTP 状态码
        </p>
        <p style="margin-top:12px;font-size:14px;">Debug 模式已启用，以下为详细错误信息。</p>
    </div>

    <?= $html ?? '' ?>
</body>
</html>
