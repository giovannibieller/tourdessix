<!DOCTYPE html>
<html lang="<?php echo $curlang; ?>">
    <head>
        <?php include (TEMPLATEPATH . "/includes/seo.php"); ?>
        
        <?php wp_head(); ?>

        <?php include (TEMPLATEPATH . "/includes/favicons.php"); ?>
        <?php include (TEMPLATEPATH . "/includes/google_analytics.php"); ?>

    </head>

    <body class="<?php body_classes(); ?>">