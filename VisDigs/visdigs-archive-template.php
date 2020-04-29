<?php get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
    <div class="visdigs visdigs-disclaimer">
      <?php echo get_option('visdigs_intro'); ?>
    </div>

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
    if(post_password_required()){
            echo'<div class="visdigs>';
            the_content();
            echo'</div>';
            break;
          }
    $visdigs_dayrate = get_post_meta($post->ID,'visdigs_dayrate',true);
    $visdigs_weekrate = get_post_meta($post->ID,'visdigs_weekrate',true);
    $visdigs_monthrate = get_post_meta($post->ID,'visdigs_monthrate',true);
    $visdigs_type = get_post_meta($post->ID,'visdigs_type',true);
    $visdigs_ownername = get_post_meta($post->ID,'visdigs_ownername',true);
    $visdigs_distance = get_post_meta($post->ID,'visdigs_distance',true);
    ?>

        <div class="visdigs">
          <div class="visdigs-row">
            <a href="<?php the_permalink() ?>" class="visdigs-title"><?php the_title(); ?></a>
          </div>
          <div class="visdigs-row">
            <div class="visdigs-col-50"><b>Rate: </b>
              <?php if (!$visdigs_dayrate == ""){ echo'<b>Day:</b> &pound;'. $visdigs_dayrate . ' ';} ?>
              <?php if (!$visdigs_weekrate == ""){ echo'<b>Week:</b> &pound;'. $visdigs_weekrate . ' ';} ?>
              <?php if (!$visdigs_monthrate == ""){ echo'<b>Month:</b> &pound;'. $visdigs_monthrate . ' ';} ?><br />
              <b>Distance to us:</b> <?php echo $visdigs_distance; ?><br />
              <b>Type:</b> <?php echo $visdigs_type; ?><br />
              <b>Description:</b> 
              <?php the_content();?><br/>
              <a href="<?php the_permalink();?>" class="visdigs-button">Find out More</a>
            </div>
            <div class="visdigs-col-50">
              <?php $img = wp_get_attachment_image(get_post_meta( get_the_id(), 'second_featured_image', true),'large'); ?>
              <?php if($img != ""){echo $img;}else{echo'<div class="visdigs-no-image"> </div>';} ?>
            </div>
          </div>
		    </div><!--end of visdigs-->
    
        <?php 
          //foce display one password box
          if(post_password_required()){
            break;
          }
        ?>

    <?php endwhile; else: ?>
			<p><?php _e('Sorry, no digs were found.'); ?></p>
		<?php endif; ?>

		<div class="visdigs-nav">
			<?php if(!post_password_required()){
            the_posts_pagination( array( 'mid_size' => 2 ) );
            }?>
		</div>
	</main>
</div>

<?php get_footer(); ?>
