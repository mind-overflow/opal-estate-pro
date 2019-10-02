<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    $package$
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2019 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class   OpalMembership_Checkout
 *
 * @version 1.0
 */
class Opalestate_Emails {


	/**
	 * init action to automatic send email when user edit or submit a new submission and init setting form in plugin setting of admin
	 */
	public static function init() {

		self::load();

		add_action( 'opalestate_processed_new_submission', [ __CLASS__, 'new_submission_email' ], 10, 2 );
		//add_action(  'opalestate_processed_edit_submission' , array( __CLASS__ , 'new_submission_email'), 10, 2 );
		if ( is_admin() ) {
			add_filter( 'opalestate_settings_tabs', [ __CLASS__, 'setting_email_tab' ], 1 );
			add_filter( 'opalestate_registered_emails_settings', [ __CLASS__, 'setting_email_fields' ], 10, 1 );
		}

		$enable_approve_property_email = opalestate_get_option( 'enable_approve_property_email' );

		if ( $enable_approve_property_email == 'on' ) {
			add_action( 'transition_post_status', [ __CLASS__, 'send_email_when_publish_property' ], 10, 3 );
			add_action( 'opalestate_processed_approve_publish_property', [ __CLASS__, 'approve_publish_property_email' ], 10, 1 );
		}

		/**
		 * Send email when User contact via Enquiry Form and Contact Form
		 */
		add_action( 'opalestate_send_email_notifycation', [ __CLASS__, 'send_notifycation' ] );
		add_action( 'opalestate_send_email_submitted', [ __CLASS__, 'new_submission_email' ] );
		add_action( 'opalestate_send_email_request_reviewing', [ __CLASS__, 'send_email_request_reviewing' ] );
	}

	/**
	 *
	 */
	public static function load() {

		require_once OPALESTATE_PLUGIN_DIR . 'inc/email/class-opalestate-abs-email-template.php';
		require_once OPALESTATE_PLUGIN_DIR . 'inc/email/class-opalestate-email-notifycation.php';
		require_once OPALESTATE_PLUGIN_DIR . 'inc/email/class-opalestate-request-viewing.php';
		require_once OPALESTATE_PLUGIN_DIR . 'inc/email/class-opalestate-new-submitted.php';
		require_once OPALESTATE_PLUGIN_DIR . 'inc/email/class-opalestate-approve.php';
	}

	/**
	 * Send Email Notifycation with two types: Enquiry or Contact
	 */
	public static function send_notifycation( $content ) {
		$mail = new OpalEstate_Send_Email_Notification();
		$mail->set_args( $content );

		$return = self::send_mail_now( $mail );

		if ( isset( $content['data'] ) ) {
			$return['data'] = $content['data'];
		}

		echo json_encode( $return );
		die();
	}


	/**
	 * send email if agent submit a new property
	 */
	public static function new_submission_email( $user_id, $post_id ) {
		$mail = new OpalEstate_Send_Email_New_Submitted();
		$mail->set_pros( $post_id, $user_id );
		$return = self::send_mail_now( $mail );

		echo json_encode( $return );
		die();

	}

	/**
	 * Send email to requet viewing a property
	 */
	public static function send_email_request_reviewing( $content ) {
		$mail = new OpalEstate_Send_Email_Request_Reviewing();
		$mail->set_args( $content );

		$return = self::send_mail_now( $mail );

		echo json_encode( $return );
		die();
	}

	/**
	 *
	 */
	public static function send_mail_now( $mail ) {
		$from_name  = $mail->from_name();
		$from_email = $mail->from_email();
		$headers    = sprintf( "From: %s <%s>\r\n Content-type: text/html", $from_name, $from_email );

		$subject = $mail->get_subject();
		$message = $mail->get_body();

		if ( $mail->to_email() ) {

			if ( $mail->get_cc() ) {
				$status = @wp_mail( $mail->get_cc(), $subject, $message, $headers );
			}

			$status = @wp_mail( $mail->to_email(), $subject, $message, $headers );

			if ( $status ) {
				return [ 'status' => true, 'msg' => esc_html__( 'Message has been successfully sent.', 'opalestate-pro' ) ];
			} else {
				return [ 'status' => true, 'msg' => esc_html__( 'Unable to send a message.', 'opalestate-pro' ) ];
			}
		}

		return [ 'status' => true, 'msg' => esc_html__( 'Missing some information!', 'opalestate-pro' ) ];
	}

	/**
	 *
	 */
	public static function send_email_when_publish_property( $new_status, $old_status, $post ) {

		if ( is_object( $post ) ) {
			if ( $post->post_type == 'opalestate_property' ) {
				if ( $new_status != $old_status ) {
					if ( $new_status == 'publish' ) {
						if ( $old_status == 'draft' || $old_status == 'pending' ) {
							// Send email
							$post_id = $post->ID;
							do_action( "opalestate_processed_approve_publish_property", $post_id );
						}
					}
				}
			}
		}

	}

	/**
	 * add new tab Email in opalestate -> setting
	 */
	public static function setting_email_tab( $tabs ) {

		$tabs['emails'] = esc_html__( 'Email', 'opalestate-pro' );

		return $tabs;
	}

	public static function newproperty_email_body() {

	}

	public static function approve_email_body() {

	}

	/**
	 * render setting email fields with default values
	 */
	public static function setting_email_fields( $fields ) {

		$contact_list_tags = '<div>
				<p class="tags-description">Use the following tags to automatically add property information to the emails. Tags labeled with an asterisk (*) can be used in the email subject as well.</p>
				
				<div class="opalestate-template-tags-box">
					<strong>{receive_name}</strong> Name of the agent who made the property
				</div>

				<div class="opalestate-template-tags-box">
					<strong>{property_link}</strong> Property of the user who made the property
				</div>
	
				<div class="opalestate-template-tags-box">
					<strong>{name}</strong> Name of the user who contact via email form
				</div>

				<div class="opalestate-template-tags-box">
					<strong>{email}</strong> Email of the user who contact via email form
				</div>

				<div class="opalestate-template-tags-box">
					<strong>{property_link}</strong> * Link of the property
				</div>
			
				<div class="opalestate-template-tags-box">
					<strong>{message}</strong> * Message content of who sent via form
				</div>

				</div> ';

		$list_tags = '<div>
				<p class="tags-description">Use the following tags to automatically add property information to the emails. Tags labeled with an asterisk (*) can be used in the email subject as well.</p>
				
				<div class="opalestate-template-tags-box">
					<strong>{property_name}</strong> Email of the user who made the property
				</div>

				<div class="opalestate-template-tags-box">
					<strong>{property_link}</strong> Email of the user who made the property
				</div>
	
				<div class="opalestate-template-tags-box">
					<strong>{user_email}</strong> Email of the user who made the property
				</div>

				<div class="opalestate-template-tags-box">
					<strong>{submitted_date}</strong> Email of the user who made the property
				</div>

				<div class="opalestate-template-tags-box">
					<strong>{user_name}</strong> * Name of the user who made the property
				</div>
			
				<div class="opalestate-template-tags-box">
					<strong>{date}</strong> * Date and time of the property
				</div>

				<div class="opalestate-template-tags-box">
					<strong>{site_name}</strong> The name of this website
				</div>
				<div class="opalestate-template-tags-box">
					<strong>{site_link}</strong> A link to this website
				</div>
				<div class="opalestate-template-tags-box">
					<strong>{current_time}</strong> Current date and time
				</div></div>';

		$list_tags = apply_filters( 'opalestate_email_tags', $list_tags );


		$fields = [
			'id'      => 'options_page',
			'title'   => esc_html__( 'Email Settings', 'opalestate-pro' ),
			'show_on' => [ 'key' => 'options-page', 'value' => [ 'opalestate_settings' ], ],
			'fields'  => apply_filters( 'opalestate_settings_emails', [
					[
						'name' => esc_html__( 'Email Settings', 'opalestate-pro' ),
						'desc' => '<hr>',
						'id'   => 'opalestate_title_email_settings_1',
						'type' => 'title',
					],
					[
						'id'      => 'from_name',
						'name'    => esc_html__( 'From Name', 'opalestate-pro' ),
						'desc'    => esc_html__( 'The name donation receipts are said to come from. This should probably be your site or shop name.', 'opalestate-pro' ),
						'default' => get_bloginfo( 'name' ),
						'type'    => 'text',
					],
					[
						'id'      => 'from_email',
						'name'    => esc_html__( 'From Email', 'opalestate-pro' ),
						'desc'    => esc_html__( 'Email to send donation receipts from. This will act as the "from" and "reply-to" address.', 'opalestate-pro' ),
						'default' => get_bloginfo( 'admin_email' ),
						'type'    => 'text',
					],


					[
						'name' => esc_html__( 'Email Submission Templates (Template Tags)', 'opalestate-pro' ),
						'desc' => $list_tags . '<br><hr>',
						'id'   => 'opalestate_title_email_settings_2',
						'type' => 'title',
					],


					//------------------------------------------
			 

					[
						'name' => esc_html__( 'Notification For New Property Submission', 'opalestate-pro' ),
						'desc' => '<hr>',
						'id'   => 'opalestate_title_email_settings_3',
						'type' => 'title',
					],


					[
						'id'         => 'newproperty_email_subject',
						'name'       => esc_html__( 'Email Subject', 'opalestate-pro' ),
						'type'       => 'text',
						'desc'       => esc_html__( 'The email subject for admin notifications.', 'opalestate-pro' ),
						'attributes' => [
							'placeholder' => 'Your package is expired',
							'rows'        => 3,
						],
						'default'    => esc_html__( 'New Property Listing Submitted: {property_name}', 'opalestate-pro' ),

					],
					[
						'id'      => 'newproperty_email_body',
						'name'    => esc_html__( 'Email Body', 'opalestate-pro' ),
						'type'    => 'wysiwyg',
						'desc'    => esc_html__( 'Enter the email an admin should receive when an initial payment request is made.', 'opalestate-pro' ),
						'default' => OpalEstate_Send_Email_New_Submitted::get_default_template(),
					],
					//------------------------------------------
					[
						'name' => esc_html__( 'Approve property for publish', 'opalestate-pro' ),
						'desc' => '<hr>',
						'id'   => 'opalestate_title_email_settings_4',
						'type' => 'title',
					],

					[
						'name'    => esc_html__( 'Enable approve property email', 'opalestate-pro' ),
						'desc'    => esc_html__( 'Enable approve property email.', 'opalestate-pro' ),
						'id'      => 'enable_approve_property_email',
						'type'    => 'switch',
						'options' => [
							'on'  => esc_html__( 'Enable', 'opalestate-pro' ),
							'off' => esc_html__( 'Disable', 'opalestate-pro' ),
						],
						'default' => 'off',
					],

					[
						'id'         => 'approve_email_subject',
						'name'       => esc_html__( 'Email Subject', 'opalestate-pro' ),
						'type'       => 'text',
						'desc'       => esc_html__( 'The email subject a user should receive when they make an initial property request.', 'opalestate-pro' ),
						'attributes' => [
							'placeholder' => 'Your property at I Love WordPress is pending',
							get_bloginfo( 'name' ),
							'rows'        => 3,
						],
						'default'    => esc_html__( 'New Property Listing Submitted: {property_name}', 'opalestate-pro' ),
					],

					[
						'id'      => 'approve_email_body',
						'name'    => esc_html__( 'Email Body', 'opalestate-pro' ),
						'type'    => 'wysiwyg',
						'desc'    => esc_html__( 'Enter the email a user should receive when they make an initial payment request.', 'opalestate-pro' ),
						'default' => OpalEstate_Send_Email_Approve::get_default_template(),
					],
					/// enquire contact template ////
					[
						'name' => esc_html__( 'Email Enquiry Contact Templates (Template Tags)', 'opalestate-pro' ),
						'desc' => $contact_list_tags . '<br><hr>',
						'id'   => 'opalestate_title_email_settings_6_1',
						'type' => 'title',
					],
					[
						'id'         => 'enquiry_email_subject',
						'name'       => esc_html__( 'Email Subject', 'opalestate-pro' ),
						'type'       => 'text',
						'desc'       => esc_html__( 'The email subject a user should receive when they make an initial property request.', 'opalestate-pro' ),
						'attributes' => [
							'placeholder' => 'Your property at I Love WordPress is pending',
							get_bloginfo( 'name' ),
							'rows'        => 3,
						],
						'default'    => esc_html__( 'You got a message', 'opalestate-pro' ),
					],

					[
						'id'   => 'enquiry_email_body',
						'name' => esc_html__( 'Email Body', 'opalestate-pro' ),
						'type' => 'wysiwyg',
						'default' =>  OpalEstate_Send_Email_Notification::get_default_template( 'enquiry' )
					],
					/// email contact template ////
					[
						'name' => esc_html__( 'Email Contact Templates (Template Tags)', 'opalestate-pro' ),
						'desc' => $contact_list_tags . '<br><hr>',
						'id'   => 'opalestate_title_email_settings_6',
						'type' => 'title',
					],
					[
						'id'         => 'contact_email_subject',
						'name'       => esc_html__( 'Email Subject', 'opalestate-pro' ),
						'type'       => 'text',
						'desc'       => esc_html__( 'The email subject a user should receive when they make an initial property request.', 'opalestate-pro' ),
						'attributes' => [
							'placeholder' => 'Your property at I Love WordPress is pending',
							get_bloginfo( 'name' ),
							'rows'        => 3,
						],
						'default'    => esc_html__( 'You got a message', 'opalestate-pro' ),
					],

					[
						'id'   => 'contact_email_body',
						'name' => esc_html__( 'Email Body', 'opalestate-pro' ),
						'type' => 'wysiwyg',
						'default' =>  OpalEstate_Send_Email_Notification::get_default_template()
					],
					/// Email Request Review /// 
					[
						'name' => esc_html__( 'Email Request Review Templates (Template Tags)', 'opalestate-pro' ),
						'desc' => $contact_list_tags . '<br><hr>',
						'id'   => 'opalestate_title_email_settings_7',
						'type' => 'title',
					],
					[
						'id'         => 'request_review_email_subject',
						'name'       => esc_html__( 'Email Subject', 'opalestate-pro' ),
						'type'       => 'text',
						'desc'       => esc_html__( 'The email subject a user should receive when they make an initial property request.', 'opalestate-pro' ),
						'attributes' => [
							'placeholder' => 'Your property at I Love WordPress is pending',
							get_bloginfo( 'name' ),
							'rows'        => 3,
						],
						'default'    =>esc_html__( 'You have a message request reviewing at: %s', 'opalestate-pro' ),
					],

					[
						'id'   => 'request_review_email_body',
						'name' => esc_html__( 'Email Body', 'opalestate-pro' ),
						'type' => 'wysiwyg',
						'default' =>  OpalEstate_Send_Email_Request_Reviewing::get_default_template()
					],
				]
			),
		];

		return $fields;
	}

	/**
	 * get data of newrequest email
	 *
	 * @return text: message
	 * @var $args  array: property_id , $body
	 */
	public static function replace_shortcode( $args, $body ) {

		$tags = [
			'user_name'      => "",
			'user_mail'      => "",
			'submitted_date' => "",
			'property_name'  => "",
			'site_name'      => '',
			'site_link'      => '',
			'property_link'  => '',
		];
		$tags = array_merge( $tags, $args );

		extract( $tags );

		$tags = [
			"{user_mail}",
			"{user_name}",
			"{submitted_date}",
			"{site_name}",
			"{site_link}",
			"{current_time}",
			'{property_name}',
			'{property_link}',
		];

		$values = [
			$user_mail,
			$user_name,
			$submitted_date,
			get_bloginfo( 'name' ),
			get_home_url(),
			date( "F j, Y, g:i a" ),
			$property_name,
			$property_link,
		];

		$message = str_replace( $tags, $values, $body );

		return $message;
	}

	public static function approve_publish_property_email( $post_id ) {

		$mail = new OpalEstate_Send_Email_Approve();
		$mail->set_pros( $post_id );

		$return = self::send_mail_now( $mail );

		echo json_encode( $return );
		die();
	}

}

Opalestate_Emails::init();
