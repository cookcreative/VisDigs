<?php get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <div class="visdigs">

          <?php the_post_thumbnail('medium', ['class' => 'blog-featured-image', 'title' => 'Feature image']); ?>

          <h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

          <?php the_content();?>

		    </div><!--end of visdigs-->

    <?php endwhile; else: ?>
			<p><?php _e('Sorry, no digs were found.'); ?></p>
		<?php endif; ?>

		<div class="visdigs-nav">
			<?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
		</div>
	</main>
</div>

<?php get_footer(); ?>
