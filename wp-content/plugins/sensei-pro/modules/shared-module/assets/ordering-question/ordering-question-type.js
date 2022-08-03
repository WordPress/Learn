/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import OrderingAnswer from './answer-blocks/ordering';

const orderingQuestionType = {
	title: __( 'Ordering', 'sensei-pro' ),
	description: __( 'Place the answers in the correct order.', 'sensei-pro' ),
	edit: OrderingAnswer,
	view: OrderingAnswer.view,
};

export default orderingQuestionType;
