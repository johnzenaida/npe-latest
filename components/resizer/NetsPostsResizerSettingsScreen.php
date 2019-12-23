<?php
namespace NetworkPosts\Components\Resizer;
require_once 'NetsPostsThumbnailBlogSettings.php';


use NetworkPosts\Components\NetsPostsTemplateRenderer;

class NetsPostsResizerSettingsScreen{

	public function save_options( array $request ){
		if( isset( $request['submit'] ) ){
			if( !isset( $request['_wpnonce'] ) )
				return;
			if( !wp_verify_nonce( $request['_wpnonce'] ) )
				return;
			if( isset( $request['allowed'] ) ){
				NetsPostsThumbnailBlogSettings::set_allowed_blogs( $request['allowed'] );
			}
			else{
				NetsPostsThumbnailBlogSettings::set_allowed_blogs( [] );
			}
			if( isset( $request['global'] ) ){
				NetsPostsThumbnailBlogSettings::set_globals( $request['global'] );
			}
			else{
				NetsPostsThumbnailBlogSettings::set_globals( [] );
			}
		}
	}

	public static function build_page( $allowed, $globals ){
		$rows = self::get_blog_list( $allowed, $globals );
		$data['rows'] = $rows;
		$data['nonce'] = wp_create_nonce();
		//echo NetsPostsTemplateRenderer::render('/resizer/network_settings.twig', $data);
		include_once NETSPOSTS_VIEW_PATH . '/resizer/network_settings.php';
	}

	public static function get_blog_list($allowed, $globals){
		$blogs = get_sites();
		$rows = array_map( function($item) use ($allowed, $globals) {
			$site = get_blog_details( ['blog_id' => $item->blog_id ]);
			return [
				'id' => $item->blog_id,
				'blogname' => $site->blogname,
				'allowed' => in_array( $item->blog_id, $allowed ) ? 'checked' : '',
				'global'  => in_array( $item->blog_id, $globals ) ? 'checked' : ''
			];
		}, $blogs );
		return $rows;
	}

	public function print_content(){
		$allowed = NetsPostsThumbnailBlogSettings::get_allowed_blogs();
		$globals = NetsPostsThumbnailBlogSettings::get_globals();
		self::build_page( $allowed, $globals );
	}

	public static function add_settings_page(){
		add_submenu_page( 'settings.php', 'Network Posts Thumbnails', 'Network Posts Thumbnails', '', 'netsposts_thumbnails', array( NetsPostsResizerSettingsScreen::class, 'load') );
	}

	public static function load(){
		$settings = new NetsPostsResizerSettingsScreen();
		$settings->save_options($_POST);
		$settings->print_content();
	}
}