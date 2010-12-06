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

define('FROM_CONTACT', 'Team Dyloot <team@dyloot.com>');
define('CONTACT_POST_ID', 45);
function dyloot_contact() {
global $wpdb;
if ($_POST['contact']) {
    $contact_invalid = array();  // which fields are invalid
    $contact_data = array();
    $contact_data['name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    // for some reason idiotic wordpress escapes quotes
    $message = str_replace('\"', '"', $_POST['message']);
    $message = str_replace("\'", "'", $message);
    $contact_data['message'] = filter_var($message, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_AMP);
    $contact_data['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    foreach ($contact_data as $contact_key => $contact_field) {
        if (!$contact_field) {
            $contact_invalid[$contact_key] = true;
        }
    }

    if (!$contact_invalid) {
        $contact_headers = "From: {$contact_data['name']} <{$contact_data['email']}>\r\n"
            . "Content-Type: text/html; charset=iso-8859-1\r\n";
        $comment_post_ID = CONTACT_POST_ID;
        $comment_author = $contact_data['name'];
        $comment_author_email = $contact_data['email'];
        $comment_author_url = '';
        $comment_content = $contact_data['message'];
        $comment_type = '';
        $comment_parent = 0;
        $user_ID = 0;


        $commentdata = compact(
            'comment_post_ID', 'comment_author', 'comment_author_email',
            'comment_author_url', 'comment_content', 'comment_type',
            'comment_parent', 'user_ID');
        $comment_id = wp_new_comment($commentdata);
        $comment_approved = $wpdb->get_results(
            "SELECT (comment_approved = 'spam') AS spam
             FROM wp_comments WHERE comment_ID = '{$comment_id} LIMIT 1;'");
        if ($comment_id && !$comment_approved[0]->spam) {
            $success = true;
            // not spam!
            if (!wp_mail(FROM_CONTACT, 'Dyloot Contact Form Message',
                '<pre style="font-family: Helvetica, Arial, sans-serif">' . $contact_data['message'] . '</pre>', $contact_headers)) {
                $success = false;
            }
            $contact_headers = "From: " . FROM_CONTACT . "\r\n" . 'Reply-To: ' . FROM_CONTACT . "\r\nContent-Type: text/html; charset=iso-8859-1\r\n";
            if (!wp_mail("{$contact_data['name']} <{$contact_data['email']}>", 'Thank you for contacting dyloot'
                , '<p>Thank you for contacting <a href="http://dyloot.com">dyloot</a>. We will get back to you shortly.</p>

<p>Below is a copy of your message.
If for any reason we do not get back to you soon, simply reply to this email.</p>

<p>------------------------------</p>

<pre style="font-family: Helvetica, Arial, sans-serif">' . $contact_data['message'] . '</pre>', $contact_headers)) {
                $success = false;
            }

            if (!$success) {
                $myFile = "fail-mail.txt";
                $fh = fopen($myFile, 'a') or die("can't open file");
                $time_c = date('Y-m-d H:i:s');
                fwrite($fh, "You received a message from {$contact_data['name']} ({$contact_data['email']}) on {$time_c}\n");
                fclose($fh);
            }
            // redirect to prevent resubmission
            header("Location: {$_SERVER['HTTP_REFERER']}?contacted");
            die;
        } else {
            wp_mail(FROM_CONTACT, '[spam?] Dyloot Contact Form Message',
                '<pre style="font-family: Helvetica, Arial, sans-serif">' . $contact_data['message'] . '</pre>', $contact_headers);
        }
    }
}
}

add_action('init', 'dyloot_init');
add_action('init', 'dyloot_contact');
