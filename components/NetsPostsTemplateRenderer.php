<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 31.05.2018
 * Time: 9:01
 */

namespace NetworkPosts\Components;

use RuntimeException;
//use Twig\Loader\FilesystemLoader;

class NetsPostsTemplateRenderer {

	private static $instance;
	private static $twig = null;
	private static $views_path;

	protected function __construct( $views_path ) {
		self::$views_path = $views_path;
		//$loader = new \Twig_Loader_Filesystem( self::$views_path );
		//self::$twig   = new \Twig_Environment( $loader );
	}

	public static function render( $template_relative_path, $data = [] ) {
		//if ( self::$twig ) {
		$full_path = self::$views_path . $template_relative_path;
		if ( file_exists( $full_path ) ) {
			return self::render_template( $full_path, $data );
		} else {
			return '';
		}
		//} else {
		//throw new RuntimeException( 'Class ' . self::class . ' is not initialized.' );
		//}
	}

	private static function render_template( $template, $data ) {
		$tmpl = '';
		$h    = fopen( $template, 'r+' );
		while ( ( $line = fgets( $h, 4096 ) ) !== false ) {
			foreach ( $data as $key => $value ) {
				$line = str_replace( "%${key}%", $value, $line );
			}
			$tmpl .= $line;
		}
		fclose( $h );
		return $tmpl;
	}

	public static function init( $views_path ) {
		self::$instance = new NetsPostsTemplateRenderer( $views_path );
	}
}