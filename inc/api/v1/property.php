<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Property_Api
 *
 * @since      1.0.0
 * @package    Property_Api
 */
class Property_Api extends Base_Api {

	/**
	 * The unique identifier of the route resource.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string $base .
	 */
	public $base = '/property';

	/**
	 * Register Routes
	 *
	 * Register all CURD actions with POST/GET/PUT and calling function for each
	 *
	 * @return avoid
	 * @since 1.0
	 *
	 */
	public function register_routes() {
		/**
		 * Get list of properties.
		 *
		 * Call http://domain.com/wp-json/estate-api/v1/property/list
		 */
		register_rest_route( $this->namespace, $this->base . '/list', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_list' ],
			'permission_callback' => [ $this, 'validate_request' ],
		] );

		/**
		 * Get list of featured properties.
		 *
		 * Call http://domain.com/wp-json/estate-api/v1/property/featured
		 */
		register_rest_route( $this->namespace, $this->base . '/featured', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_featured_list' ],
			'permission_callback' => [ $this, 'validate_request' ],
		] );

		/**
		 * Get property detail.
		 *
		 * Call http://domain.com/wp-json/estate-api/v1/property/1
		 */
		register_rest_route( $this->namespace, $this->base . '/(?P<id>\d+)', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_detail' ],
			'permission_callback' => [ $this, 'validate_request' ],
		] );

		/**
		 * Create a property.
		 *
		 * Call http://domain.com/wp-json/estate-api/v1/property/create
		 */
		register_rest_route( $this->namespace, $this->base . '/create', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'create' ],
			'permission_callback' => [ $this, 'validate_request' ],
		] );

		/**
		 * Edit a property.
		 *
		 * Call http://domain.com/wp-json/estate-api/v1/property/edit
		 */
		register_rest_route( $this->namespace, $this->base . '/edit', [
			'methods'  => 'GET',
			'callback' => [ $this, 'edit' ],
		] );

		/**
		 * Delete a property.
		 *
		 * Call http://domain.com/wp-json/estate-api/v1/property/delete
		 */
		register_rest_route( $this->namespace, $this->base . '/delete', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'delete' ],
			'permission_callback' => [ $this, 'validate_request' ],
		] );

		/**
		 * List property tags.
		 *
		 * Call http://domain.com/wp-json/estate-api/v1/property/tags
		 */
		register_rest_route( $this->namespace, $this->base . '/tags', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'delete' ],
			'permission_callback' => [ $this, 'validate_request' ],
		] );
	}

	/**
	 * Get list of featured properties.
	 *
	 * @return array|\WP_REST_Response
	 */
	public function get_featured_list() {
		$properties = [];
		$error      = [];

		$property = null;

		if ( $property == null ) {
			$properties = [];

			$property_list = get_posts( [
				'post_type'        => 'opalestate_property',
				'posts_per_page'   => $this->per_page(),
				'suppress_filters' => true,
				'meta_key'         => OPALESTATE_PROPERTY_PREFIX . 'featured',
				'meta_value'       => 'on',
				'paged'            => $this->get_paged(),
			] );

			if ( $property_list ) {
				$i = 0;
				foreach ( $property_list as $property_info ) {
					$properties[ $i ] = $this->get_property_data( $property_info );
					$i++;
				}
			}
		} else {
			if ( get_post_type( $property ) == 'opalestate_property' ) {
				$property_info = get_post( $property );

				$properties[0] = $this->get_property_data( $property_info );

			} else {
				$error['error'] = sprintf(
				/* translators: %s: property */
					esc_html__( 'Form %s not found!', 'opalestate-pro' ),
					$property
				);

				return $error;
			}
		}

		$response['collection'] = $properties;
		$response['pages']      = 4;
		$response['current']    = 1;

		return $this->get_response( 200, $response );
	}

	/**
	 * Get List Of Properties
	 *
	 * Based on request to get collection
	 *
	 * @return WP_REST_Response is json data
	 * @since 1.0
	 *
	 */
	public function get_list( $request ) {
		$properties = [];
		$error      = [];
		$property   = null;

		if ( $property == null ) {
			$properties = [];

			$property_list = get_posts( [
				'post_type'        => 'opalestate_property',
				'posts_per_page'   => $this->per_page(),
				'suppress_filters' => true,
				'paged'            => $this->get_paged(),
			] );

			if ( $property_list ) {
				$i = 0;
				foreach ( $property_list as $property_info ) {
					$properties[ $i ] = $this->get_property_data( $property_info );
					$i++;
				}
			}
		} else {
			if ( get_post_type( $property ) == 'opalestate_property' ) {
				$property_info = get_post( $property );

				$properties[0] = $this->get_property_data( $property_info );

			} else {
				$error['error'] = sprintf(
				/* translators: %s: property */
					esc_html__( 'Form %s not found!', 'opalestate-pro' ),
					$property
				);

				return $this->get_response( 404, $error );
			}
		}

		$response['collection'] = $properties;
		$response['pages']      = 4;
		$response['current']    = 1;

		return $this->get_response( 200, $response );
	}

	/**
	 * Get Property
	 *
	 * Based on request to get a property.
	 *
	 * @return WP_REST_Response is json data
	 * @since 1.0
	 *
	 */
	public function get_detail( $request ) {
		$response = [];
		if ( $request['id'] > 0 ) {
			$post = get_post( $request['id'] );
			if ( $post && 'opalestate_property' == get_post_type( $request['id'] ) ) {
				$property             = $this->get_property_data( $post );
				$response['property'] = $property ? $property : [];
				$code                 = 200;
			} else {
				$code              = 404;
				$response['error'] = sprintf( esc_html__( 'Property ID: %s does not exist!', 'opalestate-pro' ), $request['id'] );
			}
		} else {
			$code              = 404;
			$response['error'] = sprintf( esc_html__( 'Invalid ID.', 'opalestate-pro' ), $request['id'] );
		}

		return $this->get_response( $code, $response );
	}

	/**
	 * The opalestate_property post object, generate the data for the API output
	 *
	 * @param object $property_info The Download Post Object
	 *
	 * @return array                Array of post data to return back in the API
	 * @since  1.0
	 *
	 */
	private function get_property_data( $property_info ) {
		$property = [];

		$property['info']['id']            = $property_info->ID;
		$property['info']['slug']          = $property_info->post_name;
		$property['info']['title']         = $property_info->post_title;
		$property['info']['create_date']   = $property_info->post_date;
		$property['info']['modified_date'] = $property_info->post_modified;
		$property['info']['status']        = $property_info->post_status;
		$property['info']['link']          = html_entity_decode( $property_info->guid );
		$property['info']['content']       = $property_info->post_content;
		$property['info']['thumbnail']     = wp_get_attachment_url( get_post_thumbnail_id( $property_info->ID ) );

		$data                        = opalesetate_property( $property_info->ID );
		$gallery                     = $data->get_gallery();
		$property['info']['gallery'] = isset( $gallery[0] ) && ! empty( $gallery[0] ) ? $gallery[0] : [];
		$property['info']['price']   = opalestate_price_format( $data->get_price() );
		$property['info']['map']     = $data->get_map();
		$property['info']['address'] = $data->get_address();
		$property['meta']            = $data->get_meta_shortinfo();
		$property['is_featured']     = $data->is_featured();
		$property['status']          = $data->get_status();
		$property['labels']          = $data->get_labels();
		$property['locations']       = $data->get_locations();
		$property['amenities']       = $data->get_amenities();
		$property['types']           = $data->get_types_tax();
		$property['author_type']     = $data->get_author_type();
		$property['author_data']     = $data->get_author_link_data();

		return apply_filters( 'opalestate_api_properties_property', $property );
	}

	/**
	 * Delete job
	 *
	 * Based on request to get collection
	 *
	 * @return WP_REST_Response is json data
	 * @since 1.0
	 *
	 */
	public function delete() {

	}


	public function reviews() {

	}

	public function categories() {

	}

	public function tags() {

	}
}
