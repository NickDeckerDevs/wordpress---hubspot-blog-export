<?php
/**
 * Template Name: Custom RSS Template For Hubspot Import
 */

$post_total = 50;
function wxr_cdata( $str ) {
	if ( ! seems_utf8( $str ) ) {
		$str = utf8_encode( $str );
	}
	// $str = ent2ncr(esc_html($str));
	$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

	return $str;
}
function wxr_post_taxonomy() {
	$post = get_post();

	$taxonomies = get_object_taxonomies( $post->post_type );
	if ( empty( $taxonomies ) )
		return;
	$terms = wp_get_object_terms( $post->ID, $taxonomies );

	foreach ( (array) $terms as $term ) {
		echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . wxr_cdata( $term->name ) . "</category>\n";
	}
}
$args = [
	'post_type' => 'post',
	'post_status' => 'publish',
	'showposts' => -1
];
global $post;
$posts = query_posts( $args );
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true ); 

echo '<?xml version="1.0" encoding="'. get_option( 'blog_charset' ) . '"?' . '>';
?>
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.2/"
	<?php do_action( 'rss2_ns' ); ?>> <!-- the double >> = end the rss tag -->
	<channel>
	    <title><?php bloginfo_rss( 'name' ); ?></title>
	    <link><?php bloginfo_rss( 'url' ) ?></link>
	    <description><?php bloginfo_rss( 'description' ) ?></description>
	    <lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
	    <language>en-US</language>
	    <wp:wxr_version>1.2</wp:wxr_version>
		<wp:base_site_url><?php get_site_url(); ?></wp:base_site_url>
		<wp:base_blog_url><?php get_bloginfo( 'url' ); ?></wp:base_blog_url>

	    <?php 
	    do_action( 'rss2_head' );
	    while( have_posts() ) : the_post();
	        $thumb_id = get_post_thumbnail_id();
			$thumb_url_array = wp_get_attachment_image_src( $thumb_id, 'thumbnail-size', true );
			$thumb_url = $thumb_url_array[0];
			$featured_image = '<div id="featured-image" style="display:none;"><img src="' . $thumb_url . '" /></div>';
		?>
	        <item>
	            <title><?php the_title_rss(); ?></title>
	            <link><?php the_permalink_rss(); ?></link>
	            <pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
	            <dc:creator><?php the_author(); ?></dc:creator>
	            <guid isPermaLink="false"><?php the_guid(); ?></guid>
	            <description><![CDATA[<?php echo $description = get_post_meta( get_the_ID(), '_yoast_wpseo_metadesc', true ) == '' ? the_excerpt_rss() : get_post_meta( get_the_ID(), '_yoast_wpseo_metadesc', true ); ?>]]></description>
	            <content:encoded><![CDATA[<?php echo $featured_image.htmlentities( $post->post_content ); ?>]]></content:encoded>
	            <wp:post_id><?php echo intval( $post->ID ); ?></wp:post_id>
	            <wp:post_date><?php echo wxr_cdata( $post->post_date ); ?></wp:post_date>
				<wp:post_date_gmt><?php echo wxr_cdata( $post->post_date_gmt ); ?></wp:post_date_gmt>
				<wp:comment_status><?php echo wxr_cdata( $post->comment_status ); ?></wp:comment_status>
				<wp:ping_status><?php echo wxr_cdata( $post->ping_status ); ?></wp:ping_status>
				<wp:post_name><?php echo wxr_cdata( $post->post_name ); ?></wp:post_name>
				<wp:status><?php echo wxr_cdata( $post->post_status ); ?></wp:status>
				<wp:post_parent><?php echo intval( $post->post_parent ); ?></wp:post_parent>
				<wp:menu_order><?php echo intval( $post->menu_order ); ?></wp:menu_order>
				<wp:post_type><?php echo wxr_cdata( $post->post_type ); ?></wp:post_type>
				<wp:post_password><?php echo wxr_cdata( $post->post_password ); ?></wp:post_password>
				<?php wxr_post_taxonomy(); ?>
	            <?php rss_enclosure(); ?>
	            <?php do_action( 'rss2_item' ); ?>
	        </item>
	    <?php endwhile; ?>
	</channel>
</rss>