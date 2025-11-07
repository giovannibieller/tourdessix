<?php
    include (TEMPLATEPATH . "/includes/utils.php");
    include (TEMPLATEPATH . "/includes/head.php");
?>

<div class="main_container">
    <?php 
        if ( have_posts() ) : while ( have_posts() ) : the_post(); 
            $postID = $post->ID;
    ?>
        <div class="main_container_int">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>
</div>

<?php
    include (TEMPLATEPATH . "/includes/footer.php");
    include (TEMPLATEPATH . "/includes/footer_scripts.php");
?>