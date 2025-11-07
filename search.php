<?php
    include (TEMPLATEPATH . "/includes/utils.php");
    include (TEMPLATEPATH . "/includes/head.php");
?>

<div class="main_container main_container--search">
  <div class="main_container_int">
    <div class="search_container">
      <header class="search_header">
          <h1 class="search_title">
              <?php
              printf(
                  /* translators: %s: search query. */
                  esc_html__('Search Results for: %s', 'inito-wp-theme'),
                  '<span>' . get_search_query() . '</span>'
              );
              ?>
          </h1>
      </header>

      <div class="search_results">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article id="post_<?php the_ID(); ?>" <?php post_class('search_result'); ?>>
                    <h2 class="entry_title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    
                    <div class="entry_meta">
                        <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        <span class="post_type"><?php echo get_post_type(); ?></span>
                    </div>

                    <div class="entry_summary">
                        <?php echo get_post_excerpt(get_the_ID(), 30); ?>
                    </div>
                </article>
            <?php endwhile; ?>
            
            <div class="pagination">
                <?php
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => __('Previous', 'inito-wp-theme'),
                    'next_text' => __('Next', 'inito-wp-theme'),
                ));
                ?>
            </div>
        <?php else : ?>
            <div class="no_results">
                <h2><?php _e('Nothing Found', 'inito-wp-theme'); ?></h2>
                <p><?php _e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'inito-wp-theme'); ?></p>
                
                <div class="search_form">
                    <?php get_search_form(); ?>
                </div>
            </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
    include (TEMPLATEPATH . "/includes/footer.php");
    include (TEMPLATEPATH . "/includes/footer_scripts.php");
?>
