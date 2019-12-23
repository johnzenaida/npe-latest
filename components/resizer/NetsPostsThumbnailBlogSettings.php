<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 29.05.2018
 * Time: 10:44
 */

namespace NetworkPosts\Components\Resizer;

class NetsPostsThumbnailBlogSettings {

	const ALLOWED_BLOGS_OPTION = 'netsposts_resizer_blogs';
	const GLOBAL_SITES_OPTIONS = 'netsposts_global_blogs_thumbnail_sizes';

	protected function __construct() {
	}

	public static function is_allowed_for_blog($blog_id){
		$blogs = self::get_allowed_blogs();
		return is_array( $blogs ) && in_array( $blog_id, $blogs );
	}

	public static function get_allowed_blogs(){
		$blogs_option = get_site_option( self::ALLOWED_BLOGS_OPTION, null );
		if( !$blogs_option ){
			return [];
		}
		if( is_serialized( $blogs_option ) ) {
			return unserialize( $blogs_option );
		}
		return [];
	}

	public static function set_allowed_blogs( $blogs ){
		if( empty( $blogs ) ){
			delete_site_option( self::ALLOWED_BLOGS_OPTION );
		}
		else{
			update_site_option( self::ALLOWED_BLOGS_OPTION, serialize( $blogs ) );
		}
	}

	public static function restrict_for_blog( $blog_id ){
		$blogs = self::get_allowed_blogs();
		$index = array_search( $blog_id , $blogs);
		if($index !== false) {
			unset( $blogs[$index] );
			self::set_allowed_blogs( array_values( $blogs ) );
		}
	}

	public static function allow_for_blog( $blog_id ){
		$blogs = self::get_allowed_blogs();
		if( !in_array( $blog_id, $blogs ) ){
			$blogs[] = $blog_id;
			self::set_allowed_blogs( $blogs );
		}
	}

	public static function get_globals(){
		$blogs_option = get_site_option( self::GLOBAL_SITES_OPTIONS, null );
		if( !$blogs_option ){
			return [];
		}
		if(is_serialized( $blogs_option )){
			return unserialize( $blogs_option );
		}
		return [];
	}

	public static function is_global( $blog_id ){
		$globals = self::get_globals();
		return is_array( $globals ) && in_array( $blog_id, $globals );
	}

	public static function set_globals( $blogs ){
		if( !empty( $blogs ) ){
			update_site_option( self::GLOBAL_SITES_OPTIONS, serialize( $blogs ) );
		}
		else{
			delete_site_option( self::GLOBAL_SITES_OPTIONS );
		}
	}

	public static function make_global( $blog_id ){
		$blogs = self::get_globals();
		if(!in_array( $blog_id, $blogs )) {
			$blogs[] = $blog_id;
		}
		update_site_option( self::GLOBAL_SITES_OPTIONS, serialize($blogs) );
	}

	public static function delete_from_global( $blog_id ){
		$blogs = self::get_globals();
		$index = array_search( $blog_id , $blogs);
		if($index !== false) {
			unset( $blogs[$index] );
			self::set_globals( array_values( $blogs ) );
		}
	}
}