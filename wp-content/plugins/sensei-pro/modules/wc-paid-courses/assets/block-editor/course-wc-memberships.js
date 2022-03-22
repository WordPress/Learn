/**
 * WordPress dependencies.
 */
import { dispatch } from '@wordpress/data';

let noticeText;
let noticeLearnMoreText;
let noticeLearnMoreURL;

if ( window.sensei_wc_paid_courses_block_editor_course_wc_memberships ) {
	noticeText =
		window.sensei_wc_paid_courses_block_editor_course_wc_memberships
			.double_attached_product_notice;
	noticeLearnMoreText =
		window.sensei_wc_paid_courses_block_editor_course_wc_memberships
			.double_attached_product_notice_learn_more_text;
	noticeLearnMoreURL =
		window.sensei_wc_paid_courses_block_editor_course_wc_memberships
			.double_attached_product_notice_learn_more_url;
}

if ( noticeText ) {
	const options = {
		isDismissible: false,
	};

	// "Learn more" link.
	if ( noticeLearnMoreText && noticeLearnMoreURL ) {
		options.actions = [
			{
				label: noticeLearnMoreText,
				url: noticeLearnMoreURL,
			},
		];
	}

	// Show notice on double-attachment.
	dispatch( 'core/notices' ).createNotice( 'warning', noticeText, options );
}
