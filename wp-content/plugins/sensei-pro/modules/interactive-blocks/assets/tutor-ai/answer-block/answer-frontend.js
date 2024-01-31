/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import { ReactComponent as TutorIcon } from '../../icons/tutor-ai.svg';
import { TutorAIContext } from '../frontend';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

export default function FrontendAiAnswer( props ) {
	const { blockProps } = props;

	const blockPropsWithAdditionalClass = {
		...blockProps,
		className: classnames( blockProps.className, 'block-frontend' ),
	};

	const { isAiBusy } = useContext( TutorAIContext );

	if ( ! props.attributes?.message && ! isAiBusy ) {
		return <></>;
	}

	return (
		<div { ...blockPropsWithAdditionalClass }>
			{ props.attributes?.message ?? <div className="dot-typing"></div> }
			<TutorIcon
				fill={ blockProps.style?.color }
				stroke={ blockProps.style?.color }
			/>
		</div>
	);
}
