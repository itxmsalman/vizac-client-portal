<?php
/**
 * Plugin Name: VIZAC Client Portal
 * Plugin URI: https://vizco.co.uk/
 * Description: Client application tracking and progress management for VIZAC Consultant.
 * Version: 1.0.0
 * Author: VIZAC Consultant
 * Text Domain: vizac-client-portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * VIZAC - Application Progress Bar
 * Shortcode: [application_progress_bar]
 */
function vizac_application_progress_bar() {

	if ( ! is_user_logged_in() ) {
		return '<div style="padding:20px;background:#111;color:#fff;border-radius:10px;">Please log in to view your application status.</div>';
	}

	$user_id = get_current_user_id();

	if ( ! function_exists( 'get_field_object' ) ) {
		return '<div style="padding:20px;background:#111;color:#fff;border-radius:10px;">Application status is currently unavailable.</div>';
	}

	$field = get_field_object(
		'application_status',
		'user_' . $user_id
	);

	if ( empty( $field ) ) {
		return '<div style="padding:20px;background:#111;color:#fff;border-radius:10px;">Application status could not be loaded.</div>';
	}

	$current_value = get_user_meta(
		$user_id,
		'application_status',
		true
	);

	if ( empty( $current_value ) ) {
		return '<div style="padding:20px;background:#111;color:#fff;border-radius:10px;">Your application status has not been updated yet.</div>';
	}

	$choices = isset( $field['choices'] ) && is_array( $field['choices'] )
		? $field['choices']
		: array();

	$current_label = isset( $choices[ $current_value ] )
		? $choices[ $current_value ]
		: ucwords(
			str_replace(
				array( '_', '-' ),
				' ',
				$current_value
			)
		);

	/*
	 * Detect the current state from either
	 * the saved ACF value or its visible label.
	 */
	$status_text = strtolower(
		(string) $current_value . ' ' . (string) $current_label
	);

	$is_refused = false !== strpos( $status_text, 'refus' );

	$is_issued =
		false !== strpos( $status_text, 'issued' ) ||
		false !== strpos( $status_text, 'visa issue' );

	$is_approved =
		! $is_issued &&
		false !== strpos( $status_text, 'approv' );

	$is_review =
		false !== strpos( $status_text, 'review' );

	$is_submitted =
		false !== strpos( $status_text, 'submit' );

	/*
	 * Build the correct branch.
	 *
	 * Refused:
	 * Submitted -> Under Review -> Visa Refused
	 *
	 * Successful:
	 * Submitted -> Under Review -> Approved -> Visa Issued
	 */
	if ( $is_refused ) {

		$stages = array(
			'Application Submitted',
			'Under Review',
			'Visa Refused',
		);

		$current_stage = 2;
		$percentage    = 100;
		$main_color    = '#ff3b3b';

	} elseif ( $is_issued ) {

		$stages = array(
			'Application Submitted',
			'Under Review',
			'Approved',
			'Visa Issued',
		);

		$current_stage = 3;
		$percentage    = 100;
		$main_color    = '#baff00';

	} elseif ( $is_approved ) {

		$stages = array(
			'Application Submitted',
			'Under Review',
			'Approved',
			'Visa Issued',
		);

		$current_stage = 2;
		$percentage    = 75;
		$main_color    = '#baff00';

	} elseif ( $is_review ) {

		$stages = array(
			'Application Submitted',
			'Under Review',
			'Approved',
			'Visa Issued',
		);

		$current_stage = 1;
		$percentage    = 50;
		$main_color    = '#baff00';

	} else {

		$stages = array(
			'Application Submitted',
			'Under Review',
			'Approved',
			'Visa Issued',
		);

		$current_stage = 0;
		$percentage    = 25;
		$main_color    = '#baff00';
	}

	$html = '';

	$html .= '<div style="
		background:#111;
		border:1px solid #292929;
		border-radius:16px;
		padding:30px;
		color:#fff;
		width:100%;
		box-sizing:border-box;
	">';

	/*
	 * Header.
	 */
	$html .= '<div style="
		display:flex;
		justify-content:space-between;
		align-items:center;
		gap:20px;
		margin-bottom:20px;
	">';

	$html .= '<div>';

	$html .= '<div style="
		color:' . esc_attr( $main_color ) . ';
		font-size:12px;
		font-weight:700;
		letter-spacing:2px;
	">VISA APPLICATION</div>';

	$html .= '<h3 style="
		color:#fff;
		margin:6px 0 0;
		font-size:26px;
	">Application Progress</h3>';

	$html .= '</div>';

	$html .= '<div style="text-align:right;">';

	$html .= '<div style="
		color:#999;
		font-size:12px;
	">Current Status</div>';

	$html .= '<strong style="
		color:' . esc_attr( $main_color ) . ';
		font-size:19px;
	">';

	$html .= esc_html( $current_label );

	$html .= '</strong>';

	$html .= '</div>';

	$html .= '</div>';

	/*
	 * Progress bar.
	 */
	$html .= '<div style="
		height:12px;
		background:#292929;
		border-radius:20px;
		overflow:hidden;
	">';

	$html .= '<div style="
		height:100%;
		width:' . esc_attr( $percentage ) . '%;
		background:' . esc_attr( $main_color ) . ';
		border-radius:20px;
		transition:width .5s ease;
	"></div>';

	$html .= '</div>';

	$html .= '<div style="
		text-align:right;
		color:#999;
		font-size:12px;
		margin-top:8px;
	">';

	if ( $is_refused ) {
		$html .= 'Decision Reached';
	} elseif ( $is_issued ) {
		$html .= '100% Complete';
	} else {
		$html .= esc_html( $percentage ) . '% Complete';
	}

	$html .= '</div>';

	/*
	 * Stage indicators.
	 */
	$html .= '<div style="
		display:flex;
		justify-content:space-between;
		gap:10px;
		margin-top:30px;
	">';

	foreach ( $stages as $index => $stage_label ) {

		$is_current_stage  = $index === $current_stage;
		$is_previous_stage = $index < $current_stage;

		if ( $is_refused && $is_current_stage ) {

			$circle_background = '#ff3b3b';
			$circle_color      = '#ffffff';
			$label_color       = '#ff5b5b';

		} elseif ( $is_current_stage || $is_previous_stage ) {

			$circle_background = '#baff00';
			$circle_color      = '#000000';
			$label_color       = '#ffffff';

		} else {

			$circle_background = '#222222';
			$circle_color      = '#777777';
			$label_color       = '#777777';
		}

		$html .= '<div style="
			flex:1;
			text-align:center;
		">';

		$html .= '<div style="
			width:38px;
			height:38px;
			margin:0 auto 10px;
			border-radius:50%;
			display:flex;
			align-items:center;
			justify-content:center;
			font-weight:700;
			background:' . $circle_background . ';
			color:' . $circle_color . ';
		">';

		if ( $is_refused && $is_current_stage ) {

			$html .= '&#10005;';

		} elseif ( $is_previous_stage || $is_issued ) {

			$html .= '&#10003;';

		} else {

			$html .= esc_html( $index + 1 );
		}

		$html .= '</div>';

		$html .= '<div style="
			font-size:13px;
			color:' . $label_color . ';
		">';

		$html .= esc_html( $stage_label );

		$html .= '</div>';

		$html .= '</div>';
	}

	$html .= '</div>';

	/*
	 * REFUSED MESSAGE
	 */
	if ( $is_refused ) {

		$html .= '<div style="
			margin-top:30px;
			padding:22px;
			background:rgba(255,59,59,0.08);
			border:1px solid rgba(255,59,59,0.45);
			border-radius:12px;
		">';

		$html .= '<div style="
			color:#ff4b4b;
			font-size:18px;
			font-weight:700;
			margin-bottom:8px;
		">';

		$html .= '&#9888; Visa Application Refused';

		$html .= '</div>';

		$html .= '<div style="
			color:#dddddd;
			font-size:14px;
			line-height:1.7;
		">';

		$html .= 'A decision has been made and your visa application has been refused. ';
		$html .= 'Please provide us with the complete refusal decision letter so our consultancy team can review the reasons given and advise you on the appropriate next steps.';

		$html .= '</div>';

		$html .= '<div style="
			margin-top:10px;
			color:#aaa;
			font-size:13px;
		">';

		$html .= 'Please provide all pages of the refusal letter when contacting your consultant.';

		$html .= '</div>';

		$html .= '</div>';
	}

	/*
	 * APPROVED MESSAGE
	 */
	if ( $is_approved ) {

		$html .= '<div style="
			margin-top:30px;
			padding:22px;
			background:rgba(186,255,0,0.07);
			border:1px solid rgba(186,255,0,0.40);
			border-radius:12px;
		">';

		$html .= '<div style="
			color:#baff00;
			font-size:21px;
			font-weight:700;
			margin-bottom:8px;
		">';

		$html .= '&#127881; Congratulations! Your Visa Has Been Approved';

		$html .= '</div>';

		$html .= '<div style="
			color:#e5e5e5;
			font-size:14px;
			line-height:1.7;
		">';

		$html .= 'Great news — your visa application has been approved. ';
		$html .= 'Visa issuance is expected within approximately 24 hours, subject to the relevant authority completing the final issuance process. ';

		$html .= 'There is no action required from you at this stage unless your consultant contacts you for additional information.';

		$html .= '</div>';

		$html .= '<div style="
			margin-top:12px;
			color:#aaa;
			font-size:13px;
		">';

		$html .= 'We will update your account as soon as the visa has been issued.';

		$html .= '</div>';

		$html .= '</div>';
	}

	/*
	 * VISA ISSUED MESSAGE
	 */
	if ( $is_issued ) {

		$html .= '<div style="
			margin-top:30px;
			padding:24px;
			background:rgba(186,255,0,0.07);
			border:1px solid rgba(186,255,0,0.45);
			border-radius:12px;
		">';

		$html .= '<div style="
			color:#baff00;
			font-size:22px;
			font-weight:700;
			margin-bottom:8px;
		">';

		$html .= '&#127881; Congratulations — Visa Issued Successfully!';

		$html .= '</div>';

		$html .= '<div style="
			color:#e5e5e5;
			font-size:14px;
			line-height:1.7;
		">';

		$html .= 'Your visa has now been issued and your application process is complete. ';
		$html .= 'Please review the visa details carefully and contact your consultant if you have any questions about the next steps.';

		$html .= '</div>';

		$html .= '<div style="
			margin-top:12px;
			color:#baff00;
			font-size:13px;
			font-weight:600;
		">';

		$html .= '&#10003; Application completed successfully';

		$html .= '</div>';

		$html .= '</div>';
	}

	/*
	 * Under review message.
	 */
	if ( $is_review ) {

		$html .= '<div style="
			margin-top:25px;
			padding:18px;
			background:#171717;
			border:1px solid #292929;
			border-radius:10px;
			color:#ccc;
			font-size:14px;
			line-height:1.6;
		">';

		$html .= 'Your application is currently under review. Your consultant will update this page when there is a new development, so you can follow the progress directly from your account.';

		$html .= '</div>';
	}

	$html .= '</div>';

/*
 * Display application history underneath the progress card.
 */
$html .= vizac_render_application_history( $user_id );

return $html;
}

add_shortcode(
	'application_progress_bar',
	'vizac_application_progress_bar'
);

add_shortcode(
	'display_application_progress_bar',
	'vizac_application_progress_bar'
);

/**
 * VIZAC - Track Application Status Changes
 *
 * Records changes made to the ACF application_status field.
 */
function vizac_track_application_status_change( $value, $post_id, $field, $original ) {

	// Only process WordPress user profiles.
	if ( strpos( (string) $post_id, 'user_' ) !== 0 ) {
		return $value;
	}

	$user_id = absint(
		str_replace( 'user_', '', $post_id )
	);

	if ( ! $user_id ) {
		return $value;
	}

	// Current value before ACF saves the new one.
	$old_value = get_user_meta(
		$user_id,
		'application_status',
		true
	);

	// Don't create duplicate history entries.
	if ( (string) $old_value === (string) $value ) {
		return $value;
	}

	$history = get_user_meta(
		$user_id,
		'vizac_application_status_history',
		true
	);

	if ( ! is_array( $history ) ) {
		$history = array();
	}

	$history[] = array(
		'previous_status' => sanitize_text_field( $old_value ),
		'new_status'      => sanitize_text_field( $value ),
		'changed_at'      => current_time( 'mysql' ),
		'changed_by'      => get_current_user_id(),
	);

	update_user_meta(
		$user_id,
		'vizac_application_status_history',
		$history
	);

	return $value;
}

add_filter(
	'acf/update_value/name=application_status',
	'vizac_track_application_status_change',
	10,
	4
);


/**
 * VIZAC - Display Application Status History
 */
function vizac_render_application_history( $user_id ) {

	$history = get_user_meta(
		$user_id,
		'vizac_application_status_history',
		true
	);

	if ( empty( $history ) || ! is_array( $history ) ) {
		return '';
	}

	/*
	 * Show newest updates first.
	 */
	$history = array_reverse( $history );

	/*
	 * Friendly labels for stored ACF values.
	 */
	$labels = array(
		'submitted' => 'Application Submitted',
		'review'    => 'Under Review',
		'approved'  => 'Visa Approved',
		'issued'    => 'Visa Issued',
		'refused'   => 'Visa Refused',
	);

	$html = '';

	$html .= '<div style="
		margin-top:25px;
		background:#111;
		border:1px solid #292929;
		border-radius:16px;
		padding:30px;
		color:#fff;
		box-sizing:border-box;
	">';

	$html .= '<div style="
		color:#baff00;
		font-size:12px;
		font-weight:700;
		letter-spacing:2px;
		margin-bottom:6px;
	">CASE ACTIVITY</div>';

	$html .= '<h3 style="
		color:#fff;
		font-size:24px;
		margin:0 0 28px;
	">Application Timeline</h3>';

	foreach ( $history as $entry ) {

		$previous_status = isset( $entry['previous_status'] )
			? sanitize_text_field( $entry['previous_status'] )
			: '';

		$new_status = isset( $entry['new_status'] )
			? sanitize_text_field( $entry['new_status'] )
			: '';

		$changed_at = isset( $entry['changed_at'] )
			? sanitize_text_field( $entry['changed_at'] )
			: '';

		$changed_by = isset( $entry['changed_by'] )
			? absint( $entry['changed_by'] )
			: 0;


		/*
		 * Friendly status names.
		 */
		$previous_label = isset( $labels[ $previous_status ] )
			? $labels[ $previous_status ]
			: ucwords(
				str_replace(
					array( '_', '-' ),
					' ',
					$previous_status
				)
			);

		$new_label = isset( $labels[ $new_status ] )
			? $labels[ $new_status ]
			: ucwords(
				str_replace(
					array( '_', '-' ),
					' ',
					$new_status
				)
			);


		/*
		 * Status colour.
		 */
		$status_color = '#baff00';

		if ( false !== stripos( $new_label, 'refus' ) ) {
			$status_color = '#ff4b4b';
		}


		/*
		 * Format date using WordPress timezone.
		 */
		$formatted_date = '';

		if ( ! empty( $changed_at ) ) {

			$timestamp = strtotime( $changed_at );

			if ( $timestamp ) {
				$formatted_date = wp_date(
					'j F Y · g:i A',
					$timestamp
				);
			}
		}


		/*
		 * Find who made the update.
		 */
		$changed_by_name = '';

		if ( $changed_by ) {

			$user = get_userdata( $changed_by );

			if ( $user ) {
				$changed_by_name = $user->display_name;
			}
		}


		$html .= '<div style="
			display:flex;
			gap:16px;
			position:relative;
			padding-bottom:28px;
		">';


		/*
		 * Timeline marker.
		 */
		$html .= '<div style="
			width:14px;
			height:14px;
			background:' . esc_attr( $status_color ) . ';
			border-radius:50%;
			flex:0 0 14px;
			margin-top:5px;
			box-shadow:0 0 12px ' . esc_attr( $status_color ) . ';
		"></div>';


		$html .= '<div style="flex:1;">';


		/*
		 * New status.
		 */
		$html .= '<div style="
			color:' . esc_attr( $status_color ) . ';
			font-size:16px;
			font-weight:700;
		">';

		$html .= esc_html( $new_label );

		$html .= '</div>';


		/*
		 * Status transition.
		 */
		if ( ! empty( $previous_status ) ) {

			$html .= '<div style="
				color:#ccc;
				font-size:13px;
				margin-top:5px;
			">';

			$html .= 'Status updated from ';
			$html .= '<strong>' . esc_html( $previous_label ) . '</strong>';
			$html .= ' to ';
			$html .= '<strong>' . esc_html( $new_label ) . '</strong>';

			$html .= '</div>';
		}


		/*
		 * Date.
		 */
		if ( ! empty( $formatted_date ) ) {

			$html .= '<div style="
				color:#777;
				font-size:12px;
				margin-top:5px;
			">';

			$html .= esc_html( $formatted_date );

			$html .= '</div>';
		}


		/*
		 * Consultant/admin who changed it.
		 */
		if ( ! empty( $changed_by_name ) ) {

			$html .= '<div style="
				color:#666;
				font-size:11px;
				margin-top:3px;
			">';

			$html .= 'Updated by ' . esc_html( $changed_by_name );

			$html .= '</div>';
		}


		$html .= '</div>';
		$html .= '</div>';
	}

	$html .= '</div>';

	return $html;
}