<?php
/*
Plugin Name: WP Responsive Jquery Slider
Plugin URI: http://www.vivacityinfotech.net
Description: WP Responsive Jquery Slider is world renowned as the most beautiful and easy to use slider on the market.Create dynamic slideshows that adapt to any screen in just few clicks. WP Responsive Jquery Slider one of the best ways to display lots of information in a relatively small space while adding cool functionality to a web page.The jQuery plugin is completely free and totally open source, and there is literally no better way to make your website look totally stunning.
Version: 1.0
Author URI: http://www.vivacityinfotech.net
Requires at least: 3.8
License: vivacityinfotech
*/

/*Copyright 2014  Vivacity InfoTech Pvt. Ltd.  (email : vivacityinfotech.jaipur@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
add_filter('plugin_row_meta', 'RegisterPluginLinks_slider',10, 2);
function RegisterPluginLinks_slider($links, $file) {
	if ( strpos( $file, 'wp-responsive-jquery-slider.php' ) !== false ) {
		$links[] = '<a href="https://wordpress.org/plugins/wp-responsive-jquery-slider/faq/">FAQ</a>';
		$links[] = '<a href="mailto:support@vivacityinfotech.com">Support</a>';
		$links[] = '<a href="http://tinyurl.com/owxtkmt">Donate</a>';
	}
	return $links;
}




// setting in plugin page
function wrjs_settings_page( $links ) {
	$settings_block = '<a href="' . admin_url('edit.php?post_type=vslides&page=settings_section_slider' ) .'">Settings</a>';
	array_unshift( $links, $settings_block);
	return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'wrjs_settings_page' );

// when activate the slider  function call custom post and default settings 
function activate_slider() {

    custom_post_type_slider();

    flush_rewrite_rules();

	defult_setting_slider();
}
register_activation_hook( __FILE__, 'activate_slider' );

// deactivate slider
function deactivate_slider() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'deactivate_slider' );

function uninstall_slider() {
	delete_option( 'get_settings_option' );	
}
register_uninstall_hook( __FILE__, 'uninstall_slider' );

// create custom post type Vslide

function custom_post_type_slider() {
	
	$labels = array(
		'name'                 =>  'Vslides', 
		'singular_name'        => 	'Vslide', 
		'all_items'            => 	'All Vslides', 
		'add_new'              =>	'Add New Vslide', 
		'add_new_item'         => 	'Add New Vslide', 
		'edit_item'            => 	'Edit Vslide',
		'new_item'             => 	'New Vslide', 
		'view_item'            => 	'View Vslide', 
		'search_items'         =>  'Search Vslides',
		'not_found'            => 	'No Slide found', 
		'not_found_in_trash'   => 	'No Slide found in Trash',
		'parent_item_colon'    => ''
	);
	
	$args = array(
		'labels'               => $labels,
		'public'               => true,
		'publicly_queryable'   => true,
		'_builtin'             => false,
		'show_ui'              => true, 
		'query_var'            => true,
		'rewrite'              => array( "slug" => "vslides" ),
		'capability_type'      => 'post',
		'hierarchical'         => false,
		'menu_position'        => 20,
		'supports'             => array( 'title','thumbnail', 'page-attributes' ),
		'taxonomies'           => array(),
		'has_archive'          => true,
		'show_in_nav_menus'    => false
	);
	
	register_post_type( 'vslides', $args );
}
add_action( 'init', 'custom_post_type_slider' );

// linked with css and jquery
function enqueue_style_slider() {
	
	global $post_type;

	
	if ( ( isset( $post_type ) && $post_type == 'vslides' ) || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'vslides' ) ) {
	
		wp_enqueue_style( 'main_wrjs',plugins_url( 'css/main.css' , __FILE__ ) );
	}
	
}

function enqueue_script_slider() {
	wp_enqueue_style( 'style_wrjs',plugins_url( 'css/style.css' , __FILE__ ) );
	wp_enqueue_script( 'slider_script',plugins_url( 'script.js' , __FILE__ ) , array( 'jquery' ), 0.1, true );
	

	$options = get_option( 'get_settings_option' );

	wp_localize_script( 'slider_script', 'wrjs', array(
		'effect'    => $options['effect_wrjs'],
		'delay'     => $options['delay_wrjs'],
		'duration'  => $options['duration_wrjs'],
		'start'     => $options['start_wrjs']		
	) );
}
add_action( 'template_redirect', 'enqueue_style_slider' );

add_action( 'template_redirect', 'enqueue_script_slider' );

// get slide at front from custom post type
function post_slider() {

	$slides = new WP_Query( array( 'post_type' => 'vslides', 'order' => 'ASC', 'orderby' => 'menu_order' ) );

	$slider = '';
	
	if ( $slides->have_posts() ) :
		
		$slider = '<div class="contain_slider wrjs">';
		
			$slider .= '<ul class="slides">';
				
			while ( $slides->have_posts() ) : $slides->the_post();
			
				$slider .= '<li>';
				   
					$slider .= '<div id="slide-' . get_the_ID() . '" class="slide">';
						
						global $post;
						
							if ( has_post_thumbnail() ) {

								if ( get_post_meta( $post->ID, "_slide_link_url", true ) ) 
									$slider .= '<a href="' . get_post_meta( $post->ID, "_slide_link_url", true ) . '" title="' .  the_title_attribute ( array( 'echo' => 0 ) ) . '" >';

									$slider .= get_the_post_thumbnail( $post->ID, 'slide-thumbnail', array( 'class' => 'slide-thumbnail' ) );

								if ( get_post_meta( $post->ID, "_slide_link_url", true ) ) 
									$slider .= '</a>';

							}
						
						$slider .= '<h2 class="slide-title"><a href="' . get_post_meta( $post->ID, "_slide_link_url", true ) . '" title="' . the_title_attribute ( array( 'echo' => 0 ) ) . '" >' . get_the_title() . '</a></h2>';
					
					$slider .= '</div>';
				
				$slider .= '</li>';
				
			endwhile;
			
			$slider .= '</ul>';
			
		$slider .= '</div>';
	
	endif;

	wp_reset_query();

	return $slider;
}
// shortcode for slider [post_slider]
function shortcode_slider() {
	add_shortcode( 'post_slider', 'post_slider' );
}
add_action( 'init', 'shortcode_slider' );

// Width and Height for slider
function dimension_slider() {
	$options = get_option( 'get_settings_option' );
	add_image_size( 'slide-thumbnail', $options['width_wrjs'], $options['height_wrjs'], true );	
}
add_action( 'init', 'dimension_slider' );

// metabox created for link
function metabox_slider() {
    add_meta_box( 'metabox_link_slider', 'Slide Link','metabox_link_slider', 'vslides', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'metabox_slider' );

// get link from metabox            
function metabox_link_slider() {
	
	global $post;	
 
	$slide_link_url = get_post_meta( $post->ID, '_slide_link_url', true ); ?>
	
	<p>URL: <input type="text" style="width: 90%;" name="slide_link_url" value="<?php echo esc_attr( $slide_link_url ); ?>" /></p>
	<span class="description"><?php echo 'The URL link to this slide.'; ?></span>
	
<?php }
// upadte metabox link
function update_metabox_slider( $post_id, $post ) {
	
	if ( isset( $_POST['slide_link_url'] ) ) {
		update_post_meta( $post_id, '_slide_link_url', strip_tags( $_POST['slide_link_url'] ) );
	}	
}
add_action( 'save_post', 'update_metabox_slider', 1, 2 );

function edit_metabox_slider() {
   remove_meta_box( 'postimagediv', 'vslides', 'side' );
	remove_meta_box( 'pageparentdiv', 'vslides', 'side' );
	remove_meta_box( 'hybrid-core-post-template', 'vslides', 'side' );
	remove_meta_box( 'theme-layouts-post-meta-box', 'vslides', 'side' );

    add_meta_box('postimagediv','Slide Image', 'post_thumbnail_meta_box', 'vslides', 'side', 'low');
	 add_meta_box('pageparentdiv','Slide Order', 'page_attributes_meta_box', 'vslides', 'side', 'low');
}
add_action('do_meta_boxes', 'edit_metabox_slider');


function data_slider( $columns ) {

	$columns = array(
		'cb'       => '<input type="checkbox" />',
		'image'    => 'Image', 
		'title'    => 'Title', 
		'order'    => 'Order', 
		'link'     =>  'Link', 
		'date'     => 'Date'
	);

	return $columns;
}
add_filter( 'manage_edit-slides_columns', 'data_slider' );

function add_data_slider( $column ) {

	global $post;

	$edit_link = get_edit_post_link( $post->ID );

	if ( $column == 'image' )		
		echo '<a href="' . $edit_link . '" title="' . $post->post_title . '">' . get_the_post_thumbnail( $post->ID, array( 60, 60 ), array( 'title' => trim( strip_tags(  $post->post_title ) ) ) ) . '</a>';

	if ( $column == 'order' )		
		echo '<a href="' . $edit_link . '">' . $post->menu_order . '</a>';

	if ( $column == 'link' )		
		echo '<a href="' . get_post_meta( $post->ID, "_slide_link_url", true ) . '" target="_blank" >' . get_post_meta( $post->ID, "_slide_link_url", true ) . '</a>';		
}
add_action( 'manage_posts_custom_column', 'add_data_slider' );

// set order for slide
function order_data_slider($wp_query) {
	
	if( is_admin() ) {
		
		$post_type = $wp_query->query['post_type'];
		
		if( $post_type == 'vslides' ) {
			$wp_query->set( 'orderby', 'menu_order' );
			$wp_query->set( 'order', 'ASC' );
		}
	}	
}
add_filter( 'pre_get_posts', 'order_data_slider' );

// settings slider link in plugin option
function settings_slider() {
	add_submenu_page( 'edit.php?post_type=vslides','Slider Settings', 'Settings',  'manage_options', 'settings_section_slider', 'settings_box_slider' );
}
add_action('admin_menu', 'settings_slider');

// wp-admin settings box
function settings_box_slider() { ?>

	<div class="wrap">
		
		<?php screen_icon( 'plugins' ); ?>
		<h2><?php 'Wrjs Slider Options'; ?></h2>
		
		<form method="post" action="options.php">
			<?php settings_fields( 'get_settings_option' ); ?>
			<?php do_settings_sections( 'settings_section_slider' ); ?>
			<br /><p><input type="submit" name="Submit" value="<?php  'Update Settings'; ?>" class="button-primary" /></p>
			<br />
		</form>
		
	</div>
	
<?php }

function settings_start() {

	register_setting( 'get_settings_option', 'get_settings_option', 'check_slider' );

	add_settings_section( 'get_option_change_value', ' ', '', 'settings_section_slider' );

	add_settings_field( 'width_wrjs', 'Slide width:', 'width_wrjs', 'settings_section_slider', 'get_option_change_value' );
	add_settings_field( 'height_wrjs', 'Slide height:', 'height_wrjs', 'settings_section_slider', 'get_option_change_value' );
	add_settings_field( 'delay_wrjs', 'Slide Delay:', 'delay_wrjs', 'settings_section_slider', 'get_option_change_value' );
	add_settings_field( 'duration_wrjs',  'Slide duration:', 'duration_wrjs', 'settings_section_slider', 'get_option_change_value' );
	add_settings_field( 'effect_wrjs','Slide Effect:', 'effect_wrjs', 'settings_section_slider', 'get_option_change_value' );
	add_settings_field( 'start_wrjs', 'Start Automatically:', 'start_wrjs', 'settings_section_slider', 'get_option_change_value' );		
}
add_action( 'admin_init', 'settings_start' );


function width_wrjs() {

	$options = get_option( 'get_settings_option' );
	$width_wrjs = $options['width_wrjs'];
 ?>

	<input type="text" id="width_wrjs" name="get_settings_option[width_wrjs]" value="<?php echo $width_wrjs; ?>" /> <span class="description"><?php  'px'; ?></span>
	
<?php }

function height_wrjs() {

	$options = get_option( 'get_settings_option' );
	$height_wrjs = $options['height_wrjs'];
?>
	<input type="text" id="height_wrjs" name="get_settings_option[height_wrjs]" value="<?php echo $height_wrjs; ?>" /> <span class="description"><?php  'px'; ?></span>
	
<?php }

function delay_wrjs() {

	$options = get_option( 'get_settings_option' );
	$delay_wrjs = $options['delay_wrjs'];
?>
	<input type="text" id="delay_wrjs" name="get_settings_option[delay_wrjs]" value="<?php echo $delay_wrjs; ?>" /> <span class="description"><?php 'milliseconds'; ?></span>
	
<?php }

function duration_wrjs() {

	$options = get_option( 'get_settings_option' );
	$duration_wrjs = $options['duration_wrjs'];
 ?>
	<input type="text" id="duration_wrjs" name="get_settings_option[duration_wrjs]" value="<?php echo $duration_wrjs; ?>" /> <span class="description"><?php 'milliseconds'; ?></span>
	
<?php }

function effect_wrjs() {

	$options = get_option( 'get_settings_option' );
	$effect_wrjs = $options['effect_wrjs'];

	echo "<select id='effect_wrjs' name='get_settings_option[effect_wrjs]'>";
	echo '<option value="fade" ' . selected( $effect_wrjs, 'fade', false ) . ' >' . 'fade'. '</option>';
	echo '<option value="slide" ' . selected( $effect_wrjs, 'slide', false ) . ' >' . 'slide' . '</option>';
	echo '</select>';	
}





function start_wrjs() {

	$options = get_option( 'get_settings_option' );
	$start_wrjs = $options['start_wrjs'];

	echo "<input type='checkbox' id='start_wrjs' name='get_settings_option[start_wrjs]' value='1' " . checked( $start_wrjs, 1, false ) . " />";	
}

function check_slider( $input ) {
	
	$options = get_option( 'get_settings_option' );
	
	$options['width_wrjs'] = wp_filter_nohtml_kses( intval( $input['width_wrjs'] ) );
	$options['height_wrjs'] = wp_filter_nohtml_kses( intval( $input['height_wrjs'] ) );
	$options['effect_wrjs'] = wp_filter_nohtml_kses( $input['effect_wrjs'] );
	$options['delay_wrjs'] = wp_filter_nohtml_kses( intval( $input['delay_wrjs'] ) );
	$options['duration_wrjs'] = wp_filter_nohtml_kses( intval( $input['duration_wrjs'] ) );
	$options['start_wrjs'] = isset( $input['start_wrjs'] ) ? 1 : 0;	
	
	return $options;
}
// default option for slider
function defult_setting_slider() {

	$ex_options = get_option( 'get_settings_option' );

	if ( !is_array( $ex_options ) || $ex_options['duration_wrjs'] == '' ) {

		$default_options = array(	
			'width_wrjs'     => '960',
			'height_wrjs'    => '350',
			'effect_wrjs'    => 'slide',
			'delay_wrjs'     => '5000',
			'duration_wrjs'  => '600',
			'start_wrjs'     => 1		
		);	

		update_option( 'get_settings_option', $default_options );
	}	
}


?>