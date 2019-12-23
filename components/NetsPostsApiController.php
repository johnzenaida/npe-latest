<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 10.06.2018
 * Time: 20:40
 */

namespace NetworkPosts\Components;

abstract class NetsPostsApiController {

	const ERROR_CODE_LIST = array(
		400 => 'BAD_REQUEST',
		403 => 'ACCESS_RESTRICTED',
		404 => 'NOT_FOUND',
		500 => 'INTERNAL_ERROR'
	);

	protected function send_response( $status_code, $text = null ){
		http_response_code( $status_code );
		if( $text ){
			echo $text;
		}
		$this->_exit();
	}

	protected function send_json( $status_code, $data ){
		if( !defined( 'NETSPOSTS_TEST' ) ) {
			header( 'Response-Type', 'application/json' );
		}
		$this->send_response( $status_code, json_encode( $data ) );
	}

	protected function bad_request(){
		$this->send_response( 400, self::ERROR_CODE_LIST[400] );
	}

	protected function restricted(){
		$this->send_response( 403, self::ERROR_CODE_LIST[403] );
	}

	protected function not_found(){
		$this->send_response( 404, self::ERROR_CODE_LIST[404] );
	}

	protected function internal_exception(){
		$this->send_response( 500, self::ERROR_CODE_LIST[500] );
	}

	protected function _exit(){
		exit();
	}
}