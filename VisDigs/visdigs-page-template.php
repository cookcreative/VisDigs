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

	</main>
</div>

<?php
    the_post_navigation( array(
        'next_text' => '<span class="meta-nav" aria-hidden="true">Next</span> ' .
            '<span class="screen-reader-text">Next post: </span> ' .
            '<span class="post-title">%title</span>',
        'prev_text' => '<span class="meta-nav" aria-hidden="true">Previous</span> ' .
            '<span class="screen-reader-text">Previous post: </span> ' .
            '<span class="post-title">%title</span>',
    ) );
?>

<?php get_footer(); ?>
