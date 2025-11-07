<?php
    include (TEMPLATEPATH . "/includes/utils.php");
    include (TEMPLATEPATH . "/includes/head.php");
?>

<div class="main_container">
  <div class="main_container_int">
    <div class="archive_container">
      <header class="archive_header">
          <h1 class="archive_title">
              <?php
              if (is_category()) :
                  single_cat_title();
              elseif (is_tag()) :
                  single_tag_title();
              elseif (is_author()) :
                  printf(__('Author: %s', 'inito-wp-theme'), '<span class="vcard">' . get_the_author() . '</span>');
              elseif (is_date()) :
                  _e('Archives', 'inito-wp-theme');
              else :
                  _e('Archives', 'inito-wp-theme');
              endif;
              ?>
          </h1>
          <?php
          $description = get_the_archive_description();
          if ($description) :
          ?>
              <div class="archive_description"><?php echo $description; ?></div>
          <?php endif; ?>
      </header>

      <div class="archive_posts_container">
          <?php if (have_posts()) : ?>
              <?php while (have_posts()) : the_post(); ?>
                  <article id="post-<?php the_ID(); ?>" <?php post_class('post-excerpt'); ?>>
                      <?php if (has_post_thumbnail()) : ?>
                          <div class="post-thumbnail">
                              <a href="<?php the_permalink(); ?>">
                                  <?php the_post_thumbnail('card-thumb'); ?>
                              </a>
                          </div>
                      <?php endif; ?>
                      
                      <div class="post-content">
                          <h2 class="post-title">
                              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                          </h2>
                          
                          <div class="post-meta">
                              <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                              <span class="author">by <?php the_author(); ?></span>
                          </div>
                          
                          <div class="post-excerpt">
                              <?php echo get_post_excerpt(get_the_ID(), 25); ?>
                          </div>
                          
                          <a href="<?php the_permalink(); ?>" class="read-more"><?php _e('Read More', 'inito-wp-theme'); ?></a>
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
              <p><?php _e('No posts found.', 'inito-wp-theme'); ?></p>
          <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
    include (TEMPLATEPATH . "/includes/footer.php");
    include (TEMPLATEPATH . "/includes/footer_scripts.php");
?>
