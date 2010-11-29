<?php
if ( ! function_exists( 'dyloot_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post—date/time and author.
 */
function dyloot_posted_on() {
    printf('<span class="%1$s">Posted on</span> %2$s ' .
           '<span class="meta-sep">by</span> %3$s',
           'meta-prep meta-prep-author',
           sprintf('<a href="%1$s" title="%2$s" rel="bookmark">' .
                   '<span class="entry-date">%3$s</span></a>',
                   get_permalink(),
                   esc_attr( get_the_time() ),
                   get_the_date()
           ),
           sprintf('<span class="author vcard"><a class="url fn n" '.
                   'href="%1$s" title="%2$s">%3$s</a></span>',
                   get_author_posts_url(get_the_author_meta('ID')),
                   sprintf(esc_attr('View all posts by %s'), get_the_author()),
                   get_the_author())
    );
}
endif;

if ( ! function_exists( 'dyloot_posted_in' ) ) :
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 */
function dyloot_posted_in() {
    // Retrieves tag list of current post, separated by commas.
    $tag_list = get_the_tag_list('', ', ');
    if ( $tag_list ) {
        $posted_in = 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.';
    } elseif (is_object_in_taxonomy( get_post_type(), 'category')) {
        $posted_in = 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.';
    } else {
        $posted_in = 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.';
    }
    // Prints the string, replacing the placeholders.
    printf(
        $posted_in,
        get_the_category_list(', '),
        $tag_list,
        get_permalink(),
        the_title_attribute('echo=0')
    );
}
endif;


function dyloot_init() {
    wp_deregister_script('jquery');
    wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js');
    wp_register_script('dyloot_main',
                       get_bloginfo('template_directory') . '/main.js');

    wp_enqueue_script('jquery');
    wp_enqueue_script('dyloot_main');
}

add_action('init', 'dyloot_init');
