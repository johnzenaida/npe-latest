<?php


namespace NetworkPosts\Components\db;


class NetsPostsWPMLQuery {
	const WPML_TRANSLATION_TABLE = 'icl_translations';


	public static function get_translation_ids( \wpdb $db, array $post_ids ): array{
		$translation_table = $db->prefix . self::WPML_TRANSLATION_TABLE;
		$id_str = join( ',', $post_ids );
		$query = "SELECT trid FROM $translation_table 
					WHERE $translation_table.element_id IN ($id_str)";
		$rows = $db->get_results( $query, ARRAY_A );
		$translations = array_map( function( $row ){
			return $row['trid'];
		}, $rows );
		return $translations;
	}
}