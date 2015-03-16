<?php
/*
Plugin Name: Yandex RSS2 Export Feed
Plugin URI: http://code.google.com/p/yandex-feed/
Description: Export RSS2 for Yandex
Author: Trinity Solution LLC (coded by Madness), changed for Wordpress 2.6+ by Sherif aka sharof2000 (sharof2000@gmail.com), tweaked for wordpress 3.5+ by Ivan Matveev
Version: 1.4.2
Author URI: http://code.google.com/p/yandex-feed/
*/

if (empty($wp)) {
	require_once('wp-config.php');
}
function filter_text($content) {
	$content = preg_replace( "/(\[).*?(\])/", "" , $content);
	return $content;
}
function get_posts_my($category, $numberposts, $days) {
	global $wpdb;

	$posts = $wpdb->get_results(
		"SELECT * FROM $wpdb->posts p 
			INNER JOIN $wpdb->term_relationships tr ON (p.ID = tr.object_id) 
			INNER JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) 
			INNER JOIN $wpdb->terms t ON (tt.term_id = t.term_id)
			WHERE tt.taxonomy = 'category' AND p.post_type = 'post' AND p.post_status = 'publish' AND t.term_id IN (".$category.")
			AND TO_DAYS(NOW()) - TO_DAYS(post_date) <= ".intval($days)."
			GROUP BY p.ID ORDER BY p.post_date DESC LIMIT ".intval($numberposts));
	return $posts;
}
//header('Content-type: text/xml; charset=' . get_settings('blog_charset'), true);
$options = get_option('rss_yandex_options');
$posts = get_posts_my(implode(',', $options['list_categories']), $options['num_posts'], $options['days']);

$more = 1;
echo '<?xml version="1.0" encoding="'.get_settings('blog_charset').'"?>
'; ?><!-- generator="wordpress/<?php bloginfo_rss('version') ?>" -->
<rss version="2.0" xmlns="http://backend.userland.com/rss2" xmlns:yandex="http://news.yandex.ru">
<channel>
	<title><?php echo htmlspecialchars(get_bloginfo_rss('name'),ENT_QUOTES); ?></title>
	<link><?php echo htmlspecialchars(get_bloginfo_rss('url'),ENT_QUOTES); ?></link>
	<description><?php echo htmlspecialchars(get_bloginfo_rss('description'),ENT_QUOTES); ?></description>
<?php if ($options['image_url'] != "") { ?>
	<image>
		<url><?php echo htmlspecialchars($options['image_url'],ENT_QUOTES); ?></url> 
		<title><?php echo htmlspecialchars($options['image_title'],ENT_QUOTES); ?></title> 
		<link><?php echo htmlspecialchars($options['image_link'],ENT_QUOTES); ?></link> 
	</image>
<?php } ?>
<?php
	if ($posts)
	{
		foreach ($posts as $post)
		{
			start_wp();
?>
	<item>
		<title><?php echo htmlspecialchars(get_the_title_rss(),ENT_QUOTES); ?></title>
		<link><?php echo htmlspecialchars(get_permalink(),ENT_QUOTES); ?></link>
		<?php
		if ($options['description'])
		{
			$description =  filter_text( apply_filters('the_excerpt_rss',get_the_excerpt(true)) );
			echo "\n\t\t<description>".$description."</description>\n";
		}
		echo "<category>".get_the_category_by_ID($post->term_id)."</category>\n"; 
		rss_enclosure(); ?>
		<pubDate><?php
			$gmt_offset = get_option('gmt_offset');
			$gmt_offset = ($gmt_offset>9)?$gmt_offset:('0'.$gmt_offset.'00');
			echo mysql2date('D, d M Y H:i:s +'.$gmt_offset, get_date_from_gmt(get_post_time('Y-m-d H:i:s', true)), false); ?></pubDate>
		<yandex:full-text><?php

			$content = get_the_content('', 0, '');
			$content = apply_filters('the_content_rss', $content);
			$content = filter_text($content);

			echo (htmlspecialchars(strip_tags($content, ENT_QUOTES)));
			?></yandex:full-text>
	</item>
<?php
		}
	} 
?>
</channel>
</rss>
