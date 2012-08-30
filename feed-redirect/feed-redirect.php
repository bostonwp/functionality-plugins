<?php
/*
Plugin Name: BWP Feed Redirect
Plugin URI: https://github.com/BostonWP/functionality-plugins
Description: Redirect feeds to the URL of your choice. Great for easy feedburner redirect.
Version: 0.1
Author: Jon Bishop
Author URI: http://www.jonbishop.com
License: GPL2
*/
function bwp_feed_settings() {
    $feed_settings = array(
        'feed' => 'http://wordpress.org/news/feed/',
        'whitelist' => array(
            'feedburner',
            'googlebot'
        )
    );
    return apply_filters('bwp_feed_settings', $feed_settings);
}

function bwp_feed_redirect() {
    global $feed;
    // Do nothing if not a feed
    if (!is_feed()) {
        return;
    }
    
    // Grab our feed settings
    $feed_settings = bwp_feed_settings();
    
    // Do nothing if whitelisted user-agent
    foreach ($feed_settings['whitelist'] as $whitelist){
        if (preg_match('/'.$whitelist.'/i', $_SERVER['HTTP_USER_AGENT'])) 
            return;
    }

    // Redirect to our feed
    if ($feed != 'comments-rss2' && trim($feed_settings['feed']) != '') {
        do_action('bwp_feed_redirect');
        if (function_exists('status_header'))
            status_header(302);
        header("Location:" . trim($feed_settings['feed']));
        header("HTTP/1.1 302 Temporary Redirect");
        exit();
    }
}
add_action('template_redirect', &$this, 'bwp_feed_redirect');

function bwp_feedburner_feed_link($output, $feed) {
    // Grab our feed settings
    $feed_settings = bwp_feed_settings();
    $feed_url = $feed_settings['feed'];
    
    // Replace feeds
    if (trim($feed_url) != '' && $feed != 'comments-rss2') {
        $feed_array = array('rss' => $feed_url, 'rss2' => $feed_url, 'atom' => $feed_url, 'rdf' => $feed_url, 'comments_rss2' => '');
        $feed_array[$feed] = $feed_url;
        $output = $feed_array[$feed];
    }
    return apply_filters('bwp_feedburner_feed_link', $output);
}
add_filter('feed_link', &$this, 'bwp_feedburner_feed_link', 1, 2);
?>