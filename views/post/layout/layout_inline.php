<?php/** * Created by PhpStorm. * User: Admin * Date: 03.04.2017 * Time: 15:31 */if(!defined('POST_VIEWS_PATH')) die();require_once plugin_dir_path( NETSPOSTS_MAIN_PLUGIN_FILE ) . 'components/NetsPostsHtmlHelper.php';$align_thumbnail = $shortcode_mgr->get('align_thumbnail');$html .= '<div class="inline-post">';    $html .= "<div class='align-{$align_thumbnail} netsposts-post-thumbnail'>";        if( $shortcode_mgr->get_boolean( 'thumbnail' ) )        {            include POST_VIEWS_PATH . '/thumbnail.php';            $the_post['post_content'] = preg_replace("/<img[^>]+\>/i", "", $the_post['post_content']);        }    $html .= '</div>';    $html .= '<div class="netsposts-text-content">';        include POST_VIEWS_PATH . '/header.php';        if(!$shortcode_mgr->get_boolean('titles_only')) {            include POST_VIEWS_PATH . '/content.php';            if (isset($the_post['price']))                include POST_VIEWS_PATH . '/commerce.php';        }    $html .= '</div>';$html .= '</div>';