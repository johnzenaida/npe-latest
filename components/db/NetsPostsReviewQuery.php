<?php


namespace NetworkPosts\Components\db;


class NetsPostsReviewQuery {

	public static function get_post_avg_rating( $blog_id, \wpdb $wpdb, $id ) {
		$table_prefix = $wpdb->base_prefix;
		if( $blog_id > 1 ){
			$table_prefix .= $blog_id . '_';
		}
		$query = 'SELECT ROUND(AVG(rating),1) AS rating FROM ' . $table_prefix . 'reviews WHERE post_id=' . $id;
		$record = $wpdb->get_results( $query, ARRAY_A );
		return intval( $record[0]['rating'] );
	}
}