<?php


namespace NetworkPosts\Components\db;

use WPDB;

class NetsPostsQuery {
	/**
	 * @var WPDB
	 */
	protected $db;
	protected $included_posts = array();
	protected $excluded_posts = array();
	protected $limit = 999;
	protected $offset = 0;
	protected $meta_keys = array();
	protected $post_type = array( 'post', 'product' );
	protected $days = false;
	protected $order_by = false;
	protected $sort_type = 'DESC';
	protected $title_keywords = array();
	protected $is_random = false;
	protected $acf_date_filter_field = '';
	protected $acf_date = '';
	protected $before_acf_date = '';
	protected $after_acf_date = '';
	protected $load_only_ids = false;
	protected $posts_without_children = false;

	/**
	 * @param array $included_posts
	 */
	public function include_posts( array $included_posts ): void {
		$this->included_posts = $included_posts;
	}

	public function get_included_posts(): array {
		return $this->included_posts;
	}

	/**
	 * @param array $excluded_posts
	 */
	public function exclude_posts( array $excluded_posts ): void {
		$this->excluded_posts = $excluded_posts;
	}

	public function get_excluded_posts(): array {
		return $this->excluded_posts;
	}

	/**
	 * @param int $limit
	 */
	public function set_limit( int $limit ): void {
		$this->limit = $limit;
	}

	/**
	 * @param int $offset
	 */
	public function set_offset( int $offset ): void {
		$this->offset = $offset;
	}

	/**
	 * @param array $meta_keys
	 */
	public function set_meta_keys( array $meta_keys ): void {
		$this->meta_keys = $meta_keys;
	}

	/**
	 * @param mixed $post_type
	 */
	public function set_post_type( $post_type ): void {
		if ( $post_type == 'any' ) {
			$this->post_type = false;
		} else {
			$this->post_type = $post_type;
		}
	}

	/**
	 * @param string $days
	 */
	public function set_days( string $days ): void {
		$this->days = $days;
	}

	/**
	 * @param string $order_by
	 */
	public function set_order_by( string $order_by ): void {
		$this->order_by = $order_by;
	}

	/**
	 * @param string $type
	 */
	public function set_sort_type( string $type ): void {
		$this->sort_type = $type;
	}

	/**
	 * @param array $title_keywords
	 */
	public function set_title_keywords( array $title_keywords ): void {
		$this->title_keywords = $title_keywords;
	}


	public function set_random(): void {
		$this->is_random = true;
	}

	/**
	 * @param string $acf_date_filter_field
	 */
	public function set_acf_date_filter_field( string $acf_date_filter_field ): void {
		$this->acf_date_filter_field = $acf_date_filter_field;
	}

	/**
	 * @param string $acf_date
	 */
	public function filter_acf_date( string $acf_date ): void {
		$this->acf_date = $this->format_date( $acf_date );
	}

	private function format_date( string $date ): string {
		$date = str_replace( '-', '', $date );
		$date = str_replace( '/', '', $date );

		return $date;
	}

	/**
	 * @param string $before_acf_date
	 */
	public function filter_before_acf_date( string $before_acf_date ): void {
		$this->before_acf_date = $this->format_date( $before_acf_date );
	}

	/**
	 * @param string $after_acf_date
	 */
	public function filter_after_acf_date( string $after_acf_date ): void {
		$this->after_acf_date = $this->format_date( $after_acf_date );
	}

	public function without_children(){
		$this->posts_without_children = true;
	}

	public function get_posts( \WPDB $db ): array {
		$this->set_db( $db );
		$query   = $this->build_query();
		$results = $this->db->get_results( $query, ARRAY_A );
		if ( $this->load_only_ids ) {
			$results = array_map( function ( $record ) {
				return $record['ID'];
			}, $results );
		}
		if( $results ) {
			if ( $this->posts_without_children ) {
				if ( $this->load_only_ids ){
					$parent_ids = $this->get_parents( $results );
					$results = array_filter( $results, function( $id ) use ( $parent_ids ) {
						return ! array_has_value( $id, $parent_ids );
					} );
				} else {
					$ids = array_map( function ( $record ) {
						return $record['ID'];
					}, $results );
					$parent_ids = $this->get_parents( $ids );
					$results = array_filter( $results, function( $post ) use ( $parent_ids ) {
						return ! array_has_value( $post['ID'], $parent_ids );
					} );
				}
				$results = array_values( $results );
			}
		}
		return $results;
	}

	protected function set_db( \WPDB $db ): void {
		$this->db = $db;
	}

	public function get_ids( \WPDB $db ): array{
		$this->load_only_ids = true;
		$this->set_db( $db );
		$query   = $this->build_query();
		$results = $this->db->get_results( $query, ARRAY_A );
		$results = array_map( function ( $record ) {
			return $record['ID'];
		}, $results );
		if($this->posts_without_children) {
			$parent_ids = $this->get_parents( $results );
			$results    = array_filter( $results, function ( $id ) use ( $parent_ids ) {
				return ! array_has_value( $id, $parent_ids );
			} );
		}
		return array_values( $results );
	}

	protected function build_query(): string {
		$PostsTable = $this->db->posts;
		if ( $this->load_only_ids ) {
			$columns = "$PostsTable.ID";
		} else {
			$columns = "$PostsTable.ID, $PostsTable.post_title, $PostsTable.post_excerpt, $PostsTable.post_content, 
				$PostsTable.post_author, $PostsTable.post_date, $PostsTable.post_type";
		}
		$join_tables  = '';
		$include_meta = $this->meta_keys || $this->after_acf_date || $this->before_acf_date || $this->acf_date;
		if ( $include_meta ) {
			$columns     .= ', meta_key, meta_value';
			$MetaTable   = $this->db->postmeta;
			$join_tables = " LEFT JOIN $MetaTable ON ($MetaTable.post_id = $PostsTable.ID)";
		}
		$query = "SELECT $columns FROM ${PostsTable}${join_tables}";
		$where = $this->build_where_condition();
		if ( $where ) {
			$query .= ' ' . $where;
		}
		if ( $this->is_random ) {
			$query .= ' ' . $this->build_random_order();
		} elseif ( $this->order_by ) {
			$query .= ' ' . $this->build_order_by();
		}
		$query .= ' ' . $this->build_limit();

		return $query;
	}

	protected function build_where_condition(): string {
		$condition = array();
		if ( $this->post_type ) {
			$condition[] = $this->build_post_type();
		}
		if ( $this->days ) {
			$condition[] = $this->build_days_filter_query();
		}
		if ( $this->included_posts ) {
			$condition[] = $this->build_posts_inclusion();
		}
		if ( $this->excluded_posts ) {
			$condition[] = $this->build_posts_exclusion();
		}
		if ( $this->meta_keys ) {
			$condition[] = $this->build_meta_query();
		}
		if ( $this->acf_date_filter_field ) {
			if ( $this->acf_date ) {
				$condition[] = $this->build_filter_acf_date();
			}
			if ( $this->before_acf_date ) {
				$condition[] = $this->build_filter_before_acf_date();
			} elseif ( $this->after_acf_date ) {
				$condition[] = $this->build_filter_after_acf_date();
			}
		}

		if ( $this->title_keywords ) {
			$condition[] = $this->build_title_filter();
		}
		$condition[] = $this->build_post_status();
		if ( $condition ) {
			return 'WHERE ' . join( ' AND ', $condition );
		}

		return '';
	}

	protected function build_post_type(): string {
		$posts_table = $this->db->posts;
		$post_types  = array_map( function ( $type ) use ( $posts_table ) {
			return "$posts_table.post_type='" . esc_sql( $type ) . "'";
		}, $this->post_type );

		return '(' . join( ' OR ', $post_types ) . ')';

	}

	protected function build_days_filter_query(): string {
		$posts_table = $this->db->posts;
		$days        = intval( $this->days );

		return "$posts_table.post_date >= DATE_SUB(CURRENT_DATE(), INTERVAL $days DAY)";
	}

	protected function build_posts_inclusion(): string {
		$posts_table = $this->db->posts;

		return "$posts_table.ID IN (" . join( ',', $this->included_posts ) . ')';
	}

	protected function build_posts_exclusion(): string {
		$posts_table = $this->db->posts;

		return "$posts_table.ID NOT IN (" . join( ',', $this->excluded_posts ) . ')';
	}

	protected function build_meta_query(): string {
		$meta_table = $this->db->postmeta;
		$meta_keys  = array_map( function ( $key ) use ( $meta_table ) {
			$key = esc_sql( $key );

			return "$meta_table.meta_key='$key'";
		}, $this->meta_keys );

		return '(' . join( ' OR ', $meta_keys ) . ')';
	}

	protected function build_title_filter(): string {
		$posts_table = $this->db->posts;
		$query_parts = array_map( function ( $keyword ) use ( $posts_table ) {
			$keyword = esc_sql( $keyword );

			return "LOWER($posts_table.post_title) LIKE '%$keyword%'";
		}, $this->title_keywords );

		return '(' . join( ' OR ', $query_parts ) . ')';
	}

	protected function build_filter_acf_date(): string {
		$meta_table = $this->db->postmeta;
		$date       = esc_sql( $this->acf_date );
		$key        = esc_sql( $this->acf_date_filter_field );

		return "($meta_table.meta_key='$key' AND $meta_table.meta_value = $date)";
	}

	protected function build_filter_before_acf_date(): string {
		$meta_table = $this->db->postmeta;
		$date       = esc_sql( $this->before_acf_date );
		$key        = esc_sql( $this->acf_date_filter_field );

		return "($meta_table.meta_key='$key' AND $meta_table.meta_value < $date)";
	}

	protected function build_filter_after_acf_date(): string {
		$meta_table = $this->db->postmeta;
		$date       = esc_sql( $this->after_acf_date );
		$key        = esc_sql( $this->acf_date_filter_field );

		return "($meta_table.meta_key='$key' AND $meta_table.meta_value >= $date)";
	}

	protected function build_random_order(): string {
		return 'ORDER BY RAND()';
	}

	protected function build_order_by(): string {
		return 'ORDER BY ' . $this->db->posts . '.' . $this->order_by . ' ' . $this->sort_type;
	}

	protected function build_limit(): string {
		return 'LIMIT ' . $this->offset . ', ' . $this->limit;
	}

	protected function  build_post_status(): string{
		return 'post_status="publish"';
	}

	/**
	 * @param int[] $post_ids
	 *
	 * @return int[]
	 */
	protected function get_parents( array $post_ids ): array{
		$query = "SELECT post_parent FROM " . $this->db->posts . ' WHERE ';
		if ( $this->post_type ) {
			$query .= $this->build_post_type() . ' AND ';
		}
		$query .= 'post_parent IN (' . join( ',', $post_ids ) . ')';
		$parents = $this->db->get_results( $query, ARRAY_A );
		return array_map( function( $post ){
			return $post['post_parent'];
		}, $parents );
	}
}