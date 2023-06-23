/**
 * WordPress dependencies
 */
import {
	BlockControls,
	MediaPlaceholder,
	MediaReplaceFlow,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useMeta from './use-meta';

const ALLOWED_MEDIA_TYPES = [ 'image' ];

/**
 * Media hook.
 *
 * @return {Object} Media data and function to select media.
 */
const useMediaData = () => {
	const [ media = {}, setMedia ] = useMeta( '_media' );

	const onSelectMedia = ( value ) => {
		setMedia( { id: value.id, src: value.url } );
	};

	return {
		media,
		onSelectMedia,
	};
};

/**
 * Media settings component. It displays a button in the block toolbar.
 */
export const MediaSettings = () => {
	const { media, onSelectMedia } = useMediaData();

	return (
		<BlockControls group="other">
			<MediaReplaceFlow
				mediaId={ media.id }
				mediaURL={ media.src }
				allowedTypes={ ALLOWED_MEDIA_TYPES }
				accept="image/*"
				onSelect={ onSelectMedia }
				name={
					media.src
						? __( 'Replace Image', 'sensei-pro' )
						: __( 'Add Image', 'sensei-pro' )
				}
			/>
		</BlockControls>
	);
};

/**
 * Showcase media component.
 */
const Media = () => {
	const { media, onSelectMedia } = useMediaData();

	if ( media.src ) {
		return (
			<div
				role="img"
				aria-label={ __( 'Showcase illustration', 'sensei-pro' ) }
				className="sensei-showcase-card__image"
				style={ {
					backgroundImage: `url(${ media.src })`,
				} }
			></div>
		);
	}

	return (
		<MediaPlaceholder
			onSelect={ onSelectMedia }
			allowedTypes={ [ 'image' ] }
			multiple={ false }
			value={ media }
			labels={ {
				title: __( 'Select a course image', 'sensei-pro' ),
			} }
		/>
	);
};

export default Media;
