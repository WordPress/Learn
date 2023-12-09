/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
/**
 * External dependencies
 */
import classnames from 'classnames';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

const ALLOWED_BLOCKS = [ 'core/avatar' ];

const Edit = () => {
	const blockProps = useBlockProps();
	const placeHolder = __( "Student's answer", 'sensei-pro' );

	return (
		<div
			{ ...blockProps }
			className={ classnames(
				blockProps.className,
				'sensei-lms-interactive-block-tutor-ai__student-answer'
			) }
		>
			<div>
				<InnerBlocks
					templateLock={ true }
					template={ [
						[
							'core/avatar',
							{
								size: 32,
								style: {
									border: {
										radius: '500px',
									},
									cursor: 'default',
								},
								className: 'sensei-pro-tutor-ai__user-avatar',
							},
						],
					] }
					insertBlocksAfter={ false }
					allowedBlocks={ ALLOWED_BLOCKS }
					renderAppender={ false }
				></InnerBlocks>
				<span>{ placeHolder }</span>
			</div>
		</div>
	);
};

export default Edit;
