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

class Opalestate_Taxonomy_State {
	/**
	 * Opalestate_Taxonomy_State constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'definition' ] );
		add_filter( 'opalestate_taxomony_state_metaboxes', [ $this, 'metaboxes' ] );
		add_action( 'cmb2_admin_init', [ $this, 'taxonomy_metaboxes' ] );
	}

	/**
	 *
	 */
	public function definition() {
		$labels = [
			'name'              => esc_html__( 'States / Provinces', 'opalestate-pro' ),
			'singular_name'     => esc_html__( 'Properties By State', 'opalestate-pro' ),
			'search_items'      => esc_html__( 'Search States', 'opalestate-pro' ),
			'all_items'         => esc_html__( 'All States / Province', 'opalestate-pro' ),
			'parent_item'       => esc_html__( 'Parent State', 'opalestate-pro' ),
			'parent_item_colon' => esc_html__( 'Parent State:', 'opalestate-pro' ),
			'edit_item'         => esc_html__( 'Edit State', 'opalestate-pro' ),
			'update_item'       => esc_html__( 'Update State', 'opalestate-pro' ),
			'add_new_item'      => esc_html__( 'Add New State', 'opalestate-pro' ),
			'new_item_name'     => esc_html__( 'New State', 'opalestate-pro' ),
			'menu_name'         => esc_html__( 'States / Provinces', 'opalestate-pro' ),
		];

		register_taxonomy( 'opalestate_state', 'opalestate_property', [
			'labels'       => apply_filters( 'opalestate_taxomony_state_labels', $labels ),
			'hierarchical' => true,
			'query_var'    => 'state',
			'rewrite'      => [ 'slug' => esc_html__( 'state', 'opalestate-pro' ) ],
			'public'       => true,
			'show_ui'      => true,
		] );
	}


	public function metaboxes() {

	}

	/**
	 * Hook in and add a metabox to add fields to taxonomy terms
	 */
	public function taxonomy_metaboxes() {
		$prefix = 'opalestate_state_';
		/**
		 * Metabox to add fields to categories and tags
		 */
		$cmb_term = new_cmb2_box( [
			'id'           => $prefix . 'edit',
			'title'        => esc_html__( 'State Metabox', 'opalestate-pro' ), // Doesn't output for term boxes
			'object_types' => [ 'term' ], // Tells CMB2 to use term_meta vs post_meta
			'taxonomies'   => [ 'opalestate_state' ], // Tells CMB2 which taxonomies should have these fields
			// 'new_term_section' => true, // Will display in the "Add New Category" section
		] );

		$cmb_term->add_field( [
			'name'         => esc_html__( 'Image', 'opalestate-pro' ),
			'desc'         => esc_html__( 'State image', 'opalestate-pro' ),
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
			'id'       => $prefix . "location",
			'taxonomy' => 'opalestate_location', //Enter Taxonomy Slug
			'type'     => 'taxonomy_select',
		] );

		///
	}

	public static function get_list() {
		return get_terms( 'opalestate_state', [ 'hide_empty' => false ] );
	}

	public static function dropdown_agents_list( $selected = 0 ) {
		$id   = "opalestate_state" . rand();
		$args = [
			'show_option_none' => esc_html__( 'Select State', 'opalestate-pro' ),
			'id'               => $id,
			'class'            => 'form-control',
			'name'             => 'state',
			'show_count'       => 0,
			'hierarchical'     => '',
			'selected'         => $selected,
			'value_field'      => 'slug',
			'taxonomy'         => 'opalestate_agent_state',
		];

		return wp_dropdown_categories( $args );
	}

	public static function dropdown_list( $selected = 0 ) {
		$id   = "opalestate_state" . rand();
		$args = [
			'show_option_none' => esc_html__( 'Select State', 'opalestate-pro' ),
			'id'               => $id,
			'class'            => 'form-control',
			'name'             => 'state',
			'show_count'       => 0,
			'hierarchical'     => '',
			'selected'         => $selected,
			'value_field'      => 'slug',
			'taxonomy'         => 'opalestate_state',
			'orderby'          => 'name',
			'order'            => 'ASC',
			'echo'             => 0,
		];

		$label = '<label class="opalestate-label opalestate-label--state" for="' . esc_attr( $id ) . '">' . esc_html__( 'State', 'opalestate-pro' ) . '</label>';

		echo $label . wp_dropdown_categories( $args );
	}
}

new Opalestate_Taxonomy_State();
