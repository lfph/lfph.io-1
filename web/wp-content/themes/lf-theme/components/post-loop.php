<?php
/**
 * Post content - the loop
 *
 * @package WordPress
 * @subpackage lf-theme
 * @since 1.0.0
 */

?>
<main class="newsroom-archive">
	<div class="container wrap archive-container">
		<?php
		if ( have_posts() ) :
			// setup options.
			$options = get_option( 'lf-mu' );
			// setup loop count.
			$count = 0;

			$archive_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

			$is_in_the_news_category = ( in_category( 'news' ) ) ? true : false;

			$is_blog_category = ( in_category( 'blog' ) ) ? true : false;

			$featured_post = null;
			$sticky        = get_option( 'sticky_posts' );

			if ( 1 === $archive_page && $sticky && $is_blog_category ) {
				// check for sticky post and display if it exists.
				$args        = array(
					'posts_per_page'      => 1,
					'post_type'           => array( 'post' ),
					'post_status'         => array( 'publish' ),
					'has_password'        => false,
					'post__in'            => $sticky,
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'tax_query'           => array(
						array(
							'taxonomy' => 'category',
							'field'    => 'term_id',
							'terms'    => 'blog',
						),
					),
				);
				$stickyquery = new WP_Query( $args );

				if ( $stickyquery->have_posts() ) {
					$stickyquery->the_post();
					$sticky_post_id = get_the_ID();
					$count++;
					$is_featured = ' featured';
					$is_sticky   = ' sticky';
					lf_post_loop_show_post( $is_featured, $is_sticky, $is_in_the_news_category, $is_blog_category );
				}
				wp_reset_postdata();
			}

			while ( have_posts() ) {
				the_post();
				if ( isset( $sticky_post_id ) && get_the_ID() === $sticky_post_id ) {
					// skip re-showing the sticky post.
					continue;
				}
				$count++;
				$is_featured = ( 1 == $archive_page && 1 == $count ? ' featured' : '' );
				lf_post_loop_show_post( $is_featured, '', $is_in_the_news_category, $is_blog_category );

			}
		endif;
		?>
	</div>
</main>

<?php
/**
 * Shows the current post.
 *
 * @param string  $is_featured Featured post class.
 * @param string  $is_sticky Sticky post class.
 * @param boolean $is_in_the_news_category In the news cat.
 * @param boolean $is_blog_category In the blog cat.
 */
function lf_post_loop_show_post( $is_featured, $is_sticky, $is_in_the_news_category, $is_blog_category ) {

	if ( $is_in_the_news_category ) :
		$link_url = get_post_meta( get_the_ID(), 'lf_post_external_url', true );

		$target_attr = 'rel="noopener" target="_blank"';

		$add_external_icon = ' external is-primary-color';

		if ( ! $link_url ) {
			$target_attr       = '';
			$link_url          = get_the_permalink();
			$add_external_icon = '';
		}
		?>
<div class="archive-item in-news-item">
<div class="archive-image-wrapper"><a
		href="<?php echo esc_url( $link_url ); ?>"
		<?php echo esc_attr( $target_attr ); ?>
		title="<?php the_title(); ?>">
		<?php
		if ( has_post_thumbnail() ) {
			echo wp_get_attachment_image( get_post_thumbnail_id(), 'newsroom-media-coverage', false, array( 'class' => 'newsroom-media-coverage media-logo' ) );

		} else {
			echo '<img src="' . esc_url( get_stylesheet_directory_uri() )
			. '/images/thumbnail-default.svg" alt="LFPH Media Coverage" class="newsroom-media-coverage"/>';
		}
		?>
	</a>
</div>
<div class="archive-text-wrapper">
	<p class="archive-title"><a
			class="<?php echo esc_html( $add_external_icon ); ?>"
			href="<?php echo esc_url( $link_url ); ?>"
			<?php echo esc_attr( $target_attr ); ?>
			title="<?php the_title(); ?>">
			<?php the_title(); ?>
		</a></p>
		<p class="date-author-row"><span class="posted-date date-icon">
		<?php echo get_the_date( 'F j, Y' ); ?></span></p>
	<div class="archive-excerpt"><?php the_content(); ?></div>
</div>
</div>
		<?php
	else :

		// Get the Category Author.
		$category_author = Lf_Utils::get_term_names( get_the_ID(), 'lf-author-category', true );

		?>
<div class="archive-item<?php echo esc_html( $is_featured . $is_sticky ); ?>">

<div class="archive-image-wrapper"><a
		href="<?php the_permalink(); ?>"
		title="<?php the_title(); ?>">

		<?php

		if ( has_post_thumbnail() && $is_featured ) {
			// display large featured image.
			Lf_Utils::display_responsive_images( get_post_thumbnail_id(), 'newsroom-600', '600px', 'newsroom-image' );

		} elseif ( has_post_thumbnail() ) {
			// display smaller news image.
			Lf_Utils::display_responsive_images( get_post_thumbnail_id(), 'newsroom-300', '300px', 'newsroom-image' );

		} elseif ( isset( $options['generic_thumb_id'] ) && $options['generic_thumb_id'] ) {
			// show generic.
			Lf_Utils::display_responsive_images( $options['generic_thumb_id'], 'newsroom-260', '260px', 'newsroom-image' );

		} else {
			echo '<img src="' . esc_url( get_stylesheet_directory_uri() )
			. '/images/thumbnail-default.svg" alt="LFPH" class="newsroom-image"/>';
		}
		?>
	</a>
</div>
<div class="archive-text-wrapper">
		<?php if ( $is_blog_category && $category_author ) : ?>
	<div class="skew-box secondary centered margin-bottom">LFPH
			<?php
			echo esc_html( $category_author );
			?>
		Blog Post</div>
			<?php
		endif;
		?>

	<p class="archive-title"><a href="<?php the_permalink(); ?>"
			title="<?php the_title(); ?>">
			<?php the_title(); ?>
		</a></p>

	<p class="date-author-row"><span class="posted-date date-icon">
			<?php
			echo get_the_date();
			?>
		</span>
		<?php
		// Post author.
		if ( in_category( 'blog' ) ) :
				echo wp_kses_post( Lf_Utils::display_author( get_the_ID(), true ) );
		endif;
		?>
	</p>
	<div class="archive-excerpt"><?php the_excerpt(); ?></div>
</div>
</div>
		<?php
	endif;
}