<?php

/*
Plugin Name: SEO Redirect Edit
Plugin URI: https://gitlab.com/Obroten54/wp-seo-redirect-edit
Description: Plugin for deleting automatic redirects after change slugs (Yoast SEO support)
Version: 1.0
Text Domain: wp-seo-redirect-edit
Author: Obroten54
Author URI: https://proekt-obroten.ru/
License: A "Slug" license name e.g. GPL2
*/

add_action( 'add_meta_boxes', 'seor_add_field' );
function seor_add_field() {
	add_meta_box(
		'seor_redirects',
		'Old slug redirects',
		'seor_block_content',
		'post',
		'normal',
		'default'
	);
}

function seor_block_content($post) {
	wp_nonce_field( plugin_basename( __FILE__ ), 'seor_redirects' );

	$oldSlugs = get_post_meta($post->ID, "_wp_old_slug");
	if($oldSlugs) {
		foreach ($oldSlugs as $oldSlug) {
			echo "<p>".$oldSlug."<span></span></p>";
		}
	}
}

add_action( 'save_post', 'seor_custom_field_save', 40);
function seor_custom_field_save($post_id) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( !wp_verify_nonce( $_POST['seor_redirects'], plugin_basename( __FILE__ ) ) )
		return;

	$yoastExists = class_exists("WPSEO_Redirect_Option");
	$redirectOptions = false;
	if($yoastExists)
		$redirectOptions = new WPSEO_Redirect_Option();

	if(isset($_POST["seor_remove"])) {
		foreach ($_POST["seor_remove"] as $oldSlug) {
			delete_post_meta($post_id, '_wp_old_slug', $oldSlug);
			if($yoastExists) {
				$oldLink = str_replace("%postname%", $oldSlug, str_replace(home_url(), '', get_permalink($post_id, true)));
				$oldLink = trim($oldLink, "/");
				$redirectOptionKey = $redirectOptions->search($oldLink);
				if($redirectOptionKey!==false) {
					$redirectOptions->delete($redirectOptions->get_all()[$redirectOptionKey]);
				}
			}
		}
	}

	if($yoastExists) {
		$redirectOptions->save();
	}
}

function seor_resources() {
	wp_register_style('seor_resources', plugins_url('style.css',__FILE__ ));
	wp_enqueue_style('seor_resources');
	wp_register_script( 'seor_resources', plugins_url('script.js',__FILE__ ));
	wp_enqueue_script('seor_resources', ["jquery"]);
}

add_action( 'admin_init','seor_resources');