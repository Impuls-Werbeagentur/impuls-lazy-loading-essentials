<?php
/*
Plugin Name: Impuls Lazy Loading Essentials
Description: Kernfunktionen für Lazy Loading für Impuls Themes und Gutenberg Blocks
Version: 1.2
Author: Impuls Werbeagentur
Author URI: https://www.werbeagentur-impuls.de
*/


function impuls_lle_initialize_options(){
	if(function_exists('impuls_framework_get_options_page_options')){
		add_filter('impuls_framework_options_page_options','impuls_lle_add_setting_to_impuls_framework');
	} else {
		add_action('admin_init','impuls_lle_register_settings');
		add_action( 'admin_enqueue_script', 'impuls_lle_include_admin_script' );
		add_action( 'admin_menu', 'impuls_lle_register_options_page');
	}
}

add_action('after_setup_theme','impuls_lle_initialize_options');

function impuls_lle_add_setting_to_impuls_framework($options_array){
	if(!$options_array){
		$options_array = array();
	}
	$options_array['impuls_lle_fallback_image'] = array(
			'name'    	=> 'Platzhalter-Bild für Lazy Loading',
			'description' 	=> 'Stellen Sie hier ein Bild ein, das angezeigt wird, während das eigentliche Bild durch Lazy Loading nachgeladen wird.',
			'id'      	=> 'impuls_lle_fallback_image',
			'type'    	=> 'file',
			'options' 	=> array(
						'url' => false, // Hide the text input for the url
						),
			'query_args' 	=> array(
						'type' => 'image', 
						),
			'preview_size' 	=> 'thumbnail', // Image size to use when previewing in the admin.
	);
	return $options_array;
}

function impuls_lle_register_settings(){
	register_setting( 'impuls_lle_options_group', 'impuls_lle_options' );
	// Add a form section for the Logo
	add_settings_section('impuls_lle_options_group_header', __( 'Lazy Loading Optionen', 'wptuts' ), 'impuls_lle_options_group_header_text', 'impuls_lle');
	// Add Logo uploader
	add_settings_field('impuls_lle_fallback_image_id',  'Platzhalter für Lazy Loading', 'impuls_lle_fallback_image', 'impuls_lle', 'impuls_lle_options_group_header');
}

function impuls_lle_register_options_page(){
	add_options_page('Lazy Loading Essentials', 'Lazy Loading Essentials', 'manage_options', 'impuls_lle_options', 'impuls_lle_options_render_options_page');
}


function impuls_lle_options_render_options_page(){
	?>
        <div class="wrap"> 
            <h2>Impuls Lazy Loading Essentials</h2>
            <!-- If we have any error by submiting the form, they will appear here -->
            <?php settings_errors( 'impuls-lle-settings-errors' ); ?>
            <form id="form-impuls-lle-options" action="options.php" method="post" enctype="multipart/form-data">
                <?php
                    settings_fields('impuls_lle_options_group');
                    do_settings_sections('impuls_lle');
		    submit_button('Speichern');
                ?>
            </form>
        </div>
    <?php
}

function impuls_lle_include_admin_script(){
	wp_enqueue_media();	
	wp_enqueue_script('impuls-lle-admin-script',plugins_url('/js/impuls-lle-admin-page.js',__FILE__),array('jquery'),filemtime(plugin_dir_path(__FILE__).'/js/impuls-lle-admin-page.js'),true);
}

function impuls_lle_options_group_header_text() {
    ?>
        <p>Geben Sie hier das Platzhalterbild ein, das angezeigt wird, während das korrekte Bild geladen wird.</p>
    <?php
}
 
function impuls_lle_fallback_image() {
    $impuls_lle_options = get_option( 'impuls_lle_options' );
	$fb_id = isset($impuls_lle_options['impuls_lle_fallback_image_id']) ? (int)$impuls_lle_options['impuls_lle_fallback_image_id'] : 0;
    ?>
        <input type="hidden" id="logo_id" name="impuls_lle_options[impuls_lle_fallback_image_id]" value="<?php echo $fb_id; ?>" />
        <input id="upload_logo_button" type="button" class="button" value="Bild auswählen oder hochladen" />
		<?php
			if($fb_id){
				$img = wp_get_attachment_image($fb_id,'thumbnail');
				echo $img;
			}
		?>
    <?php
}

function impuls_lle_get_fallback_bg_tags($url="",$size="thumbnail",$classes="",$styles=""){
	$rueckgabe="";
	if($url){
		$styletag = 'style="background-image:';
		$platzhalter_src = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
		if($platzhalter = impuls_lle_get_fallback_image_id()){
			$platzhalter_src = wp_get_attachment_image_src($platzhalter,$size)[0];
		}
		$styletag.='url('.$platzhalter_src.');';
		if($styles){
			$styletag.=$styles;
		}
		$styletag.='"';
		$classtag = 'class="impuls-lle-lazy';
		if($classes){
			$classtag.=' '.$classes;
		}
		$classtag.='"';
		$rueckgabe = 'data-lazysrc="'.$url.'" '.$classtag.' '.$styletag;
	}
	return $rueckgabe;
}

function impuls_lle_get_fallback_image_id(){
	$fallback_id = 0;
	if(function_exists('impuls_framework_get_options_page_options')){
		$all_options = get_option(impuls_get_custom_theme_options_name(),array());
		if(is_array($all_options) && isset($all_options['impuls_lle_fallback_image_id'])){
			$fallback_id = (int)$all_options['impuls_lle_fallback_image_id'];
		}
	} else {
		$option = get_option('impuls_lle_options',0);
		if(is_array($option) && isset($option['impuls_lle_fallback_image_id'])){
			$fallback_id = (int)$option['impuls_lle_fallback_image_id'];
		}
	}
	return $fallback_id;
}

/************************
Lazy Loading für Bilder
*************************/

if(!is_admin()){
	add_filter('wp_get_attachment_link','impuls_lle_make_images_go_lazy_attachment_link',100,3);
	add_filter('post_thumbnail_html','impuls_lle_make_images_go_lazy_thumbnail_html',100,4);
}

function impuls_lle_make_images_go_lazy_attachment_link($imagelink="",$id,$size){
	$platzhalter_src = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
	if($platzhalter = impuls_lle_get_fallback_image_id()){
		$platzhalter_src = wp_get_attachment_image_src($platzhalter,$size)[0];
	}
	return str_replace(array('class="','src="'),array('class="impuls-lle-lazy ','src="'.$platzhalter_src.'" data-lazysrc="'),$imagelink);
}

function impuls_lle_make_images_go_lazy_thumbnail_html($html="", $post_id,$post_thumbnail_id,$size){
	$platzhalter_src = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
	if($platzhalter = impuls_lle_get_fallback_image_id()){
		$platzhalter_src = wp_get_attachment_image_src($platzhalter,$size)[0];
	}
	return str_replace(array('class="','src="'),array('class="impuls-lle-lazy ','src="'.$platzhalter_src.'" data-lazysrc="'),$html);
}

function impuls_lle_get_lazy_gmaps_integration($apikey,$callback,$libraries = ""){
	return '<div class="impuls-lle-lazy-gmap" data-lazygmapsapikey="'.$apikey.'" data-lazygmapscallback="'.$callback.'" data-lazygmapslibraries="'.$libraries.'"></div>';
}

add_action('wp_enqueue_scripts','impuls_lle_enqueue_lazy_loading_scripts');

function impuls_lle_enqueue_lazy_loading_scripts(){
	wp_enqueue_script('intersection-observer-polyfill',plugins_url('js/intersection-observer.min.js',__FILE__),array(),filemtime(plugin_dir_path(__FILE__).'/js/intersection-observer.min.js'),true);
	wp_enqueue_script('impuls-lle-lazy-loader-script',plugins_url('js/impuls-lle-lazy-loader.js',__FILE__),array(),filemtime(plugin_dir_path(__FILE__).'/js/impuls-lle-lazy-loader.js'),true);
}
