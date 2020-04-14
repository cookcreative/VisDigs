<?php
defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );
/*
Plugin Name: VisDigs
Plugin URI:  https://cookcreativemedia.com
Description: Creates an interfaces to manage digs.
Version:     1.0.0
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
    add_filter('the_content', array($this,'prepend_digs_meta_to_content')); //gets our meta data and dispayed it before the content
    add_action('the_post', array($this,'passwordProtectPosts')); //password protects the post type
    add_action('pre_get_posts', array($this,'exclude_protected_action'));//hide post type from rest of the website
    add_action('admin_init', array($this,'visdigs_register_settings' ));
    add_action('admin_menu', array($this, 'visdigs_register_options_page'));
    add_filter( 'protected_title_format', array($this, 'remove_protected_text'));

    // Move all "advanced" metaboxes above the default editor
    add_action('edit_form_after_title', function() {
        global $post, $wp_meta_boxes;
        do_meta_boxes(get_current_screen(), 'advanced', $post);
        unset($wp_meta_boxes[get_post_type($post)]['advanced']);
    });

    add_filter('manage_posts_columns', array($this,'visdigs_columns_head'));
    add_action('manage_posts_custom_column', array($this,'visdigs_columns_content'), 10, 2);

    register_activation_hook(__FILE__, array($this,'plugin_activate')); //activate hook
    register_deactivation_hook(__FILE__, array($this,'plugin_deactivate')); //deactivate hook

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
           'supports'          => array('title','thumbnail','editor'),
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
            <input type="name" name="visdigs_ownername" id="visdigs_ownername" value="<?php echo $visdigs_ownername;?>"/>
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
            <input type="address" name="visdigs_address" id="visdigs_address" value="<?php echo $visdigs_address;?>"/>
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
            <small>Total number of rooms</small>
            <input type="number" name="visdigs_rooms" id="visdigs_rooms" value="<?php echo $visdigs_rooms;?>"/>
        </div>
       <div class="field">
            <label for="visdigs_type">Type of digs</label>
            <small>Self Contained/Staying with Owner/B&amp;B/Hotel</small>
            <input type="text" name="visdigs_type" id="visdigs_type" value="<?php echo $visdigs_type;?>"/>
        </div>
    <?php
    //after main form elementst hook
    do_action('visdigs_admin_form_end');
    ?>
    </div>
    <?php

}

//Function to Add meta before content on a page
public function prepend_digs_meta_to_content($content){

    global $post, $post_type;

    //display meta only on our digs (and if its a single location)
    //if($post_type == 'digs' && is_singular('digs')){
    if($post_type == 'digs'){

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

        //display
        $html = '';
      if ( ! post_password_required() ) {
        $html .= '<section class="digs-info">';
        $html .= '<table>';
        //hook for outputting additional meta data (at the start of the form)
        do_action('visdigs_meta_data_output_start',$post->ID);



        $html .= '<tr>';
          $html .= '<td><b>Owner Name</b></td>';
          $html .= '<td>'. $visdigs_ownername .'</td>';
        $html .= '<tr>';

       $html .= '<tr>';
          $html .= '<td><b>Owner Email</b></td>';
          $html .= '<td>'. $visdigs_owneremail .'</td>';
        $html .= '</tr>';

       $html .= '<tr>';
          $html .= '<td><b>Owner Landline</b></td>';
          $html .= '<td>'. $visdigs_ownerlandline .'</td>';
        $html .= '</tr>';

       $html .= '<tr>';
          $html .= '<td><b>Owner Mobile</b></td>';
          $html .= '<td>'. $visdigs_ownermobile .'</td>';
        $html .= '</tr>';

       $html .= '<tr>';
          $html .= '<td><b>Address</b></td';
          $html .= '<td>'. $visdigs_address .'</td>';
        $html .= '</tr>';

       $html .= '<tr>';
          $html .= '<td><b>Dayrate</b></td>';
          $html .= '<td>&pound;'. $visdigs_dayrate .'</td>';
        $html .= '</tr>';

       $html .= '<tr>';
          $html .= '<td><b>Weekrate</b></td>';
          $html .= '<td>&pound;'. $visdigs_weekrate .'</td>';
        $html .= '</tr>';

       $html .= '<tr>';
          $html .= '<td><b>Monthrate</b></td>';
          $html .= '<td>&pound;'. $visdigs_monthrate .'</td>';
        $html .= '</tr>';

       $html .= '<tr>';
          $html .= '<td><b>Number of Rooms</b></td>';
          $html .= '<td>'. $visdigs_rooms .'</td>';
        $html .= '</tr>';

       $html .= '<tr>';
          $html .= '<td><b>Accom Type</b></td>';
          $html .= '<td>'. $visdigs_type .'</td>';
        $html .= '</tr>';

        //hook for outputting additional meta data (at the end of the form)
        do_action('visdigs_meta_data_output_end',$post->ID);


      //close container
      $html .= '</table>';
      $html .= '</section>';
      }//end password protected area.
      $html .= '<br /><div class="my-3"><b>More Details</b></div>';
        $html .= $content;

        return $html;


    }else{
        return $content;
    }

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

    //location save hook
    //used so you can hook here and save additional post fields added via 'visdigs_meta_data_output_end' or 'visdigs_meta_data_output_end'
    do_action('visdigs_admin_save',$post_id, $_POST);

}

//enqueus scripts and stles on the back end
public function enqueue_admin_scripts_and_styles(){
    wp_enqueue_style('visdigs_admin_styles', plugin_dir_url(__FILE__) . 'css/visdigs_admin_styles.css');
}

//enqueues scripts and styled on the front end
public function enqueue_public_scripts_and_styles(){
    wp_enqueue_style('visdigs_public_styles', plugin_dir_url(__FILE__). 'css/visdigs_public_styles.css');
    wp_enqueue_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');

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
   register_setting( 'visdigs_options_group', 'visdigs_password', 'visdigs_callback' );
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
  </table>
  <?php  submit_button(); ?>
  </form>
  </div>
<?php
}



}//end class

global $visdigs;
$visdigs = new visdigs();

?>
