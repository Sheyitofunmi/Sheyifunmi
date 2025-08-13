<?php
/**
 * Plugin Name: Honeycom3 Custom Post Type
 * Plugin URI: http://fatbeehive.com
 * Description: Class for custom post type helpers.
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package Honeycom3
 */

/**
 * Class Honeycom3_Custom_Post_Type
 */
class Honeycom3_Custom_Post_Type {

	/**
	 * Check if a search/filtering is being performed.
	 *
	 * @return bool whether a search is being performed.
	 */
	protected function is_search() {
		if ( isset( $_REQUEST['search'] ) && '1' === $_REQUEST['search'] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the value of a query string if a search is performed.
	 *
	 * @param string $query_string_name the name of the query string.
	 * @return string $value the value of the query string if set and not empty.
	 */
	protected function get_query_string_value( $query_string_name ) {
		if ( $this->is_search() ) {
			if ( isset( $_REQUEST[ $query_string_name ] ) && ! empty( $_REQUEST[ $query_string_name ] ) ) {
				return sanitize_text_field( wp_unslash( $_REQUEST[ $query_string_name ] ) );
			} else {
				return false;
			}
		}
	}

	/**
	 * Get taxonomy term IDs and names for select input.
	 *
	 * @param string $taxonomy taxonomy name.
	 * @return array $select_data the term IDs and names for the taxonomy.
	 */
	protected function get_taxonomy_terms( $taxonomy ) {
		$select_data = array();
		$terms       = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);
		if ( is_array( $terms ) ) {
			foreach ( $terms as $key => $term ) {
				$select_data[ $key ]['term_id'] = $term->term_id;
				$select_data[ $key ]['name']    = $term->name;
			}
		}
		return $terms;
	}
}
