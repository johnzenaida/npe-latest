<?php
/**
 * Created by PhpStorm.
 * User: Andrew
 * Date: 05.06.2018
 * Time: 8:41
 */

namespace NetworkPosts\Components\resizer;

require_once plugin_dir_path(NETSPOSTS_MAIN_PLUGIN_FILE) . 'components/NetsPostsApiController.php';
require_once 'NetsPostsThumbnailResizerClient.php';
require_once 'NetsPostsThumbnailSizeSettings.php';
require_once 'NetsPostsThumbnailResizerClient.php';
require_once 'NetsPostsThumbnailsResizer.php';

use NetworkPosts\Components\NetsPostsApiController;

class NetsPostsImageResizerFacade extends NetsPostsApiController {
	protected static $instance;
	protected $blog_sizes = array();
	protected $global_sizes = array();
	protected $resizer_client;
	protected $image_resizer;
	protected $keys = array();
	private $initialized = false;
	private $is_resizing_allowed = false;
	private $is_global_resizing = false;

	protected function init( $is_allowed, $is_global ) {
		$this->image_resizer  = self::create_resizer();
		$this->resizer_client = new NetsPostsThumbnailResizerClient();

		$this->initialized         = true;
		$this->is_resizing_allowed = $is_allowed;
		$this->is_global_resizing  = $is_global;

		$this->register_hooks();
	}

	private static function create_resizer() {
		$inst = new NetsPostsThumbnailsResizer();
		//Migrate previous plugin settings
		$sizes = null;
		if ( NetsPostsThumbnailSizeSettings::has_old_options() ) {
			$sizes = NetsPostsThumbnailSizeSettings::get_old_image_sizes();
			NetsPostsThumbnailSizeSettings::delete_old_options();
		} else {
			$sizes = NetsPostsThumbnailSizeSettings::get_blog_image_sizes();
		}
		if( is_array( $sizes ) ){
			$inst->set_local_sizes( $sizes );
		}
		return $inst;
	}

	public function register_hooks() {
		add_action( 'init', array( $this, 'init_sizes' ) );

		$this->resizer_client->register_hooks();

		add_filter( 'image_size_names_choose', array( $this, 'add_size_name' ) );

		add_action( 'admin_post_netsposts_add_size', array( $this, 'receive_post' ) );

		add_action( 'admin_post_netsposts_remove_size', array( $this, 'delete_size' ) );

		add_action( 'admin_post_generate_images', array( $this, 'generate_images' ) );

		add_action( 'wp_ajax_netsposts_get_sizes', array( $this, 'get_thumbnail_sizes' ) );

		add_action( 'wp_ajax_netsposts_get_size', array( $this, 'get_by_id' ) );

		add_action( 'admin_post_get_dummy_thumbnails', array( $this, 'get_dummy_thumbnails' ) );
	}

	public function is_initialized() {
		return $this->initialized;
	}

	public function register_scripts() {
		if ( $this->is_resizing_allowed ) {
			$this->resizer_client->register_scripts();
		}
	}

	public function get_table_html() {
		if ( $this->is_resizing_allowed ) {
			return $this->resizer_client->get_table_html();
		}

		return '';
	}

	public function get_blog_options_form() {
		return $this->resizer_client->get_blog_options_form_html( $this->is_resizing_allowed, $this->is_global_resizing );
	}

	public function get_dummy_thumbnails() {
		ob_start( null, 0, PHP_OUTPUT_HANDLER_CLEAN );
		for ( $i = 0; $i < 100; $i ++ ) {
			$string = '{file:' . '"file"' . $i . '.png' . ', progress:' . $i . '}';
			echo $string . PHP_EOL . PHP_EOL;
			sleep( 500 );
			ob_flush();
			flush();
		}
	}

	public function init_sizes() {
		if ( $this->is_initialized() ) {
			if ( function_exists( 'add_image_size' ) ) {
				$sizes = $this->image_resizer->get_sizes();
				$this->add_new_sizes( $sizes );
			}
		}
	}

	public function add_size_name( $sizes ) {
		$created = $this->image_resizer->get_sizes();
		foreach ( $created as $size ) {
			$sizes[ $size['data']['alias'] ] = @$size['name'];
		}

		return $sizes;
	}

	public function add_new_sizes( $sizes ) {
		foreach ( $sizes as $size ) {
			$size_data = $size['data'];

			if ( isset( $size_data['height'] ) && is_numeric( $size_data['height'] ) ) {
				$height = $size_data['height'];
			} else {
				$height = 9999;
			}

			if ( $size_data['crop'] == "true" ) {
				add_image_size( $size_data['alias'], $size_data['width'], $height, true, array(
					$size_data['crop_x'],
					$size_data['crop_y']
				) );
			} else {
				add_image_size( $size_data['alias'], $size_data['width'], $height, false );
			}
		}
	}

	protected function is_model_valid( $model ) {
		if ( ! empty( $model ) ) {
			if ( ! isset( $model['name'] ) ) {
				return false;
			}
			if ( ! is_numeric( @$model['width'] ) ) {
				return false;
			}

			return true;
		} else {
			return false;
		}
	}

	public function receive_post() {
		if ( $this->is_resizing_allowed ) {
			if ( $this->is_model_valid( $_POST ) ) {
				if ( @$_POST['id'] ) {
					$size = $this->image_resizer->find_by_id( $_POST['id'] );

					if ( ! $size ) {
						$this->not_found();
					}
				} else {
					$size = array();
				}

				if ( $_POST['crop'] === 'true' ) {
					$size['crop_x'] = @$_POST['crop_x'];
					$size['crop_y'] = @$_POST['crop_y'];
				} else {
					if ( ! empty( $size ) ) {
						unset( $_POST['crop_x'] );
						unset( $_POST['crop_y'] );
					}
				}
				$size['crop'] = $_POST['crop'] === 'true';

				if ( ! isset( $size['data'] ) ) {
					$size['data'] = array();
				}

				if ( isset( $_POST['alias'] ) ) {
					$size['data']['alias'] = $_POST['alias'];
				}
				$size['name']           = $_POST['name'];
				$size['data']['width']  = $_POST['width'];
				$size['data']['height'] = @$_POST['height'];

				if ( isset( $size['id'] ) ) {
					$result = $this->image_resizer->update_size( $size['id'], $size );
				} else {
					$result = $this->image_resizer->create_size( $size['name'], $size );
				}

				if ( $result ) {
					$this->send_json( 200, $result );
				} else {
					$this->internal_exception();
				}
			} else {
				$this->bad_request();
			}
		} else {
			$this->restricted();
		}
	}

	public function delete_size() {
		if ( isset( $_GET['id'] ) ) {
			$size = $this->image_resizer->find_by_id( $_GET['id'] );

			if ( $size ) {
				$this->image_resizer->delete( $size['id'], isset( $size['global'] ) );
				$this->send_response( 200 );
			} else {
				$this->not_found();
			}
		} else {
			$this->bad_request();
		}
	}

	public function get_thumbnail_sizes() {
		$this->send_json( 200, $this->image_resizer->get_sizes() );
	}

	public function get_by_id() {
		if ( isset( $_GET['id'] ) ) {
			$item = $this->image_resizer->find_by_id( $_GET['id'] );
			if ( $item ) {
				$this->send_json( 200, $item );
			} else {
				$this->not_found();
			}
		} else {
			$this->bad_request();
		}
	}

	public function generate_images() {
		if ( isset( $_POST['sizes'] ) ) {
			$size = $this->image_resizer->find_by_id( $_POST['sizes'] );

			if ( $size ) {
				if ( @$_POST['select_images'] == 'selected_number' && is_numeric( @$_POST['images_count'] ) ) {
					$attachments = $this->get_attachments( (int) $_POST['images_count'] );
				} else {
					$attachments = $this->get_attachments();
				}
				if ( $attachments ) {
					$count = 1;
					foreach ( $attachments as $blog_id => $blog_attachments ) {
						if ( $blog_id === 'count' ) {
							continue;
						}
						if ( count( $blog_attachments ) > 0 ) {
							switch_to_blog( $blog_id );
                            $upload_dir = wp_upload_dir();

							foreach ( $blog_attachments as $attachment ) {
								$image = wp_get_attachment_metadata( $attachment );
                                $file = $image['file'];
                                if(!realpath($file)){
                                    $file = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $file;
                                }
								if ( $image && file_exists($file) ) {
									$result = $this->image_resizer->generate_image( $size['id'], $file );
									if ( $result ) {
										$image['sizes'][ $size['data']['alias'] ] = $result;
										wp_update_attachment_metadata( $attachment, $image );
									}
									$response = '{"progress":' . $count / $attachments['count'] * 100 . ', "data":"' . $file . '"}';
									if( isset( $_POST['submit'] ) ){
										$this->ob_ignore( $response, true );
									}
									else{
										echo $response;
									}
									$count++;
									usleep( 1000 );
								}
							}
							restore_current_blog();
						}
					}
					$this->send_response( 200 );
				}
			}
		} else {
			$this->bad_request();
		}
	}

	protected function get_attachments( $limit = 0 ) {
		$attachments = array();

		$count = 0;

		if ( $this->is_global_resizing ) {
			$blogs = get_sites( array( 'fields' => 'ids' ) );
		} else {
			$blogs = array( get_current_blog_id() );
		}

		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog );

			$tmp = get_posts(
			    array(
			        'post_type' => 'attachment',
                    'posts_per_page' => -1,
                    'post_status' => 'any',
                    'post_parent' => null,
                    'fields'      => 'ids'
            ) );

            $tmp_count = count( $tmp );

			if ( $limit ) {
				if ( $limit < $tmp_count ) {
					$attachments[ $blog ] = array_slice( $tmp, 0, $limit );
					$limit                = 0;
				} else {
					$attachments[ $blog ] = $tmp;
					$limit                -= $tmp_count;
				}

				if ( $limit <= 0 ) {
					$count += count( $attachments[ $blog ] );
					restore_current_blog();
					break;
				}
			} else {
				$attachments[ $blog ] = $tmp;
			}
			$count += count( $attachments[ $blog ] );

			restore_current_blog();
		}
		if ( $count ) {
			$attachments['count'] = $count;

			return $attachments;
		} else {
			return false;
		}
	}

	private function ob_ignore($data, $flush = false)
	{
		$ob = array();
		while (ob_get_level())
		{
			array_unshift($ob, ob_get_contents());
			ob_end_clean();
		}

		echo $data;
		if ($flush)
			flush();

		foreach ($ob as $ob_data)
		{
			ob_start();
			echo $ob_data;
		}
		return count($ob);
	}

	public static function getInstance( $is_resizing_allowed = false, $is_global = false ) {
		if ( self::$instance ) {
			return self::$instance;
		}

		self::$instance = new NetsPostsImageResizerFacade();
		self::$instance->init( $is_resizing_allowed, $is_global );

		return self::$instance;
	}
}