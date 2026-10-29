<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Hệ thống Quản lý kho Marvel Sweet') ?> - WMS Core</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons qua CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Stylesheet dùng chung theo Design Tokens Figma -->
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar cố định bên trái (270px) -->
        <?php require __DIR__ . '/sidebar.php'; ?>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Header phía trên -->
            <?php require __DIR__ . '/header.php'; ?>

            <!-- Nội dung chính -->
            <main class="main-content">
                <?php \App\Core\View::partial('flash_message'); ?>
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <!-- Scripts dùng chung -->
    <script src="assets/js/app.js"></script>
</body>
</html>

