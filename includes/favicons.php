<?php
    $seo_theme_color = get_field('theme_color', 'seo-settings');
?>

<!-- generated favicons block -->
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $tmpDir;?>/dist/ico/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $tmpDir;?>/dist/ico/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $tmpDir;?>/dist/ico/favicon-16x16.png">
<link rel="manifest" href="<?php echo $tmpDir;?>/manifest.json">
<link rel="mask-icon" href="<?php echo $tmpDir;?>/dist/ico/safari-pinned-tab.svg" color="#000000">
<meta name="msapplication-TileColor" content="#000000">
<meta name="theme-color" content="<?php echo $seo_theme_color; ?>">