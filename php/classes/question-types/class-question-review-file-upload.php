<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QSM_Question_Review_File_Upload extends QSM_Question_Review {
	function __construct( $question_id = 0, $question_title_old = '', $answer_array = array() ) {
		parent::__construct( $question_id, $question_title_old, $answer_array );
	}

	public function set_user_answer() {
		if ( isset( $_FILES[ 'qsm_file_question' . $this->question_id ] ) ) {
			global $qsm_global_result_warning_message;
			$do_error = 0;
			$do_error_message = '';
			$user_answer_value = '';
			global $mlwQuizMasterNext;
			$question_id       = $this->question_id;
			$file_upload_type  = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'file_upload_type' );
			$file_upload_limit = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'file_upload_limit' );
			$mimes             = array();
			if ( $file_upload_type ) {
				$file_type_exp = explode( ',', $file_upload_type );
				foreach ( $file_type_exp as $value ) {
					$value = trim( $value );
					if ( 'image' === $value ) {
						$mimes[] = 'image/jpeg';
						$mimes[] = 'image/png';
						$mimes[] = 'image/x-icon';
						$mimes[] = 'image/gif';
						$mimes[] = 'image/webp';
					} elseif ( 'doc' === $value ) {
						$mimes[] = 'application/msword';
						$mimes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
					} elseif ( 'excel' === $value ) {
						$mimes[] = 'application/excel, application/vnd.ms-excel, application/x-excel, application/x-msexcel';
						$mimes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
						$mimes[] = 'text/csv';
					} elseif ( empty( $value ) ) {
						// don't add blank mime type
					} else {
						$mimes[] = $value;
					}
				}
				$mimes = apply_filters( 'qsm_file_upload_mime_type', $mimes );
			}
	
			if ( ! isset( $_FILES[ 'qsm_file_question' . $this->question_id ] ) ) {
				$do_error = 1;
				$do_error_message = __( 'File is not uploaded!', 'quiz-master-next' );
			}
			$uploaded_file = $_FILES[ 'qsm_file_question' . $this->question_id ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$file_name     = isset( $_FILES[ 'qsm_file_question' . $this->question_id ]['name'] ) ? sanitize_file_name( wp_unslash( $uploaded_file['name'] ) ) : '';
			$validate_file = wp_check_filetype( $file_name );
			if ( isset( $validate_file['type'] ) && in_array( $validate_file['type'], $mimes, true ) ) {
				if ( isset( $_FILES[ 'qsm_file_question' . $this->question_id ]['size'] ) && $file_upload_limit > 0 && $_FILES[ 'qsm_file_question' . $this->question_id ]['size'] >= $file_upload_limit * 1024 * 1024 ) {
					$do_error = 1;
					$do_error_message = __( 'File is too large. File must be less than ', 'quiz-master-next' ) . $file_upload_limit . ' MB';
				}
	
				$uploaded_file['name'] = 'qsmfileupload_' . uniqid() . '_' . str_replace( '-', '_', $file_name );
				$upload_overrides      = array(
					'test_form' => false,
				);
				$movefile              = wp_handle_upload( $uploaded_file, $upload_overrides );
				if ( $movefile && ! isset( $movefile['error'] ) ) {
					// Prepare an array of post data for the attachment.
					$attachment = array(
						'guid'           => $movefile['url'],
						'post_mime_type' => $movefile['type'],
						'post_title'     => preg_replace( '/\\.[^.]+$/', '', basename( $uploaded_file['name'] ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					);
					// Insert the attachment.
					$attach_id = wp_insert_attachment( $attachment, $movefile['file'], 0 );
					if ( $attach_id ) {
						include_once ABSPATH . 'wp-admin/includes/image.php';
						$attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
						wp_update_attachment_metadata( $attach_id, $attach_data );
						$user_answer_key                       = 'file_id';
						$user_answer_value                     = $attach_id;
					} else {
						$do_error = 1;
						$do_error_message = __( 'Upload failed!', 'quiz-master-next' );
					}
				} else {
					$do_error = 1;
					$do_error_message = $movefile['error'];
				}
			} else {
				if ( ! empty ($file_upload_type) ) {
					$filestype = explode(',', $file_upload_type);
					foreach ( $filestype as $file ) {
						if ( strpos($file, '/') !== false ) {
							$filetypes = explode('/', $file);
							if ( ! empty($filetypes[0]) && 'application' == $filetypes[0] ) {
								$filetypes_allowed[] = 'pdf';
							} else {
							$filetypes_allowed[] = $filetypes[0];
							}
						}else {
							$filetypes_allowed[] = $file;
						}
					}
					if ( count($filetypes_allowed) > 1 ) {
						$files_allowed = implode(',', $filetypes_allowed);
					} else {
						$files_allowed = $filetypes_allowed[0]; // Just take the single element
					}
					$do_error = 1;
					$do_error_message = __('File Upload Unsuccessful! (Please upload ', 'quiz-master-next') . $files_allowed . __(' file type)', 'quiz-master-next');
				} else {
					$do_error = 1;
					$do_error_message = __( 'File Upload Unsuccessful! (Please select file type)', 'quiz-master-next' );
				}
			}
			if ( 1 == $do_error ) {
				$qsm_global_result_warning_message .= '<div class="qsm-result-page-warning">' . $do_error_message . '</div>';
			}
			$this->user_answer[ $user_answer_key ] = $user_answer_value;
		}
	}

	public function set_answer_status() {
		$this->answer_status = 'correct';
	}
}
