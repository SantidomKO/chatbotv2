<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Chatbot' ?></title>
    <?php
    global $globalStyles, $pageStyles;

    $globalStyles = $globalStyles ?? [
        'assets/css/style.css',
        'assets/css/form_style.css',
        'assets/css/sidebar.css',
    ];
    $pageStyles = $pageStyles ?? [];
    $resolvedStyles = array_values(array_unique(array_merge($globalStyles, $pageStyles)));
    foreach ($resolvedStyles as $cssPath) {
        $cssUrl = strpos($cssPath, 'http://') === 0 || strpos($cssPath, 'https://') === 0
            ? $cssPath
            : BASE_URL . ltrim($cssPath, '/');
        echo '    <link rel="stylesheet" href="' . htmlspecialchars($cssUrl) . '">' . PHP_EOL;
    }
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1001;
            background: rgba(0, 255, 255, 0.2);
            border: 1px solid rgba(0, 255, 255, 0.4);
            color: #00ffff;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body>
<button class="mobile-menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('active')" aria-label="Abrir menú">☰</button>