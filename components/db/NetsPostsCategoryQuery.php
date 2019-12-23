<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.03.2019
 * Time: 10:55
 */

namespace NetworkPosts\db;

use WPDB;

class NetsPostsCategoryQuery {

	/**
	 * @var WPDB
	 */
	private $db;

	private $taxonomy = [];

	private $taxonomy_type = [];
	private $must_include_all_categories = false;

	public function __construct( WPDB $db ) {
		$this->db = $db;
	}

	protected function set_taxonomy( array $taxonomy ): void {
		$this->taxonomy = array_map( function ( $category ) {
			return "'" . strtolower( $category ) . "'";
		}, $taxonomy );
	}

	public function set_taxonomy_type( array $taxonomy ) {
		foreach( $taxonomy as $type ) {
			$type = strtolower( $type );
			switch ( $type ) {
				case 'tag':
					$taxonomy_type_value = 'post_tag';
					break;
				case 'category':
					$taxonomy_type_value = 'category';
					break;
				case 'product_tag':
					$taxonomy_type_value = 'product_tag';
					break;
				case 'product_category':
					$taxonomy_type_value = 'product_cat';
					break;
				default:
					$taxonomy_type_value = $type;
			}
			$this->taxonomy_type[] = $taxonomy_type_value;
		}
	}

	public function clear_taxonomy_types(): void {
		$this->taxonomy_type = [];
	}

	protected function build_query(): string {
		$terms_relationship_table = $this->db->term_relationships;
		$terms_taxonomy           = $this->db->term_taxonomy;
		$terms_table              = $this->db->terms;

		$query = "SELECT object_id
				  FROM $terms_table
				  LEFT JOIN $terms_relationship_table ON $terms_relationship_table.term_taxonomy_id = $terms_table.term_id
				  LEFT JOIN $terms_taxonomy ON $terms_taxonomy.term_id = $terms_table.term_id";

		return $query;
	}

	protected function build_where_statement(): string {
		$where = '';
		if( empty( $this->taxonomy ) ){
			if( ! empty( $this->taxonomy_type ) ) {
				$where .= $this->build_taxonomy_type();
			}
		} else {
			if ( ! empty( $this->taxonomy_type ) ) {
				$where .= $this->build_taxonomy_type() . ' AND ';
			}
			if ( $this->must_include_all_categories ) {
				$where .= $this->build_strict_inclusion();
			} else {
				$where .= $this->build_inclusion();
			}
		}
		if( $where ) {
			$where = ' WHERE ' . $where . ' ';
		}
		return $where;
	}

	protected function build_inclusion(): string {
		$query = '(';
		$terms_table = $this->db->terms;
		$parts = array();
		foreach ( $this->taxonomy as $taxonomy ){
			$parts[] = "(LOWER($terms_table.slug) LIKE $taxonomy)";
		}
		$query .= join( ' OR ', $parts ) . ')';
		return $query;
	}

	protected function build_strict_inclusion(): string {
		$sql            = $this->build_inclusion();
		$sql            .= ' ';
		$category_count = count( $this->taxonomy );
		$sql            .= "GROUP BY object_id 
				HAVING COUNT(*) = $category_count;";

		return $sql;
	}

	protected function build_taxonomy_type() {
		$terms_taxonomy = $this->db->term_taxonomy;
		return format_inclusion( $terms_taxonomy, "taxonomy", $this->taxonomy_type );
	}


	public function get_posts( $taxonomy, $include_all_taxonomies = false ): array {
		if( ! empty( $taxonomy ) ) {
			$this->set_taxonomy( $taxonomy );
		}
		$this->must_include_all_categories = $include_all_taxonomies;
		$query  = $this->build_query();
		$query  .= $this->build_where_statement();
		$result = $this->db->get_results( $query, ARRAY_A );
		return array_map( function ( $item ) {
			return $item['object_id'];
		}, $result );
	}
}