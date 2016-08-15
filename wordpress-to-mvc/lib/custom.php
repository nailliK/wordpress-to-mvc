<?php
// Changing excerpt more
function new_excerpt_more($more) {
	global $post;
	return '… <a href="'. get_permalink($post->ID) . '">' . 'Read More' . '</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');

function footer_content() {
    echo '<p>This is inserted in the footer</p>';
}
add_action('wp_footer', 'footer_content');