<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 05.06.2018
 * Time: 8:25
 */

namespace NetworkPosts\Components\resizer;
require 'NetsPostsSizesSerializer.php';

class NetsPostsThumbnailSizeSettings {

	const OLD_IMAGE_SIZE_OPTIONS = 'netsposts-thumbnail-sizes';
	const OLD_SETTINGS = 'netsposts-settings';
	const IMAGE_SIZES = 'netsposts_sizes';

	protected function __construct() {
	}

	public static function get_old_image_sizes(){
		$sizes = get_option( self::OLD_IMAGE_SIZE_OPTIONS, false );
		if( $sizes ){
			$keys = self::get_old_size_keys();
			if( $keys ){
				return NetsPostsSizesSerializer::unserializeOld( $keys, $sizes );
			}
		}
		return [];
	}

	private static function get_old_size_keys(){
		$key_string = get_option( self::OLD_SETTINGS, false );
		if( $key_string ){
			return json_decode( $key_string );
		}
		return null;
	}

	public static function delete_old_options(){
		delete_option( self::OLD_SETTINGS );
		delete_option( self::OLD_IMAGE_SIZE_OPTIONS );
	}

	public static function has_old_options(){
		return get_option( self::OLD_IMAGE_SIZE_OPTIONS, false ) === true;
	}


	public static function get_blog_image_sizes(){
		$val = get_option( self::IMAGE_SIZES, false );
		if( $val ){
			return NetsPostsSizesSerializer::unserializeValue( $val );
		}
		return [];
	}

	public static function set_blog_image_sizes($sizes){
		$val = NetsPostsSizesSerializer::serializeValue( $sizes );
		update_option( self::IMAGE_SIZES, $val );
	}
}