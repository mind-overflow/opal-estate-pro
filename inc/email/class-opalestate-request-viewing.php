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

/**
 * @class   OpalEstate_Send_Email_Notification
 *
 * @version 1.0
 */
class OpalEstate_Send_Email_Request_Reviewing extends OpalEstate_Abstract_Email_Template {
 
	/**
	 * Send Email
	 */
	public function get_subject () {
		$propety_title = '' ;
		return sprintf( esc_html__( 'You have a message request reviewing: %s at', 'opalestate-pro' ),  $propety_title );
	}

	/**
	 * Send Email
	 */
	public function get_content_template() {
 		return opalestate_load_template_path( 'emails/request-reviewing' );
	}	

	/**
	 * Send Email
	 */
	public function to_email () {
		return $this->args ['receiver_email'];
	}

	/**
	 * Send Email
	 */
	public function cc_email () {
		return $this->args ['sender_email'];
	}

	/**
	 * Send Email
	 */
	public function get_body() {
		
		$post = get_post( $this->args['post_id'] ); 
		
		$this->args['email'] = $this->args['receiver_email'];
		$this->args['property_link'] = get_permalink( $post->ID ); 
		$this->args['property_name'] = $post->post_title; 
 

		return parent::get_body();
	}
}
?>