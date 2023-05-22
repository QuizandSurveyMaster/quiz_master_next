<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class for ocerride license class methods
 */

class QSM_Check_License
{

	/**
	 * Overrides the active license method.
	 * @since 8.1.7
	 * @param $licensekey 
	 * @param $item_name 
	 */
	public static function activate( $license_key = '', $item_name = '' ) {
		$response = array(
			'status'  => 'error',
			'message' => __( 'Please try again!', 'quiz-master-next' ),
		);
		if ( ! empty( $license_key ) ) {
			$params              = array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => array(
					'edd_action' => 'activate_license',
					'license'    => $license_key,
					'item_name'  => rawurlencode( $item_name ), /* The name of product in EDD. */
					'url'        => home_url(),
				),
			);
			$activation_response = wp_remote_post( 'https://quizandsurveymaster.com', $params );
			if ( ! empty( $activation_response ) ) {
				$body = json_decode( $activation_response['body'] );
				if ( $body->success ) {
					$response = array(
						'status'      => 'success',
						'message'     => __( 'License validated Successfully', 'quiz-master-next' ),
						'expiry_date' => 'lifetime' != $body->expires ? gmdate( "d-m-Y", strtotime( $body->expires ) ) : gmdate( "d-m-Y", strtotime( '+1000 years' ) ),
					);
				} else {
					$error_message   = array(
						'missing'               => __( 'License doesn\'t exist', 'quiz-master-next' ),
						'missing_url'           => __( 'URL not provided', 'quiz-master-next' ),
						'license_not_activable' => __( 'Attempting to activate a bundle\'s parent license', 'quiz-master-next' ),
						'disabled'              => __( 'License key revoked', 'quiz-master-next' ),
						'no_activations_left'   => __( 'No activations left', 'quiz-master-next' ),
						'expired'               => __( 'License has expired', 'quiz-master-next' ),
						'key_mismatch'          => __( 'License is not valid for this product', 'quiz-master-next' ),
						'invalid_item_id'       => __( 'Invalid Item ID', 'quiz-master-next' ),
						'item_name_mismatch'    => __( 'License is not valid for this product', 'quiz-master-next' ),
					);
					$message         = __( 'Please try again!', 'quiz-master-next' );
					if ( ! empty( $body->error ) ) {
						$message = $error_message[ $body->error ];
					}
					$expires = "";
					if ( isset( $body->expires ) ) {
						$expires = gmdate("d-m-Y", strtotime($body->expires));
					}
					$response = array(
						'status'      => 'error',
						'message'     => $message,
						'expiry_date' => $expires,
					);
				}
			}
		}
		return $response;
	}
}
