<?php

namespace NetworkPosts\Components;

class NetsPostsHtmlHelper {
	public static function get_date($date, $format){
		return '<span>' . $date->format($format) . '</span><br/>';
	}

	public static function create_link($url, $label, $open_in_new_tab = '', $class = ''){
		return '<a href="' . $url . '" ' . $open_in_new_tab . ' class="' . $class . '">' . $label . '</a>';
	}

	public static function create_category_link($blog_url, $id, $name, $open_in_new_tab = '', $class = ''){
		$url = $blog_url . '?cat=' . $id;
		return self::create_link($url, $name, $open_in_new_tab, $class);
	}

	public static function create_custom_category_link($blog_url, $slug, $name, $open_in_new_tab = '', $class = ''){
		$url = $blog_url . '/custom_taxonomy/' . $slug;
		return self::create_link($url, $name, $open_in_new_tab, $class);
	}

	public static function create_tag_link($blog_url, $id, $name, $open_in_new_tab = '', $class = ''){
		$url = $blog_url . '?tag=' . $id;
		return self::create_link($url, $name, $open_in_new_tab, $class);
	}

	public static function create_author_link($url, $author_label, $open_in_new_tab = '', $class = ''){
		$link = self::create_link($url, $author_label, $open_in_new_tab, $class);
		return __( 'Author', 'netsposts' ) . ' ' . $link;
	}

	public static function create_span($text, $class = '', $style = ''){
		return '<span class="' . $class . '" style="' . $style . '">' . $text . '</span>';
	}
}