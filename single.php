<?php
    include (TEMPLATEPATH . "/includes/utils.php");
    include (TEMPLATEPATH . "/includes/head.php");
?>

<div class="main_container">
  <div class="main_container_int">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                
                <div class="entry-meta">
                    <time datetime="<?php echo get_the_date('c'); ?>" class="published">
                        <?php echo get_the_date(); ?>
                    </time>
                    <span class="author">by <?php the_author(); ?></span>
                    <?php if (has_category()) : ?>
                        <span class="categories"><?php the_category(', '); ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail('post-thumb'); ?>
                    </div>
                <?php endif; ?>
            </header>
            
            <div class="entry-content">
                <?php the_content(); ?>
                
                <?php
                wp_link_pages(array(
                    'before' => '<div class="page-links">' . __('Pages:', 'inito-wp-theme'),
                    'after'  => '</div>',
                ));
                ?>
            </div>
            
            <footer class="entry-footer">
                <?php if (has_tag()) : ?>
                    <div class="tags">
                        <?php the_tags(__('Tags: ', 'inito-wp-theme'), ', '); ?>
                    </div>
                <?php endif; ?>
            </footer>
        </article>
        
        <?php
        // Post navigation
        the_post_navigation(array(
            'prev_text' => '<span class="nav-subtitle">' . __('Previous:', 'inito-wp-theme') . '</span> <span class="nav-title">%title</span>',
            'next_text' => '<span class="nav-subtitle">' . __('Next:', 'inito-wp-theme') . '</span> <span class="nav-title">%title</span>',
        ));
        ?>
        
        <?php
        // If comments are open or we have at least one comment, load up the comment template.
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        ?>
    <?php endwhile; endif; ?>
  </div>
</div>

<?php
    include (TEMPLATEPATH . "/includes/footer.php");
    include (TEMPLATEPATH . "/includes/footer_scripts.php");
?>