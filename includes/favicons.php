<?php
    $seo_theme_color = get_field('theme_color', 'seo-settings');
?>

<!-- generated favicons block -->
<link rel="icon" type="image/png" href="<?php echo $tmpDir;?>/dist/ico/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="<?php echo $tmpDir;?>/dist/ico/favicon.svg" />
<link rel="shortcut icon" href="<?php echo $tmpDir;?>/dist/ico/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $tmpDir;?>/dist/ico/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="TD6" />
<link rel="manifest" href="<?php echo $tmpDir;?>/site.webmanifest" />

<meta name="msapplication-TileColor" content="#000000">
<meta name="theme-color" content="<?php echo $seo_theme_color; ?>">