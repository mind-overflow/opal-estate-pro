<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    opalestate
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2019 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Opalestate_Taxonomy_City {

	/**
	 *
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'definition' ] );
		add_action( 'cmb2_admin_init', [ __CLASS__, 'taxonomy_metaboxes' ] );
	}

	/**
	 *
	 */
	public static function definition() {

		$labels = [
			'name'              => esc_html__( 'Cities / Towns', 'opalestate-pro' ),
			'singular_name'     => esc_html__( 'Properties By City', 'opalestate-pro' ),
			'search_items'      => esc_html__( 'Search Cities / Towns', 'opalestate-pro' ),
			'all_items'         => esc_html__( 'All Cities / Town', 'opalestate-pro' ),
			'parent_item'       => esc_html__( 'Parent City', 'opalestate-pro' ),
			'parent_item_colon' => esc_html__( 'Parent City:', 'opalestate-pro' ),
			'edit_item'         => esc_html__( 'Edit City', 'opalestate-pro' ),
			'update_item'       => esc_html__( 'Update City', 'opalestate-pro' ),
			'add_new_item'      => esc_html__( 'Add New City', 'opalestate-pro' ),
			'new_item_name'     => esc_html__( 'New City', 'opalestate-pro' ),
			'menu_name'         => esc_html__( 'Cities / Towns', 'opalestate-pro' ),
		];

		register_taxonomy( 'opalestate_city', 'opalestate_property', [
			'labels'       => apply_filters( 'opalestate_taxomony_city_labels', $labels ),
			'hierarchical' => true,
			'query_var'    => 'city',
			'rewrite'      => [ 'slug' => esc_html__( 'city', 'opalestate-pro' ) ],
			'public'       => true,
			'show_ui'      => true,
		] );
	}

	/**
	 * Hook in and add a metabox to add fields to taxonomy terms
	 */
	public static function taxonomy_metaboxes() {

		$prefix = 'opalestate_city_';
		/**
		 * Metabox to add fields to categories and tags
		 */
		$cmb_term = new_cmb2_box( [
			'id'           => $prefix . 'edit',
			'title'        => esc_html__( 'City Metabox', 'opalestate-pro' ), // Doesn't output for term boxes
			'object_types' => [ 'term' ], // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'   => [ 'opalestate_city' ], // Tells CMB2 which taxonomies should have these fields
		] );

		$cmb_term->add_field( [
			'name'         => esc_html__( 'Image', 'opalestate-pro' ),
			'desc'         => esc_html__( 'City image', 'opalestate-pro' ),
			'id'           => $prefix . 'image',
			'type'         => 'file',
			'preview_size' => 'small',
			'options'      => [
				'url' => false, // Hide the text input for the url
			],
		] );

		////
		$cmb_term->add_field( [
			'name'     => esc_html__( 'Country', 'opalestate-pro' ),
			'desc'     => esc_html__( 'Select one, to add new you create in countries of estate panel', 'opalestate-pro' ),
			'id'       => $prefix . 'location',
			'taxonomy' => 'opalestate_location', //Enter Taxonomy Slug
			'type'     => 'taxonomy_select',
		] );

		///
		$cmb_term->add_field( [
			'name'     => esc_html__( 'State / Province', 'opalestate-pro' ),
			'desc'     => esc_html__( 'Select one, to add new you create in City/Town of estate panel', 'opalestate-pro' ),
			'id'       => $prefix . 'state',
			'taxonomy' => 'opalestate_state', //Enter Taxonomy Slug
			'type'     => 'taxonomy_select',
		] );
	}

	/**
	 * Gets list.
	 *
	 * @param array $args
	 * @return array|int|\WP_Error
	 */
	public static function get_list( $args = [] ) {
		$default = [
			'taxonomy'   => 'opalestate_city',
			'hide_empty' => true,
		];

		if ( $args ) {
			$default = array_merge( $default, $args );
		}

		return get_terms( $default );
	}

	/**
	 * Render dopdown list.
	 *
	 * @param int $selected
	 * @return string
	 */
	public static function dropdown_list( $selected = 0 ) {
		$id   = 'opalestate_city' . rand();
		$args = [
			'show_option_none' => esc_html__( 'Select City', 'opalestate-pro' ),
			'id'               => $id,
			'class'            => 'form-control',
			'name'             => 'city',
			'show_count'       => 0,
			'hierarchical'     => '',
			'selected'         => $selected,
			'value_field'      => 'slug',
			'taxonomy'         => 'opalestate_city',
			'orderby'          => 'name',
			'order'            => 'ASC',
			'echo'             => 0,
		];

		$label = '<label class="opalestate-label opalestate-label--city" for="' . esc_attr( $id ) . '">' . esc_html__( 'City', 'opalestate-pro' ) . '</label>';

		echo $label . wp_dropdown_categories( $args );
	}
}

Opalestate_Taxonomy_City::init();
