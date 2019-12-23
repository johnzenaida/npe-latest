<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 01.06.2018
 * Time: 10:22
 */

namespace NetworkPosts\Components;
require_once 'resizer/NetsPostsThumbnailBlogSettings.php';
require_once 'resizer/NetsPostsThumbnailResizerClient.php';
require_once 'NetsPostsTemplateRenderer.php';
use NetworkPosts\Components\Resizer\NetsPostsThumbnailBlogSettings;
use NetworkPosts\Components\Resizer\NetsPostsThumbnailsResizer;

class NetsPostsSettings {

###################  TOOL PAGE  #########################

	public static function print_page() {
		$client = new Resizer\NetsPostsThumbnailResizerClient();
		$blog_id = get_current_blog_id();
		$allowed = NetsPostsThumbnailBlogSettings::is_allowed_for_blog( $blog_id );

		$data                             = [];
		if( is_super_admin() ){

			$global = NetsPostsThumbnailBlogSettings::is_global( $blog_id );
			$data['options_form'] = $client->get_blog_options_form_html( $allowed, $global );
		}
		else{
			$data['options_form'] = '';
		}
		if( $allowed ) {
			$data['resizer_table'] = $client->get_table_html();
		}
		else{
			$data['resizer_table'] = '';
		}
		$data['nonce']                    = wp_create_nonce( 'netsposts_page-options' );
		$data['pages']                    = get_option( 'hide_readmore_link_pages' );
		$data['hide_all_readmore_links']  = checked( get_option( 'hide_all_readmore_links' ), 1, false);
		$data['use_single_images_folder'] = checked( get_option( 'use_single_images_folder' ), 1, false);
		$data['use_compressed_images']    = checked(get_option( 'use_compressed_images' ), 1, false);
		$data['load_plugin_styles']        = checked( get_option( 'load_plugin_styles', 1 ), 1, false );

		echo \NetworkPosts\Components\NetsPostsTemplateRenderer::render(  '/settings.html', $data);
	}

	public static function plugin_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=netsposts_page">Settings</a>';

		array_unshift( $links, $settings_link );

		return $links;
	}

	public static function add_toolpage() {
		add_options_page( 'Network Posts Ext', 'Network Posts Ext', 'manage_options', 'netsposts_page', array( self::class, 'print_page') );
	}

	public static function register_settings(){
		add_option_whitelist( [
			'netsposts_page' => [
				'hide_readmore_link_pages', 'hide_all_readmore_links',
				'use_single_images_folder', 'use_compressed_images',
				'load_plugin_styles'
			]]);
		register_setting( 'netsposts_page', 'hide_readmore_link_pages' );
		register_setting( 'netsposts_page', 'hide_all_readmore_links' );
		register_setting( 'netsposts_page', 'use_single_images_folder' );
		register_setting( 'netsposts_page', 'use_compressed_images' );
		register_setting( 'netsposts_page', 'load_plugin_styles' );
	}
}