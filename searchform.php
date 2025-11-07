<?php
/**
 * Template for displaying search forms
 * 
 * @package inito-wp-theme
 */

$search_id = wp_unique_id('search-form-');
$search_query = get_search_query();
?>

<form role="search" 
      method="get" 
      class="search-form" 
      action="<?php echo esc_url(home_url('/')); ?>"
      aria-label="<?php esc_attr_e('Search', 'inito-wp-theme'); ?>">
    
    <div class="search-form__wrapper">
        <label for="<?php echo esc_attr($search_id); ?>" class="search-form__label">
            <span class="screen-reader-text"><?php esc_html_e('Search for:', 'inito-wp-theme'); ?></span>
        </label>
        
        <div class="search-form__input-wrapper">
            <input type="search" 
                   id="<?php echo esc_attr($search_id); ?>"
                   class="search-form__field" 
                   placeholder="<?php esc_attr_e('What are you looking for?', 'inito-wp-theme'); ?>" 
                   value="<?php echo esc_attr($search_query); ?>" 
                   name="s"
                   autocomplete="off"
                   required
                   aria-describedby="<?php echo esc_attr($search_id); ?>-description" />
            
            <button type="submit" 
                    class="search-form__submit"
                    aria-label="<?php esc_attr_e('Submit search', 'inito-wp-theme'); ?>">
                <span class="search-form__submit-text" aria-hidden="true">
                    <?php esc_html_e('Search', 'inito-wp-theme'); ?>
                </span>
                <svg class="search-form__icon" 
                     width="20" 
                     height="20" 
                     viewBox="0 0 24 24" 
                     fill="none" 
                     stroke="currentColor" 
                     stroke-width="2" 
                     stroke-linecap="round" 
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
        </div>
        
        <div id="<?php echo esc_attr($search_id); ?>-description" class="search-form__description screen-reader-text">
            <?php esc_html_e('Press Enter to search or Escape to close', 'inito-wp-theme'); ?>
        </div>
    </div>
</form>
