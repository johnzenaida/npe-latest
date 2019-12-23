<?php
############  SETUP  ####################
require_once netsposts_path('components/NetsPostsUtils.php');
require_once netsposts_path( 'components/db/NetsPostsCategoryQuery.php' );
require_once netsposts_path( 'components/db/NetsPostsQuery.php' );
require_once netsposts_path( 'components/db/NetsPostsReviewQuery.php' );
require_once netsposts_path('components/db/NetsPostsDBQuery.php');
require_once netsposts_path( 'components/db/NetsPostsWPMLQuery.php' );
require_once netsposts_path('components/NetsPostsShortcodeContainer.php');
require_once netsposts_path('components/NetsPostsSettings.php');
require_once netsposts_path('components/resizer/NetsPostsResizerSettingsScreen.php');
require_once netsposts_path('components/resizer/NetsPostsThumbnailBlogSettings.php');
require_once netsposts_path('components/resizer/NetsPostsImageResizerFacade.php');

require_once netsposts_path('components/NetsPostsMultisite.php');
require_once netsposts_path('components/NetsPostsSettings.php');
require_once netsposts_path('components/NetsPostsTemplateRenderer.php');
require_once netsposts_path('components/NetsPostsThumbnailManager.php');

use \NetworkPosts\Components\NetsPostsMultisite;
use NetworkPosts\Components\NetsPostsPlugin;
use \NetworkPosts\Components\NetsPostsSettings;
use NetworkPosts\Components\NetsPostsTemplateRenderer;
use NetworkPosts\Components\Resizer;
use NetworkPosts\Components\NetsPostsThumbnailManager;

define( 'DEFAULT_THUMBNAIL_WIDTH', 300 );
define( 'BASE_JS_PATH', plugins_url( '/network-posts-extended/js' ) );
define( 'POST_VIEWS_PATH', plugin_dir_path( __FILE__ ) . 'views/post' );
define( 'NETSPOSTS_VIEW_PATH', plugin_dir_path( __FILE__ ) . 'views' );

//add_action( 'init', 'net_shared_posts_init' );
if( !defined( 'NETSPOSTS_TEST' ) ) {
	add_action('admin_init', 'netsposts_init_thumbnails_resizer');
	add_action( 'admin_init', array( NetsPostsSettings::class, 'register_settings' ) );
	add_action( 'wpmu_new_blog', array( NetsPostsMultisite::class, 'activate_new_blog_plugin' ) );

	NetsPostsTemplateRenderer::init( NETSPOSTS_VIEW_PATH );
	add_action( 'init', array( NetsPostsMultisite::class, 'multisite_deactivate' ) );
	add_action( 'init', array(NetsPostsThumbnailManager::class, 'initialize') );
	add_action( "plugins_loaded", "netsposts_load_translations" );
	add_shortcode( 'netsposts', 'netsposts_shortcode' );
	add_action( 'admin_menu', array( NetsPostsSettings::class, 'add_toolpage' ) );
	add_action( 'admin_enqueue_scripts', 'netsposts_init_settings_page' );
	add_action( 'network_admin_menu', 'netsposts_add_network_settings' );
	add_action( 'update_option', 'netsposts_save_for_blog' );
}

$plugin = plugin_basename( __FILE__ );

add_filter( "plugin_action_links_$plugin", array(
    \NetworkPosts\Components\NetsPostsSettings::class,
    'plugin_settings_link'
) );

function netsposts_add_network_settings() {
    if ( is_super_admin() ) {
        Resizer\NetsPostsResizerSettingsScreen::add_settings_page();
    }
}

function netsposts_load_translations() {
    register_uninstall_hook( __FILE__, 'net_shared_posts_uninstall' );
    if( get_option( 'load_plugin_styles', 1 ) ) {
        add_action( 'wp_enqueue_scripts', 'netposts_add_stylesheet' );
    }
    load_plugin_textdomain( 'netsposts', false, basename( dirname( __FILE__ ) ) . '/language' );
}

function netsposts_init_thumbnails_resizer() {
    global $wpdb;
    $is_resizing_allowed = Resizer\NetsPostsThumbnailBlogSettings::is_allowed_for_blog( get_current_blog_id() );
    $is_global_resizing  = Resizer\NetsPostsThumbnailBlogSettings::is_global( get_current_blog_id() );
    Resizer\NetsPostsImageResizerFacade::getInstance( $is_resizing_allowed, $is_global_resizing );
}

function netposts_add_stylesheet() {
    wp_register_style( 'netsposts_css', plugins_url( '/css/net_posts_extended.css', __FILE__ ) );
    wp_enqueue_style( 'netsposts_css' );

	wp_register_style( 'netsposts_star_css', plugins_url( '/css/fontawesome-stars.css', __FILE__ ) );
	wp_enqueue_style( 'netsposts_star_css' );
}

function netsposts_init_settings_page() {
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'netsposts_page' ) {
        wp_register_style( 'netsposts_admin_css', plugins_url( '/css/settings.css', __FILE__ ) );
        wp_enqueue_style( 'netsposts_admin_css' );
        Resizer\NetsPostsImageResizerFacade::getInstance()->register_scripts();
    }
}

function net_shared_posts_uninstall() {
    remove_shortcode( 'netsposts' );
}

function netsposts_save_for_blog() {
    if ( @$_REQUEST['option_page'] === 'netsposts_page' ) {
        $blog_id = get_current_blog_id();
        if ( isset( $_POST['blog_resizer_options'] ) ) {
            if ( isset( $_POST['allowed'] ) ) {
                Resizer\NetsPostsThumbnailBlogSettings::allow_for_blog( $blog_id );
            } else {
                Resizer\NetsPostsThumbnailBlogSettings::restrict_for_blog( $blog_id );
            }
            if ( isset( $_POST['global'] ) ) {
                Resizer\NetsPostsThumbnailBlogSettings::make_global( $blog_id );
            } else {
                Resizer\NetsPostsThumbnailBlogSettings::delete_from_global( $blog_id );
            }
        }
    }
}

function netsposts_url( $relative_url ){
	return plugins_url( $relative_url, __FILE__ );
}
