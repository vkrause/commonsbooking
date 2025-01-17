<?php

namespace CommonsBooking\Messages;

use CommonsBooking\CB\CB;
use CommonsBooking\Settings\Settings;
use WP_Error;
use function commonsbooking_parse_template;

class Messages {

	private $postId;

	private $action;

	private $post;

	public function __construct( $postId, $action ) {
		$this->postId = $postId;
		$this->action = $action;
	}

	public function triggerMail(): void {
		if ( in_array( $this->getAction(), [ "confirmed", "canceled" ] ) ) {
			$this->sendMessage();
		}
	}

	/**
	 * @return mixed
	 */
	public function getAction() {
		return $this->action;
	}

	public function sendMessage() {
		$this->prepareMail();
		$this->SendNotificationMail();
	}

	/**
	 * Setup the email template, headers (BCC)
	 */
	public function prepareMail(): void {
		// Setup email: Recipent
		$booking_user = get_userdata( $this->getPost()->post_author );
		$this->to     = sprintf( '%s <%s>', $booking_user->user_nicename, $booking_user->user_email );

		// WPML: Switch system language to user´s set lang https://wpml.org/documentation/support/sending-emails-with-wpml/
		do_action( 'wpml_switch_language_for_email', $booking_user->user_email );

		// get templates from Admin Options
		$template_body    = Settings::getOption( 'commonsbooking_options_templates',
			'emailtemplates_mail-booking-' . $this->action . '-body' );
		$template_subject = Settings::getOption( 'commonsbooking_options_templates',
			'emailtemplates_mail-booking-' . $this->action . '-subject' );

		// check if templates are available
		if ( ! $template_body or ! $template_subject ) {
			new WP_Error( 'e-mail ', esc_html( __( "Could not send email because mail-template was not available. Check options -> templates", "commonsbooking" ) ) );
		}

		global $post;
		$post = $this->getPost();

		// parse templates & replaces template tags (e.g. {{item:name}})
		$this->body    = commonsbooking_sanitizeHTML( commonsbooking_parse_template( $template_body ) );
		$this->subject = commonsbooking_sanitizeHTML( commonsbooking_parse_template( $template_subject ) );

		// Setup mime type
		$this->headers[] = "MIME-Version: 1.0";
		$this->headers[] = "Content-Type: text/html";

		// Setup email: From
		$this->headers[] = sprintf(
			"From: %s <%s>",
			Settings::getOption( 'commonsbooking_options_templates', 'emailheaders_from-name' ),
			sanitize_email( Settings::getOption( 'commonsbooking_options_templates', 'emailheaders_from-email' ) )
		);

		// TODO: @christian: Add later
		//Check settings for additionitional Recipients
		// $bcc_roles    = CB2_Settings::get( 'bookingemails_bcc-roles' ); /* WP roles that should recieve the email */
		$bcc_adresses = CB::get( 'location', COMMONSBOOKING_METABOX_PREFIX . 'location_email' ); /*  email adresses, comma-seperated  */

		// TODO: @christian: add later - we have to implement user reference in location and item first (cmb2 issue user select)
		// Get users
		// $location_owner_user 	= get_userdata( $this->cb2_object->location->post_author );
		// $item_owner_user 		= get_userdata( $this->cb2_object->item->post_author );

		// if ( is_array( $bcc_roles ) ) {
		// 	if ( in_array ( 'admin-bcc', $bcc_roles ) )  { $this->add_bcc ( get_bloginfo('admin_email') ); }
		// 	if ( in_array ( 'item-owner-bcc', $bcc_roles )) { $this->add_bcc ( $item_owner_user->user_email ); }
		// 	if ( in_array ( 'location-owner-bcc', $bcc_roles )) { $this->add_bcc ( $location_owner_user->user_email ); }
		// }

		if ( ! empty ( $bcc_adresses ) ) {
			$adresses_array = explode( ',', $bcc_adresses );
			foreach ( $adresses_array as $adress ) {
				$this->add_bcc( $adress );
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function getPost() {
		if ( $this->post == null ) {
			$this->post = get_post( $this->getPostId() );
		}

		return $this->post;
	}

	/**
	 * @return mixed
	 */
	public function getPostId() {
		return $this->postId;
	}

	public function add_bcc( $adress ) {
		$this->headers[] = sprintf( "BCC:%s", sanitize_email( $adress ) );
	}

	/**
	 * Send the email
	 */
	public function SendNotificationMail() {

		$to      = apply_filters( 'cb_mail_to', $this->to );
		$subject = apply_filters( 'cb_mail_subject', $this->subject );
		$body    = apply_filters( 'cb_mail_body', $this->body );
		$headers = implode( "\r\n", $this->headers );

		$result = wp_mail( $to, $subject, $body, $headers );

		// WPML: Reset system lang
		do_action( 'wpml_reset_language_after_mailing' );

		do_action( 'commonsbooking_mail_sent', $this->action, $result );

	}

}
