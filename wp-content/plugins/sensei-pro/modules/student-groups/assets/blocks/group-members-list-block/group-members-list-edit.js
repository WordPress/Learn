/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import Settings from './group-members-list-settings';

const Edit = ( props ) => {
	const { numberOfMembers } = props.attributes;

	const blockProps = useBlockProps();

	const items = Array.from( { length: numberOfMembers } ).map(
		( item, index ) => (
			<li
				className="wp-block-sensei-pro-group-members-list__item"
				key={ index }
			>
				<div className="wp-block-sensei-pro-group-members-list__avatar-placeholder"></div>
			</li>
		)
	);

	return (
		<>
			<div { ...blockProps }>
				<ul className="wp-block-sensei-pro-group-members-list__list">
					{ items }
				</ul>
			</div>
			<Settings { ...props } />
		</>
	);
};

export default Edit;
