<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 31.05.2018
 * Time: 11:25
 */

namespace NetworkPosts\Components\Resizer;

require_once plugin_dir_path(NETSPOSTS_MAIN_PLUGIN_FILE) . '/components/NetsPostsTemplateRenderer.php';
use NetworkPosts\Components\NetsPostsTemplateRenderer;

class NetsPostsThumbnailResizerClient {
	public function register_hooks() {
		add_action( 'wp_ajax_get_add_size_form', array( $this, 'print_add_size_form' ) );
		add_action( 'wp_ajax_get_image_generator_form', array( $this, 'print_image_generator_form' ) );
	}

	public function register_scripts() {

		wp_enqueue_script( 'netsposts_admin_script', netsposts_url( 'dist/main.js' ), false, '1.0.0', true );

		wp_localize_script( 'netsposts_admin_script', 'data', array(

			'loading_gif' => netsposts_url( '/pictures/3.gif'),

			'base_path'   => BASE_JS_PATH,

			'empty_table' => 'No entries',

			'error_message' => 'Something went wrong.',

			'create_item_title' => 'Create item',

			'modify_item_title' => 'Modify item',

			'notifier_url' => plugins_url( '/components/action-progress.php', dirname( __FILE__ ) )

		) );

	}

	public function print_image_generator_form() {
		echo NetsPostsTemplateRenderer::render( '/resizer/image_resizer_form.html' );
		exit();
	}

	public function print_add_size_form() {
		$template = NetsPostsTemplateRenderer::render( '/resizer/add_size_form.html', array(
			'loader_url' => plugins_url( '/pictures/3.gif', dirname( __FILE__ ) )
		) );
		echo $template;
		exit();
	}

	public function get_table_html() {
		return NetsPostsTemplateRenderer::render( '/resizer/table.html' );
	}

	public function get_blog_options_form_html( $allowed, $is_global ) {
		return NetsPostsTemplateRenderer::render( '/resizer/blog_resizer_options.html', array(
			'allowed' => $allowed ? 'checked' : '',
			'global'  => $is_global ? 'checked' : ''
		) );
	}
}