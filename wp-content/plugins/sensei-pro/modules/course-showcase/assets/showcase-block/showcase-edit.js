/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import { Spinner } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SiteInfo, { useSiteSettings } from './site-info';
import Media from './media';
import RichTextWithFocus from './rich-text-with-focus';
import Settings from './showcase-settings';
import useMeta from './use-meta';
import { CATEGORY_OPTIONS, STUDENTS_NUMBER } from './constants';
import useHandleResponse from './use-handle-response';

/**
 * Showcase edit component.
 */
const Edit = () => {
	const blockProps = useBlockProps( { className: 'test' } );
	const { siteTitle, siteLogo, isRequestingMediaItem } = useSiteSettings();

	const [ isPaid ] = useMeta( '_is_paid' );
	const [ title, setTitle ] = useMeta( '_title' );
	const [ excerpt, setExcerpt ] = useMeta( '_excerpt' );
	const [ category ] = useMeta( '_category' );
	useHandleResponse();

	const categoryLabel =
		category &&
		CATEGORY_OPTIONS.find( ( c ) => c.value === category ).label;

	let content = (
		<div className="sensei-showcase-card">
			{ <Media /> }
			<div className="sensei-showcase-card__body">
				<div className="sensei-showcase-card__badges">
					{ categoryLabel && (
						<div className="sensei-showcase-card__badge sensei-showcase-card__badge--category">
							{ categoryLabel }
						</div>
					) }
					<div
						className={ classnames( 'sensei-showcase-card__badge', {
							'sensei-showcase-card__badge--paid': isPaid,
							'sensei-showcase-card__badge--free': ! isPaid,
						} ) }
					>
						{ isPaid
							? __( 'Paid', 'sensei-pro' )
							: __( 'Free', 'sensei-pro' ) }
					</div>
				</div>
				<RichTextWithFocus
					className="sensei-showcase-card__title"
					focusClassName="sensei-showcase-card__title--focus"
					onChange={ setTitle }
					value={ title }
					placeholder={ __( 'Course title', 'sensei-pro' ) }
					disableLineBreaks
				/>
				{ ( siteTitle || siteLogo ) && (
					<SiteInfo siteTitle={ siteTitle } siteLogo={ siteLogo } />
				) }
				<RichTextWithFocus
					className="sensei-showcase-card__excerpt"
					focusClassName="sensei-showcase-card__excerpt--focus"
					onChange={ setExcerpt }
					value={ excerpt }
					placeholder={ __( 'Course excerpt', 'sensei-pro' ) }
					disableLineBreaks
				/>
				<div className="sensei-showcase-card__students">
					{ sprintf(
						/* translators: %s: Expression with the approximate number of students */
						__( '%s Students', 'sensei-pro' ),
						STUDENTS_NUMBER
					) }
				</div>
			</div>
		</div>
	);

	if ( isRequestingMediaItem ) {
		content = (
			<div className="sensei-showcase-loading">
				<Spinner />
			</div>
		);
	}

	return (
		<>
			<div { ...blockProps }>{ content }</div>
			<Settings />
		</>
	);
};

export default Edit;
