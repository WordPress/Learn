/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __, sprintf, _n } from '@wordpress/i18n';
import moment from 'moment';

/**
 * Internal dependencies
 */
import { optionsMap } from './visibility-options/options';

const BLOCK_HIDDEN_CLASS = 'sensei-block-visibility__hidden';

/**
 * Generate CSS to add a badge for the block in 'List View'
 *
 * @param {string} clientId   Block ID.
 * @param {string} badgeLabel Badge label text.
 */
const getListViewCss = ( clientId, badgeLabel ) => `
		.block-editor-list-view-tree
			tr[data-block="${ clientId }"]
				.block-editor-list-view-block-select-button__title::after {
							content: '${ badgeLabel }';
							border-radius: 2px;
							background-color: var(--sensei-visibility-badge-color);
							color: #1e1e1e;
							display: inline-flex;
							padding: 2px 6px;
							margin: 0 8px;
							overflow: hidden;
							text-overflow: ellipsis;
							font-size: 11px;
						}
		`;

/**
 * Get badge label text based on the visibility selected.
 *
 * @param {Object} senseiVisibility
 */
const getBadgeLabel = ( senseiVisibility ) => {
	const fallbackLabel = __( 'Hidden', 'sensei-pro' );
	const senseiVisibilityTypes = Object.keys( senseiVisibility );
	const senseiVisibilityType =
		senseiVisibilityTypes.length > 1
			? 'HIDDEN'
			: senseiVisibilityTypes[ 0 ];

	switch ( senseiVisibilityType ) {
		case 'GROUPS':
			return (
				senseiVisibility.GROUPS.groups
					?.map( ( { label } ) => label )
					.join( ', ' ) || fallbackLabel
			);

		case 'SCHEDULE':
			const { daysAfterCourseStart } = senseiVisibility.SCHEDULE;
			if ( daysAfterCourseStart ) {
				return sprintf(
					// Translators: Placeholder %s is the number of days.
					_n(
						'%s day after course starts',
						'%s days after course starts',
						daysAfterCourseStart,
						'sensei-pro'
					),
					daysAfterCourseStart
				);
			}
			const [ startDate, endDate ] = [
				senseiVisibility.SCHEDULE.startDate,
				senseiVisibility.SCHEDULE.endDate,
			].map( ( date ) => date && moment( date ).format( 'YYYY-MM-DD' ) );

			if ( startDate && endDate ) {
				return sprintf(
					// Translators: %1$s is the start date and %2$s is the end/stop date.
					__( 'Visible between %1$s and %2$s' ),
					`${ startDate }`,
					`${ endDate }`
				);
			}

			if ( startDate && ! endDate ) {
				return sprintf(
					// Translators: Placeholder %s is the start date.
					__( 'Visible after %s', 'sensei-pro' ),
					`${ startDate }`
				);
			}

			if ( ! startDate && endDate ) {
				return sprintf(
					// Translators: Placeholder %s is the end date.
					__( 'Visible until %s', 'sensei-pro' ),
					`${ endDate }`
				);
			}

			return fallbackLabel;

		default:
			return (
				optionsMap[ senseiVisibilityType ]?.badge_label || fallbackLabel
			);
	}
};

const addBlockEditClassName = ( settings ) => {
	const existingGetEditWrapperProps = settings.getEditWrapperProps;
	settings.getEditWrapperProps = ( attributes ) => {
		let props = {};

		if ( existingGetEditWrapperProps ) {
			props = existingGetEditWrapperProps( attributes );
		}

		if ( ! attributes.senseiVisibility ) {
			return props;
		}

		props.className = classnames( props.className, BLOCK_HIDDEN_CLASS );

		return props;
	};

	return settings;
};

/**
 * Draw visibilty borders for hidden blocks.
 *
 * @param {Object} props Block props.
 */
export const VisibilityLabels = ( props ) => {
	const { senseiVisibility } = props.attributes;

	// Do nothing if there is no senseiVisibility attribute present.
	if ( ! senseiVisibility ) {
		return null;
	}

	const badgeLabel = getBadgeLabel( senseiVisibility );

	const listViewCss = getListViewCss( props.clientId, badgeLabel );

	const borderCss = `#block-${ props.clientId }::before {
			content: '${ badgeLabel }';
		}`;

	return (
		<style
			dangerouslySetInnerHTML={ {
				__html: `
						${ listViewCss }
						${ borderCss }
					`,
			} }
		/>
	);
};

addFilter(
	'blocks.registerBlockType',
	'sensei/extend-supports/visibility/addBlockEditClassName',
	addBlockEditClassName
);
