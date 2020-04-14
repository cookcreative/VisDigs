<?php get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<div class="visdigs-nav">
			<a href="<?php echo get_post_type_archive_link( 'digs' ); ?>"><span class="dashicons dashicons-arrow-left-alt2"></span> View All Digs</a>
		</div>

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <div class="visdigs">

          <?php the_post_thumbnail('medium', ['class' => 'blog-featured-image', 'title' => 'Feature image']); ?>

          <h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

          <?php the_content();?>

		    </div><!--end of visdigs-->

    <?php endwhile; else: ?>
			<p><?php _e('Sorry, no digs were found.'); ?></p>
		<?php endif; ?>

	</main>
</div>

<?php get_footer(); ?>
