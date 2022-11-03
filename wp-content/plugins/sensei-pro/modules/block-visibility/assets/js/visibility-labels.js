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

const VISIBILITY_ICON_BLACK =
	'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzYiIGhlaWdodD0iMzYiIHZpZXdCb3g9IjAgMCAzNiAzNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0yMy43ODA0IDE0LjIxOThMMjIuNzA4OCAxNS4yOTE1QzIzLjAxNyAxNS41MTU3IDIzLjI5OCAxNS43NTA5IDIzLjU1MjMgMTUuOTg3MUMyNC4xNjE4IDE2LjU1MyAyNC42MTI0IDE3LjExOTkgMjQuOTA5NCAxNy41NDM3QzI0Ljk5NjcgMTcuNjY4MyAyNS4wNzA0IDE3Ljc4IDI1LjEzMDYgMTcuODc1QzI1LjA3MDQgMTcuOTcgMjQuOTk2NyAxOC4wODE3IDI0LjkwOTQgMTguMjA2M0MyNC42MTI0IDE4LjYzMDEgMjQuMTYxOCAxOS4xOTcgMjMuNTUyMyAxOS43NjI5QzIyLjMzNTMgMjAuODkzIDIwLjUwOTEgMjIgMTguMDAwMiAyMkMxNy4zNTU4IDIyIDE2Ljc1NjUgMjEuOTI3IDE2LjIwMDkgMjEuNzk5M0wxNC45ODY5IDIzLjAxMzNDMTUuODc5IDIzLjMxNDIgMTYuODgyNSAyMy41IDE4LjAwMDIgMjMuNUMyMC45OTEyIDIzLjUgMjMuMTY1IDIyLjE2OTUgMjQuNTczIDIwLjg2MjFDMjUuMjc2MSAyMC4yMDkzIDI1Ljc5NDIgMTkuNTU3NCAyNi4xMzc4IDE5LjA2NzJDMjYuMzA5OSAxOC44MjE2IDI2LjQzOTMgMTguNjE1IDI2LjUyNyAxOC40NjcyQzI2LjU3MDkgMTguMzkzMyAyNi42MDQ0IDE4LjMzMzggMjYuNjI3OCAxOC4yOTE0QzI2LjYzOTUgMTguMjcwMiAyNi42NDg2IDE4LjI1MzIgMjYuNjU1MiAxOC4yNDA3TDI2LjY1OTcgMTguMjMyM0MyNi42Njk4IDE4LjIxMzYgMjYuNjc5MSAxOC4xOTQ1IDI2LjY4NzUgMTguMTc1QzI2Ljc2ODUgMTcuOTg5MyAyNi43NzM5IDE3Ljc3MjQgMjYuNjg3NiAxNy41NzVDMjYuNjc5MSAxNy41NTU1IDI2LjY2OTggMTcuNTM2NCAyNi42NTk3IDE3LjUxNzdMMjYuNjU1MiAxNy41MDkzTDI2LjY0NDggMTcuNDg5OEwyNi42Mjc4IDE3LjQ1ODZDMjYuNjA0NCAxNy40MTYyIDI2LjU3MDkgMTcuMzU2NyAyNi41MjcgMTcuMjgyOEMyNi40MzkzIDE3LjEzNSAyNi4zMDk5IDE2LjkyODQgMjYuMTM3OCAxNi42ODI4QzI1Ljc5NDIgMTYuMTkyNiAyNS4yNzYxIDE1LjU0MDcgMjQuNTczIDE0Ljg4NzlDMjQuMzMxNCAxNC42NjM2IDI0LjA2NzMgMTQuNDM4NiAyMy43ODA0IDE0LjIxOThaTTEwLjkzOTUgMjQuOTM5NEw5LjkzOTYxIDIzLjkzOTVMMTIuMjkzMyAyMS41ODU3QzExLjk3NzkgMjEuMzQ5NSAxMS42ODk0IDIxLjEwNTQgMTEuNDI3MyAyMC44NjIxQzEwLjcyNDMgMjAuMjA5MyAxMC4yMDYxIDE5LjU1NzQgOS44NjI1NCAxOS4wNjcyQzkuNjkwNCAxOC44MjE2IDkuNTYxMDYgMTguNjE1IDkuNDczMzMgMTguNDY3MkM5LjQyOTQ1IDE4LjM5MzMgOS4zOTU4OSAxOC4zMzM4IDkuMzcyNTMgMTguMjkxNEM5LjM2MDg1IDE4LjI3MDIgOS4zNTE3MSAxOC4yNTMyIDkuMzQ1MSAxOC4yNDA3TDkuMzQwNjggMTguMjMyM0M5LjI4MTU4IDE4LjEyMyA5LjI1MDEgMTggOS4yNSAxNy44NzVDOS4yNDk5MiAxNy43NzM1IDkuMjcwNDkgMTcuNjcwNyA5LjMxMzczIDE3LjU3MjhDOS4zMjE5NSAxNy41NTQxIDkuMzMwOTQgMTcuNTM1NyA5LjM0MDY3IDE3LjUxNzdMOS4zNDUxIDE3LjUwOTNDOS4zNTE3MSAxNy40OTY4IDkuMzYwODUgMTcuNDc5OCA5LjM3MjUzIDE3LjQ1ODZDOS4zOTU4OSAxNy40MTYyIDkuNDI5NDUgMTcuMzU2NyA5LjQ3MzMzIDE3LjI4MjhDOS41NjEwNiAxNy4xMzUgOS42OTA0IDE2LjkyODQgOS44NjI1NCAxNi42ODI4QzEwLjIwNjEgMTYuMTkyNiAxMC43MjQzIDE1LjU0MDcgMTEuNDI3MyAxNC44ODc5QzEyLjgzNTMgMTMuNTgwNSAxNS4wMDkxIDEyLjI1IDE4LjAwMDIgMTIuMjVDMTkuMTU4MSAxMi4yNSAyMC4xOTM1IDEyLjQ0OTQgMjEuMTA5NCAxMi43Njk2TDIzLjkzOTYgOS45Mzk0NUwyNS4wMDAzIDExLjAwMDFMMjUuMDAwMiAxMS4wMDAyTDI0LjkzOTUgMTAuOTM5NUwxMC45Mzk1IDI0LjkzOTRaTTE2LjgwNjQgMjEuMTkzOEMxNy4xODM0IDIxLjMxMTYgMTcuNTg0NCAyMS4zNzUgMTguMDAwMiAyMS4zNzVDMjAuMjA5MyAyMS4zNzUgMjIuMDAwMiAxOS41ODQxIDIyLjAwMDIgMTcuMzc1QzIyLjAwMDIgMTYuOTU5MiAyMS45MzY3IDE2LjU1ODIgMjEuODE5IDE2LjE4MTJMMTYuODA2NCAyMS4xOTM4Wk0xNC4wMDAyIDE3LjM3NUMxNC4wMDAyIDE4LjA5NTQgMTQuMTkwNiAxOC43NzEzIDE0LjUyMzkgMTkuMzU1MkwxMy4zNjY2IDIwLjUxMjVDMTMuMDI4NyAyMC4yNzIxIDEyLjcyMjggMjAuMDE4MSAxMi40NDggMTkuNzYyOUMxMS44Mzg2IDE5LjE5NyAxMS4zODggMTguNjMwMSAxMS4wOTA5IDE4LjIwNjNDMTEuMDAzNiAxOC4wODE3IDEwLjkyOTkgMTcuOTcgMTAuODY5NyAxNy44NzVDMTAuOTI5OSAxNy43OCAxMS4wMDM2IDE3LjY2ODMgMTEuMDkwOSAxNy41NDM3QzExLjM4OCAxNy4xMTk5IDExLjgzODYgMTYuNTUzIDEyLjQ0OCAxNS45ODcxQzEzLjI2NTMgMTUuMjI4MSAxNC4zNTc0IDE0LjQ3OTYgMTUuNzQ2NiAxNC4wNjk4QzE0LjY5MjIgMTQuNzkwMSAxNC4wMDAyIDE2LjAwMTcgMTQuMDAwMiAxNy4zNzVaIiBmaWxsPSJibGFjayIvPgo8L3N2Zz4K';

const VISIBILITY_ICON_WHITE =
	'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB2aWV3Qm94PSI5LjI1IDkuOTM5IDE3LjUgMTUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0yMy43ODA0IDE0LjIxOThMMjIuNzA4OCAxNS4yOTE1QzIzLjAxNyAxNS41MTU3IDIzLjI5OCAxNS43NTA5IDIzLjU1MjMgMTUuOTg3MUMyNC4xNjE4IDE2LjU1MyAyNC42MTI0IDE3LjExOTkgMjQuOTA5NCAxNy41NDM3QzI0Ljk5NjcgMTcuNjY4MyAyNS4wNzA0IDE3Ljc4IDI1LjEzMDYgMTcuODc1QzI1LjA3MDQgMTcuOTcgMjQuOTk2NyAxOC4wODE3IDI0LjkwOTQgMTguMjA2M0MyNC42MTI0IDE4LjYzMDEgMjQuMTYxOCAxOS4xOTcgMjMuNTUyMyAxOS43NjI5QzIyLjMzNTMgMjAuODkzIDIwLjUwOTEgMjIgMTguMDAwMiAyMkMxNy4zNTU4IDIyIDE2Ljc1NjUgMjEuOTI3IDE2LjIwMDkgMjEuNzk5M0wxNC45ODY5IDIzLjAxMzNDMTUuODc5IDIzLjMxNDIgMTYuODgyNSAyMy41IDE4LjAwMDIgMjMuNUMyMC45OTEyIDIzLjUgMjMuMTY1IDIyLjE2OTUgMjQuNTczIDIwLjg2MjFDMjUuMjc2MSAyMC4yMDkzIDI1Ljc5NDIgMTkuNTU3NCAyNi4xMzc4IDE5LjA2NzJDMjYuMzA5OSAxOC44MjE2IDI2LjQzOTMgMTguNjE1IDI2LjUyNyAxOC40NjcyQzI2LjU3MDkgMTguMzkzMyAyNi42MDQ0IDE4LjMzMzggMjYuNjI3OCAxOC4yOTE0QzI2LjYzOTUgMTguMjcwMiAyNi42NDg2IDE4LjI1MzIgMjYuNjU1MiAxOC4yNDA3TDI2LjY1OTcgMTguMjMyM0MyNi42Njk4IDE4LjIxMzYgMjYuNjc5MSAxOC4xOTQ1IDI2LjY4NzUgMTguMTc1QzI2Ljc2ODUgMTcuOTg5MyAyNi43NzM5IDE3Ljc3MjQgMjYuNjg3NiAxNy41NzVDMjYuNjc5MSAxNy41NTU1IDI2LjY2OTggMTcuNTM2NCAyNi42NTk3IDE3LjUxNzdMMjYuNjU1MiAxNy41MDkzTDI2LjY0NDggMTcuNDg5OEwyNi42Mjc4IDE3LjQ1ODZDMjYuNjA0NCAxNy40MTYyIDI2LjU3MDkgMTcuMzU2NyAyNi41MjcgMTcuMjgyOEMyNi40MzkzIDE3LjEzNSAyNi4zMDk5IDE2LjkyODQgMjYuMTM3OCAxNi42ODI4QzI1Ljc5NDIgMTYuMTkyNiAyNS4yNzYxIDE1LjU0MDcgMjQuNTczIDE0Ljg4NzlDMjQuMzMxNCAxNC42NjM2IDI0LjA2NzMgMTQuNDM4NiAyMy43ODA0IDE0LjIxOThaTTEwLjkzOTUgMjQuOTM5NEw5LjkzOTYxIDIzLjkzOTVMMTIuMjkzMyAyMS41ODU3QzExLjk3NzkgMjEuMzQ5NSAxMS42ODk0IDIxLjEwNTQgMTEuNDI3MyAyMC44NjIxQzEwLjcyNDMgMjAuMjA5MyAxMC4yMDYxIDE5LjU1NzQgOS44NjI1NCAxOS4wNjcyQzkuNjkwNCAxOC44MjE2IDkuNTYxMDYgMTguNjE1IDkuNDczMzMgMTguNDY3MkM5LjQyOTQ1IDE4LjM5MzMgOS4zOTU4OSAxOC4zMzM4IDkuMzcyNTMgMTguMjkxNEM5LjM2MDg1IDE4LjI3MDIgOS4zNTE3MSAxOC4yNTMyIDkuMzQ1MSAxOC4yNDA3TDkuMzQwNjggMTguMjMyM0M5LjI4MTU4IDE4LjEyMyA5LjI1MDEgMTggOS4yNSAxNy44NzVDOS4yNDk5MiAxNy43NzM1IDkuMjcwNDkgMTcuNjcwNyA5LjMxMzczIDE3LjU3MjhDOS4zMjE5NSAxNy41NTQxIDkuMzMwOTQgMTcuNTM1NyA5LjM0MDY3IDE3LjUxNzdMOS4zNDUxIDE3LjUwOTNDOS4zNTE3MSAxNy40OTY4IDkuMzYwODUgMTcuNDc5OCA5LjM3MjUzIDE3LjQ1ODZDOS4zOTU4OSAxNy40MTYyIDkuNDI5NDUgMTcuMzU2NyA5LjQ3MzMzIDE3LjI4MjhDOS41NjEwNiAxNy4xMzUgOS42OTA0IDE2LjkyODQgOS44NjI1NCAxNi42ODI4QzEwLjIwNjEgMTYuMTkyNiAxMC43MjQzIDE1LjU0MDcgMTEuNDI3MyAxNC44ODc5QzEyLjgzNTMgMTMuNTgwNSAxNS4wMDkxIDEyLjI1IDE4LjAwMDIgMTIuMjVDMTkuMTU4MSAxMi4yNSAyMC4xOTM1IDEyLjQ0OTQgMjEuMTA5NCAxMi43Njk2TDIzLjkzOTYgOS45Mzk0NUwyNS4wMDAzIDExLjAwMDFMMjUuMDAwMiAxMS4wMDAyTDI0LjkzOTUgMTAuOTM5NUwxMC45Mzk1IDI0LjkzOTRaTTE2LjgwNjQgMjEuMTkzOEMxNy4xODM0IDIxLjMxMTYgMTcuNTg0NCAyMS4zNzUgMTguMDAwMiAyMS4zNzVDMjAuMjA5MyAyMS4zNzUgMjIuMDAwMiAxOS41ODQxIDIyLjAwMDIgMTcuMzc1QzIyLjAwMDIgMTYuOTU5MiAyMS45MzY3IDE2LjU1ODIgMjEuODE5IDE2LjE4MTJMMTYuODA2NCAyMS4xOTM4Wk0xNC4wMDAyIDE3LjM3NUMxNC4wMDAyIDE4LjA5NTQgMTQuMTkwNiAxOC43NzEzIDE0LjUyMzkgMTkuMzU1MkwxMy4zNjY2IDIwLjUxMjVDMTMuMDI4NyAyMC4yNzIxIDEyLjcyMjggMjAuMDE4MSAxMi40NDggMTkuNzYyOUMxMS44Mzg2IDE5LjE5NyAxMS4zODggMTguNjMwMSAxMS4wOTA5IDE4LjIwNjNDMTEuMDAzNiAxOC4wODE3IDEwLjkyOTkgMTcuOTcgMTAuODY5NyAxNy44NzVDMTAuOTI5OSAxNy43OCAxMS4wMDM2IDE3LjY2ODMgMTEuMDkwOSAxNy41NDM3QzExLjM4OCAxNy4xMTk5IDExLjgzODYgMTYuNTUzIDEyLjQ0OCAxNS45ODcxQzEzLjI2NTMgMTUuMjI4MSAxNC4zNTc0IDE0LjQ3OTYgMTUuNzQ2NiAxNC4wNjk4QzE0LjY5MjIgMTQuNzkwMSAxNC4wMDAyIDE2LjAwMTcgMTQuMDAwMiAxNy4zNzVaIiBzdHlsZT0iZmlsbDogcmdiKDI1NSwgMjU1LCAyNTUpOyIvPgo8L3N2Zz4=';

/**
 * Generate CSS to add a badge for the block in 'List View'
 *
 * @param {string} clientId Block ID.
 */
const getListViewCss = ( clientId ) => {
	// Prepare block list menu cell for this block.
	let css = `
		.block-editor-list-view-tree
		tr[data-block="${ clientId }"]
		.block-editor-list-view-block-select-button__title {
			margin-right: 36px;
			position: relative;
			width: 100%;
		}
	`;

	// Insert the visibility indicator icon.
	css += `
		.block-editor-list-view-tree
		tr[data-block="${ clientId }"]
		.block-editor-list-view-block-select-button__title::after {
			content: '';
			display: block;
			position: absolute;
			top: 50%;
			right: -36px;
			transform: translateY(-50%);
			width: 18px;
			height: 18px;
			border: 3px solid transparent;
			background-image: url("${ VISIBILITY_ICON_BLACK }");
			background-repeat: no-repeat;
			background-position: center;
		}
	`;

	// Change the color of the icon to white when the block is selected.
	css += `
		.block-editor-list-view-tree
		tr[data-block="${ clientId }"].is-selected
		.block-editor-list-view-block-select-button__title::after {
			background-image: url("${ VISIBILITY_ICON_WHITE }");
		}
	`;

	// Align the distance between visibility icon and the lock icon according to designs.
	css += `
		.block-editor-list-view-tree
		tr[data-block="${ clientId }"]
		.block-editor-list-view-block-select-button__lock {
			padding-left: 8px;
		}
	`;

	return css;
};

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

	const listViewCss = getListViewCss( props.clientId );

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
