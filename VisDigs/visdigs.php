<?php
defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );
/*
Plugin Name: VisDigs
Plugin URI:  https://cookcreativemedia.com
Description: Creates an interfaces to manage digs.
Version:     1.0.2
Author:      James Cook
Author URI:  http://cookcreativemedia.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

//Adapted from https://www.sitepoint.com/real-world-example-wordpress-plugin-development/

class visdigs{

//magic function (triggered on initialization)
public function __construct(){

    add_action('init', array($this,'register_digs_content_type')); //register digs content type
    add_action('add_meta_boxes', array($this,'add_digs_meta_boxes')); //add meta boxes
    add_action('save_post_digs', array($this,'save_digs')); //save location
    add_action('admin_enqueue_scripts', array($this,'enqueue_admin_scripts_and_styles')); //admin scripts and styles
    add_action('wp_enqueue_scripts', array($this,'enqueue_public_scripts_and_styles')); //public scripts and styles
    add_action('the_post', array($this,'passwordProtectPosts')); //password protects the post type
    add_action('pre_get_posts', array($this,'exclude_protected_action'));//hide post type from rest of the website
    add_action('admin_init', array($this,'visdigs_register_settings' ));
    add_action('admin_menu', array($this, 'visdigs_register_options_page'));
    add_filter('protected_title_format', array($this, 'remove_protected_text'));

    // Move all "advanced" metaboxes above the default editor
    add_action('edit_form_after_title', function() {
      global $post, $wp_meta_boxes;
      do_meta_boxes(get_current_screen(), 'advanced', $post);
      unset($wp_meta_boxes[get_post_type($post)]['advanced']);
    });

    add_filter('manage_posts_columns', array($this,'visdigs_columns_head'));
    add_action('manage_posts_custom_column', array($this,'visdigs_columns_content'), 10, 2);
    add_filter('single_template', array($this,'visdigs_single_template'));
    add_filter('archive_template', array($this,'visdigs_archive_template'));

    register_activation_hook(__FILE__, array($this,'plugin_activate')); //activate hook
    register_deactivation_hook(__FILE__, array($this,'plugin_deactivate')); //deactivate hook
  
    add_action( 'pre_get_posts', array($this,'visdigs_query') );

}

//triggered on activation of the plugin (called only once)
public function plugin_activate(){
    //call our custom content type function
    $this->register_digs_content_type();
    //flush permalinks
    flush_rewrite_rules();
}
//trigered on deactivation of the plugin (called only once)
public function plugin_deactivate(){
    //flush permalinks
    flush_rewrite_rules();
}

//register the digs content type
public function register_digs_content_type(){
     //Labels for post type
     $labels = array(
           'name'               => 'Digs',
           'singular_name'      => 'Digs',
           'menu_name'          => 'Digs',
           'name_admin_bar'     => 'Digs',
           'add_new'            => 'Add New',
           'add_new_item'       => 'Add New Digs',
           'new_item'           => 'New Digs',
           'edit_item'          => 'Edit Details',
           'view_item'          => 'View Digs',
           'all_items'          => 'All Digs',
           'search_items'       => 'Search Digs',
           'parent_item_colon'  => 'Parent Digs:',
           'not_found'          => 'No Digs found.',
           'not_found_in_trash' => 'No Digs found in Trash.'
       );
       //arguments for post type
       $args = array(
           'labels'            => $labels,
           'public'            => true,
           'publicly_queryable'=> true,
           'show_ui'           => true,
           'show_in_nav'       => true,
           'query_var'         => true,
           'hierarchical'      => false,
           'supports'          => array('title','editor'),
           'has_archive'       => true,
           'menu_position'     => 20,
           'show_in_admin_bar' => true,
           'menu_icon'         => 'dashicons-location-alt',
           'rewrite'            => array('slug' => 'digs', 'with_front' => 'true')
       );
       //register post type
       register_post_type('digs', $args);
}

public function visdigs_columns_head($defaults) {
    $defaults['visdigs_ownername_col'] = 'Owner Name';
    $defaults['visdigs_address_col'] = 'Address';
    return $defaults;
}

public function visdigs_columns_content($column_name, $post_ID) {
    if ($column_name == 'visdigs_ownername_col') {
        $visdigs_ownername = get_post_meta($post_ID,'visdigs_ownername',true);
        if ($visdigs_ownername) {
            echo $visdigs_ownername;
        }else{
            echo"<i>None</i>";
        }
    }
    if ($column_name == 'visdigs_address_col') {
        $visdigs_address = get_post_meta($post_ID,'visdigs_address',true);
        if ($visdigs_address) {
            echo $visdigs_address;
        }else{
            echo"<i>None</i>";
        }
    }
}

//adding meta boxes for the digs content type
public function add_digs_meta_boxes(){

    add_meta_box(
        'visdigs_meta_box', //id
        'Digs Information', //name
        array($this,'visdigs_meta_box_display'), //display function
        'digs', //post type
        'advanced', //location
        'high' //priority
    );
 }

//display function used for our custom location meta box*/
public function visdigs_meta_box_display($post){

    //set nonce field
    wp_nonce_field('visdigs_nonce', 'visdigs_nonce_field');

    //collect variables
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
    <p>Enter additional information about your digs </p>
    <div class="field-container">
        <?php
        //before main form elementst hook
        do_action('visdigs_admin_form_start');
        ?>
      <div class="field">
            <label for="visdigs_ownername">Owner Name</label>
            <small>Full name of owner</small>
            <input type="text" name="visdigs_ownername" id="visdigs_ownername" value="<?php echo $visdigs_ownername;?>"/>
        </div>
        <div class="field">
            <label for="visdigs_ownerlandline">Owner Landline</label>
            <small>Local Landline Number</small>
            <input type="tel" name="visdigs_ownerlandline" id="visdigs_ownerlandline" value="<?php echo $visdigs_ownerlandline;?>"/>
        </div>
      <div class="field">
            <label for="visdigs_ownermobile">Owner Mobile</label>
            <small>Mobile Phone Number</small>
            <input type="tel" name="visdigs_ownermobile" id="visdigs_ownermobile" value="<?php echo $visdigs_ownermobile;?>"/>
        </div>
        <div class="field">
            <label for="visdigs_owneremail">Contact Email</label>
            <small>Email contact</small>
            <input type="email" name="visdigs_owneremail" id="visdigs_owneremail" value="<?php echo $visdigs_owneremail;?>"/>
        </div>
        <div class="field">
            <label for="visdigs_address">Street Address</label>
            <small>Location of the digs</small>
            <input type="text" name="visdigs_address" id="visdigs_address" value="<?php echo $visdigs_address;?>"/>
        </div>
        <div class="field">
            <label for="visdigs_address">Distance to Theatre</label>
            <small>E.g. 2 miles, 20min walk</small>
            <input type="text" name="visdigs_distance" id="visdigs_distance" value="<?php echo $visdigs_distance;?>"/>
        </div>
       <div class="field">
            <label for="visdigs_dayrate">Day Rate</label>
            <small>Cost for single night stay</small>
            <input type="text" name="visdigs_dayrate" id="visdigs_dayrate" value="<?php echo $visdigs_dayrate;?>"/>
        </div>
        <div class="field">
            <label for="visdigs_weekrate">Week Rate</label>
            <small>Cost for week long stay</small>
            <input type="text" name="visdigs_weekrate" id="visdigs_weekrate" value="<?php echo $visdigs_weekrate;?>"/>
        </div>
        <div class="field">
            <label for="visdigs_monthrate">Month Rate</label>
            <small>Cost for month long stay</small>
            <input type="text" name="visdigs_monthrate" id="visdigs_monthrate" value="<?php echo $visdigs_monthrate;?>"/>
        </div>
        <div class="field">
            <label for="visdigs_rooms">Number of Rooms</label>
            <small>Total number of rooms (please specify if single or double)</small>
            <input type="text" name="visdigs_rooms" id="visdigs_rooms" value="<?php echo $visdigs_rooms;?>"/>
        </div>
       <div class="field">
            <label for="visdigs_type">Type of digs</label>
            <small>Self Contained/Staying with Owner/B&amp;B/Hotel</small>
            <input type="text" name="visdigs_type" id="visdigs_type" value="<?php echo $visdigs_type;?>"/>
        </div>
      
      <div class="field">
            <label for="visdigs_type">Do you have any pets?</label>
            <small>(Please Specify)</small>
            <input type="text" name="visdigs_pets" id="visdigs_pets" value="<?php echo $visdigs_pets;?>"/>
      </div>
      <div class="field">
            <label for="visdigs_type">On Street Parking?</label>
            <small>Yes/No/Other</small>
            <input type="text" name="visdigs_onparking" id="visdigs_onparking" value="<?php echo $visdigs_onparking;?>"/>
        </div>
      <div class="field">
            <label for="visdigs_type">Off Street Parking?</label>
            <small>Yes/No/Other</small>
            <input type="text" name="visdigs_offparking" id="visdigs_offparking" value="<?php echo $visdigs_offparking;?>"/>
      </div>
      
      <div class="field">
            <label for="visdigs_type">Utilities</label>
            <small>Tick available utilities</small>
      </div>
      <div class="checkboxfield">
        <input type="checkbox" value="Yes" id="visdigs_sharedbathroom" name="visdigs_sharedbathroom" <?php if($visdigs_sharedbathroom == 'Yes'){echo"checked";}?>><label for="visdigs_sharedbathroom">Shared Bathroom/Shower Room</label><br />
        <input type="checkbox" value="Yes" id="visdigs_privatebathroom" name="visdigs_privatebathroom" <?php if($visdigs_privatebathroom == 'Yes'){echo"checked";}?>><label for="visdigs_privatebathroom">Private Bathroom/Shower Room</label><br />
        <input type="checkbox" value="Yes" id="visdigs_towels" name="visdigs_towels" <?php if($visdigs_towels == 'Yes'){echo"checked";}?>><label for="visdigs_towels">Towels Provided</label><br />
        <input type="checkbox" value="Yes" id="visdigs_bedding" name="visdigs_bedding" <?php if($visdigs_bedding == 'Yes'){echo"checked";}?>><label for="visdigs_bedding">Bed linen Provided</label><br />
        <input type="checkbox" value="Yes" id="visdigs_kitchen" name="visdigs_kitchen" <?php if($visdigs_kitchen == 'Yes'){echo"checked";}?>><label for="visdigs_kitchen">Use of Kitchen</label><br />
        <input type="checkbox" value="Yes" id="visdigs_laundry" name="visdigs_laundry" <?php if($visdigs_laundry == 'Yes'){echo"checked";}?>><label for="visdigs_laundry">Use of Laundry facilities</label><br />
        <input type="checkbox" value="Yes" id="visdigs_communal" name="visdigs_communal" <?php if($visdigs_communal == 'Yes'){echo"checked";}?>><label for="visdigs_communal">Use of communal areas (e.g. living/dining room)</label><br />
        <input type="checkbox" value="Yes" id="visdigs_garden" name="visdigs_garden" <?php if($visdigs_garden == 'Yes'){echo"checked";}?>><label for="visdigs_garden">Use of Garden</label><br />
        <input type="checkbox" value="Yes" id="visdigs_smokealarms" name="visdigs_smokealarms" <?php if($visdigs_smokealarms == 'Yes'){echo"checked";}?>><label for="visdigs_smokealarms">Smoke Alarms installed</label><br />
        <input type="checkbox" value="Yes" id="visdigs_smoking" name="visdigs_smoking" <?php if($visdigs_smoking == 'Yes'){echo"checked";}?>><label for="visdigs_smoking">Non-Smoking Property</label><br />
        <input type="checkbox" value="Yes" id="visdigs_wifi" name="visdigs_wifi" <?php if($visdigs_wifi == 'Yes'){echo"checked";}?>><label for="visdigs_wifi">Wifi Available</label><br />
        <input type="checkbox" value="Yes" id="visdigs_tv" name="visdigs_tv" <?php if($visdigs_tv == 'Yes'){echo"checked";}?>><label for="visdigs_tv">TV in Room</label><br />
      </div>
      <div class="field imagesfield">
            <label>Images</label>
            <small>Select/Upload 5 Images</small>
      
    <?php 
    //an array with all the images (ba meta key). The same array has to be in custom_postimage_meta_box_save($post_id) as well.
    $meta_keys = array('second_featured_image','third_featured_image','fourth_featured_image','fith_featured_image','sixth_featured_image');

    foreach($meta_keys as $meta_key){
        $image_meta_val=get_post_meta( $post->ID, $meta_key, true);
        ?>
        <div class="visdigs_custom_postimage_wrapper" id="<?php echo $meta_key; ?>_wrapper" style="margin-bottom:20px;">
            <img src="<?php echo ($image_meta_val!=''?wp_get_attachment_image_src( $image_meta_val)[0]:''); ?>" style="width:150px;display: <?php echo ($image_meta_val!=''?'block':'none'); ?>" alt="">
            <?php if ($meta_key == "second_featured_image"){echo"This one is featured on the view all page.<br />";} ?>
            <a class="addimage button" onclick="custom_postimage_add_image('<?php echo $meta_key; ?>');">Select Image</a><br>
            <a class="removeimage" style="color:#a00;cursor:pointer;display: <?php echo ($image_meta_val!=''?'block':'none'); ?>" onclick="custom_postimage_remove_image('<?php echo $meta_key; ?>');">Remove Image</a>
            <div class="clear"></div>
            <input type="hidden" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo $image_meta_val; ?>" />
        </div>
    <?php } ?>
      </div>
    <script>
    function custom_postimage_add_image(key){

        var $wrapper = jQuery('#'+key+'_wrapper');

        custom_postimage_uploader = wp.media.frames.file_frame = wp.media({
            title: '<?php _e('select image','yourdomain'); ?>',
            button: {
                text: '<?php _e('select image','yourdomain'); ?>'
            },
            multiple: false
        });
        custom_postimage_uploader.on('select', function() {

            var attachment = custom_postimage_uploader.state().get('selection').first().toJSON();
            var img_url = attachment['url'];
            var img_id = attachment['id'];
            $wrapper.find('input#'+key).val(img_id);
            $wrapper.find('img').attr('src',img_url);
            $wrapper.find('img').show();
            $wrapper.find('a.removeimage').show();
        });
        custom_postimage_uploader.on('open', function(){
            var selection = custom_postimage_uploader.state().get('selection');
            var selected = $wrapper.find('input#'+key).val();
            if(selected){
                selection.add(wp.media.attachment(selected));
            }
        });
        custom_postimage_uploader.open();
        return false;
    }

    function custom_postimage_remove_image(key){
        var $wrapper = jQuery('#'+key+'_wrapper');
        $wrapper.find('input#'+key).val('');
        $wrapper.find('img').hide();
        $wrapper.find('a.removeimage').hide();
        return false;
    }
    </script>
    <?php
    //after main form elementst hook
    do_action('visdigs_admin_form_end');
    ?>
    </div>
    <?php

}

//triggered when adding or editing a digs
public function save_digs($post_id){

    //check for nonce
    if(!isset($_POST['visdigs_nonce_field'])){
        return $post_id;
    }
    //verify nonce
    if(!wp_verify_nonce($_POST['visdigs_nonce_field'], 'visdigs_nonce')){
        return $post_id;
    }
    //check for autosave
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
        return $post_id;
    }

    //get our fields

    $visdigs_ownername = isset($_POST['visdigs_ownername']) ? sanitize_text_field($_POST['visdigs_ownername']) : '';
    $visdigs_owneremail = isset($_POST['visdigs_owneremail']) ? sanitize_text_field($_POST['visdigs_owneremail']) : '';
    $visdigs_ownerlandline = isset($_POST['visdigs_ownerlandline']) ? sanitize_text_field($_POST['visdigs_ownerlandline']) : '';
    $visdigs_ownermobile = isset($_POST['visdigs_ownermobile']) ? sanitize_text_field($_POST['visdigs_ownermobile']) : '';
    $visdigs_address = isset($_POST['visdigs_address']) ? sanitize_text_field($_POST['visdigs_address']) : '';
    $visdigs_dayrate = isset($_POST['visdigs_dayrate']) ? sanitize_text_field($_POST['visdigs_dayrate']) : '';
    $visdigs_weekrate = isset($_POST['visdigs_weekrate']) ? sanitize_text_field($_POST['visdigs_weekrate']) : '';
    $visdigs_monthrate = isset($_POST['visdigs_monthrate']) ? sanitize_text_field($_POST['visdigs_monthrate']) : '';
    $visdigs_rooms = isset($_POST['visdigs_rooms']) ? sanitize_text_field($_POST['visdigs_rooms']) : '';
    $visdigs_type = isset($_POST['visdigs_type']) ? sanitize_text_field($_POST['visdigs_type']) : '';
    $visdigs_pets = isset($_POST['visdigs_pets']) ? sanitize_text_field($_POST['visdigs_pets']) : '';
    $visdigs_smoking = isset($_POST['visdigs_smoking']) ? sanitize_text_field($_POST['visdigs_smoking']) : '';
    $visdigs_smokealarms = isset($_POST['visdigs_smokealarms']) ? sanitize_text_field($_POST['visdigs_smokealarms']) : '';
    $visdigs_sharedbathroom = isset($_POST['visdigs_sharedbathroom']) ? sanitize_text_field($_POST['visdigs_sharedbathroom']) : '';
    $visdigs_privatebathroom = isset($_POST['visdigs_privatebathroom']) ? sanitize_text_field($_POST['visdigs_privatebathroom']) : '';
    $visdigs_towels = isset($_POST['visdigs_towels']) ? sanitize_text_field($_POST['visdigs_towels']) : '';
    $visdigs_bedding = isset($_POST['visdigs_bedding']) ? sanitize_text_field($_POST['visdigs_bedding']) : '';
    $visdigs_kitchen = isset($_POST['visdigs_kitchen']) ? sanitize_text_field($_POST['visdigs_kitchen']) : '';
    $visdigs_laundry = isset($_POST['visdigs_laundry']) ? sanitize_text_field($_POST['visdigs_laundry']) : '';
    $visdigs_communal = isset($_POST['visdigs_communal']) ? sanitize_text_field($_POST['visdigs_communal']) : '';
    $visdigs_garden = isset($_POST['visdigs_garden']) ? sanitize_text_field($_POST['visdigs_garden']) : '';
    $visdigs_offparking = isset($_POST['visdigs_offparking']) ? sanitize_text_field($_POST['visdigs_offparking']) : '';
    $visdigs_onparking = isset($_POST['visdigs_onparking']) ? sanitize_text_field($_POST['visdigs_onparking']) : '';
    $visdigs_wifi = isset($_POST['visdigs_wifi']) ? sanitize_text_field($_POST['visdigs_wifi']) : '';
    $visdigs_tv = isset($_POST['visdigs_tv']) ? sanitize_text_field($_POST['visdigs_tv']) : '';
    $visdigs_distance = isset($_POST['visdigs_distance']) ? sanitize_text_field($_POST['visdigs_distance']) : '';

    //update phone, memil and address fields
    update_post_meta($post_id, 'visdigs_ownername', $visdigs_ownername);
    update_post_meta($post_id, 'visdigs_owneremail', $visdigs_owneremail);
    update_post_meta($post_id, 'visdigs_ownerlandline', $visdigs_ownerlandline);
    update_post_meta($post_id, 'visdigs_ownermobile', $visdigs_ownermobile);
    update_post_meta($post_id, 'visdigs_address', $visdigs_address);
    update_post_meta($post_id, 'visdigs_dayrate', $visdigs_dayrate);
    update_post_meta($post_id, 'visdigs_weekrate', $visdigs_weekrate);
    update_post_meta($post_id, 'visdigs_monthrate', $visdigs_monthrate);
    update_post_meta($post_id, 'visdigs_rooms', $visdigs_rooms);
    update_post_meta($post_id, 'visdigs_type', $visdigs_type);
    update_post_meta($post_id, 'visdigs_pets', $visdigs_pets);
    update_post_meta($post_id, 'visdigs_smoking', $visdigs_smoking);
    update_post_meta($post_id, 'visdigs_smokealarms', $visdigs_smokealarms);
    update_post_meta($post_id, 'visdigs_sharedbathroom', $visdigs_sharedbathroom);
    update_post_meta($post_id, 'visdigs_privatebathroom', $visdigs_privatebathroom);
    update_post_meta($post_id, 'visdigs_towels', $visdigs_towels);
    update_post_meta($post_id, 'visdigs_bedding', $visdigs_bedding);
    update_post_meta($post_id, 'visdigs_kitchen', $visdigs_kitchen);
    update_post_meta($post_id, 'visdigs_laundry', $visdigs_laundry);
    update_post_meta($post_id, 'visdigs_communal', $visdigs_communal);
    update_post_meta($post_id, 'visdigs_garden', $visdigs_garden);
    update_post_meta($post_id, 'visdigs_offparking', $visdigs_offparking);
    update_post_meta($post_id, 'visdigs_onparking', $visdigs_onparking);
    update_post_meta($post_id, 'visdigs_wifi', $visdigs_wifi);
    update_post_meta($post_id, 'visdigs_tv', $visdigs_tv);
    update_post_meta($post_id, 'visdigs_distance', $visdigs_distance);
    
    //saving our images
    $meta_keys = array('second_featured_image','third_featured_image','fourth_featured_image','fith_featured_image','sixth_featured_image');
        foreach($meta_keys as $meta_key){
            if(isset($_POST[$meta_key]) && intval($_POST[$meta_key])!=''){
                update_post_meta( $post_id, $meta_key, intval($_POST[$meta_key]));
            }else{
                update_post_meta( $post_id, $meta_key, '');
            }
     }

    //location save hook
    //used so you can hook here and save additional post fields added via 'visdigs_meta_data_output_end' or 'visdigs_meta_data_output_end'
    do_action('visdigs_admin_save',$post_id, $_POST);

}

//enqueus scripts and stles on the back end
public function enqueue_admin_scripts_and_styles(){
    wp_enqueue_style('visdigs-admin-styles', plugin_dir_url(__FILE__) . 'css/visdigs-admin-styles.css');
}

//enqueues scripts and styled on the front end
public function enqueue_public_scripts_and_styles(){
    wp_enqueue_style('visdigs-public-styles', plugin_dir_url(__FILE__) . 'css/visdigs-public-styles.css');
}

public function passwordProtectPosts($post_object) {

	//Checks if current post is a specific custom post type
	if ($post_object->post_type!='digs') {
		return;
	}

	$post_object->post_password = get_option('visdigs_password');
}


public function exclude_protected($where) {
// Filter to hide protected posts
	global $wpdb;
	return $where .= " AND {$wpdb->posts}.post_type = 'digs' ";
}
public function exclude_protected_action($query) {
  // Decide where to display this post type
	if( !is_single() && !is_page() && !is_admin() ) {
		add_filter( 'posts_where', array($this,'exclude_protected' ));
	}
}
public function remove_protected_text() {
  if ( post_password_required() ) {
    return __('Password Protected.');
  }else{
    return __('%s');
  }
}

public function visdigs_register_settings() {
   add_option( 'visdigs_password', 'password');
  add_option( 'visdigs_intro', 'intro');
   register_setting( 'visdigs_options_group', 'visdigs_password', 'visdigs_callback' );
  register_setting( 'visdigs_options_group', 'visdigs_intro', 'visdigs_callback' );
}
public function visdigs_register_options_page() {
  add_options_page('VisDigs Settings', 'VisDigs Settings', 'manage_options', 'visdigs', array($this, 'visdigs_options_page'));
}
public function visdigs_options_page()
{
 
?>
  <div>
  <?php screen_icon(); ?>
  <h2>Visdigs Settings</h2>
  <form method="post" action="options.php">
  <?php settings_fields( 'visdigs_options_group' ); ?>
  <h3>Password</h3>
  <p>This is the required password to view digs info on the public facing pages.</p>
  <table>
  <tr valign="top">
  <th scope="row"><label for="visdigs_password">Password</label></th>
  <td><input type="text" id="visdigs_password" name="visdigs_password" value="<?php echo get_option('visdigs_password'); ?>" length="20" /></td>
  </tr>
  <tr valign="top">
  <th scope="row"><label for="visdigs_password">Intro</label></th>
  <td>
    <?php 
   wp_editor( get_option('visdigs_intro'), 'visdigs_intro', array( 
        'textarea_name' => 'visdigs_intro',
        'media_buttons' => false,
    ) );
  ?>
  </tr>
  </table>
  <?php submit_button(); ?>
  </form>
  </div>
<?php
}

public function visdigs_single_template( $template )
{
  if (is_singular('digs')) {
    $dir = plugin_dir_path( __FILE__ );
		$template = $dir . 'visdigs-single-template.php';
	}
	return $template;
}

public function visdigs_archive_template( $template )
{
  if (is_post_type_archive('digs')) {
    $dir = plugin_dir_path( __FILE__ );
		$template = $dir . 'visdigs-archive-template.php';
	}
	return $template;
}
  
function visdigs_query( $query ) {
	
	if( $query->is_main_query() && !$query->is_feed() && !is_admin() && $query->is_post_type_archive( 'digs' ) ) {

		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', 'visdigs_distance' );
		$query->set( 'order', 'ASC' );
		//$query->set( 'posts_per_page', '4' );
	}

}


  


}//end class

global $visdigs;
$visdigs = new visdigs();

?>
