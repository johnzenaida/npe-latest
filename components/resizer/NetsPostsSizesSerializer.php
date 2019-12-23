<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 04.06.2018
 * Time: 15:32
 */

namespace NetworkPosts\Components\resizer;


/*
 * This class is used for old version data serialization
 */
class NetsPostsSizesSerializer {

	public static function serializeValue( $value ) {
		return serialize( $value );
	}

	public static function unserializeValue( $serialized ) {
		return unserialize( $serialized );
	}

	public static function unserializeOld( $keys, $value ){
		return self::translate_names( $keys, $value );
	}

	private static function translate_names( $keys, $values ) {
		$result = array();

		foreach ( $keys as $key => $subkey ) {
			if ( isset( $values[ $subkey ] ) ) {
				$result[ $key ] = $values[ $subkey ];
			}
		}
		return $result;
	}
}