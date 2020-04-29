<?php get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
    <div class="visdigs">
      <a href="#" onclick="window.history.go(-1); return false;">&lt; Back to listings</a>
    </div>

				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
    
          //foce display one password box
          if(post_password_required()){
            echo'<div class="visdigs">';
            the_content();
            echo'</div>';
            break;
          }
        
    
    
    $visdigs_ownername = get_post_meta($post->ID,'visdigs_ownername',true);
    $visdigs_owneremail = get_post_meta($post->ID,'visdigs_owneremail',true);
    $visdigs_ownerlandline = get_post_meta($post->ID,'visdigs_ownerlandline',true);
    $visdigs_ownermobile = get_post_meta($post->ID,'visdigs_ownermobile',true);
    $visdigs_address = get_post_meta($post->ID,'visdigs_address',true);
    $visdigs_dayrate = get_post_meta($post->ID,'visdigs_dayrate',true);
    $visdigs_weekrate = get_post_meta($post->ID,'visdigs_weekrate',true);
    $visdigs_monthrate = get_post_meta($post->ID,'visdigs_monthrate',true);
    $visdigs_rooms = get_post_meta($post->ID,'visdigs_rooms',true);
    $visdigs_type = get_post_meta($post->ID,'visdigs_type',true);
    $visdigs_pets = get_post_meta($post->ID,'visdigs_pets',true);
    $visdigs_smoking = get_post_meta($post->ID,'visdigs_smoking',true);
    $visdigs_smokealarms = get_post_meta($post->ID,'visdigs_smokealarms',true);
    $visdigs_sharedbathroom = get_post_meta($post->ID,'visdigs_sharedbathroom',true);
    $visdigs_privatebathroom = get_post_meta($post->ID,'visdigs_privatebathroom',true);
    $visdigs_towels = get_post_meta($post->ID,'visdigs_towels',true);
    $visdigs_bedding = get_post_meta($post->ID,'visdigs_bedding',true);
    $visdigs_kitchen = get_post_meta($post->ID,'visdigs_kitchen',true);
    $visdigs_laundry = get_post_meta($post->ID,'visdigs_laundry',true);
    $visdigs_communal = get_post_meta($post->ID,'visdigs_communal',true);
    $visdigs_garden = get_post_meta($post->ID,'visdigs_garden',true);
    $visdigs_offparking = get_post_meta($post->ID,'visdigs_offparking',true);
    $visdigs_onparking = get_post_meta($post->ID,'visdigs_onparking',true);
    $visdigs_wifi = get_post_meta($post->ID,'visdigs_wifi',true);
    $visdigs_tv = get_post_meta($post->ID,'visdigs_tv',true);
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
              <?php the_content();?>
            </div>
            <div class="visdigs-col-50">
              <?php $img = wp_get_attachment_image(get_post_meta( get_the_id(), 'second_featured_image', true),'large'); ?>
              <?php if($img != ""){echo $img;}else{echo'<div class="visdigs-no-image"> </div>';} ?>
            </div>
          </div>
          <div class="visdigs-row">
            <section class="digs-info">
              <span class="visdigs-heading">Further Information</span>
              <table class="visdigs-50">
              <tr>
                <td></td>
                <td></td>
              <tr>
              <tr>
                <td><b>Owner Name</b></td>
                <td><?php echo $visdigs_ownername;?></td>
              <tr>

             <tr>
                <td><b>Owner Email</b></td>
                <td><?php echo $visdigs_owneremail;?></td>
              </tr>

             <tr>
                <td><b>Owner Landline</b></td>
                <td><?php echo $visdigs_ownerlandline;?></td>
              </tr>

             <tr>
                <td><b>Owner Mobile</b></td>
                <td><?php echo $visdigs_ownermobile;?></td>
              </tr>

             <tr>
                <td><b>Address</b></td>
                <td><?php echo $visdigs_address;?></td>
              </tr>

             <tr>
                <td><b>Number of Rooms</b></td>
                <td><?php echo $visdigs_rooms;?></td>
              </tr>
              <tr>
                <td><b>Pets</b></td>
                <td><?php echo $visdigs_pets;?></td>
              <tr>

             <tr>
                <td><b>On Street Parking</b></td>
                <td><?php echo $visdigs_onparking;?></td>
              </tr>

             <tr>
                <td><b>Off Street Parking</b></td>
                <td><?php echo $visdigs_offparking;?></td>
              </tr>

             </table>
              <span class="visdigs-heading">Utilities</span>
              <table>
              <tr>
                <td width="40%">Shared Bathroom</td>
                <td width="10%"><?php if($visdigs_sharedbathroom == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
                <td width="40%">Private Bathroom</td>
                <td width="10%"><?php if($visdigs_privatebathroom == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
              </tr>
              <tr>
                <td>Towels Provided</td>
                <td><?php if($visdigs_towels == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
                <td>Bed Linen Provided</td>
                <td><?php if($visdigs_bedding == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
              </tr>
              <tr>
                <td>Use of Kitchen</td>
                <td><?php if($visdigs_kitchen == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
                <td>Use of Laundry facilities</td>
                <td><?php if($visdigs_laundry == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
              </tr>
              <tr>
                <td>Use of Communal areas</td>
                <td><?php if($visdigs_communal == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
                <td>Use of Garden</td>
                <td><?php if($visdigs_garden == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
              </tr>
              <tr>
                <td>Smoke Alarms Installed</td>
                <td><?php if($visdigs_smokealarms == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
                <td>Non-Smoking Property</td>
                <td><?php if($visdigs_smoking == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
              </tr>
              <tr>
                <td>Wifi Available</td>
                <td><?php if($visdigs_wifi == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
                <td>TV in room</td>
                <td><?php if($visdigs_tv == "Yes"){echo"&check;";}else{echo"&cross;";}?></td>
              </tr>
             </table>
            <span class="visdigs-heading">Images</span>
              <div class="visdigs-single-images">
              <?php echo wp_get_attachment_image(get_post_meta( get_the_id(), 'second_featured_image', true),'full'); ?>
              <?php echo wp_get_attachment_image(get_post_meta( get_the_id(), 'third_featured_image', true),'full'); ?>
              <?php echo wp_get_attachment_image(get_post_meta( get_the_id(), 'fourth_featured_image', true),'full'); ?>
              <?php echo wp_get_attachment_image(get_post_meta( get_the_id(), 'fith_featured_image', true),'full'); ?>
              <?php echo wp_get_attachment_image(get_post_meta( get_the_id(), 'sixth_featured_image', true),'full'); ?>
              </div>
           </section>
          </div>
		    </div><!--end of visdigs-->


    <?php endwhile; else: ?>
			<p><?php _e('Sorry, no digs were found.'); ?></p>
		<?php endif; ?>

	</main>
</div>

<?php get_footer(); ?>
