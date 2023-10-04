/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';

/**
 * Site settings hook.
 */
export const useSiteSettings = () =>
	useSelect( ( select ) => {
		const { canUser, getEntityRecord, getEditedEntityRecord } = select(
			coreStore
		);
		const canUserEdit = canUser( 'update', 'settings' );
		const siteSettings = getEditedEntityRecord( 'root', 'site' );
		const siteData = getEntityRecord( 'root', '__unstableBase' );

		const siteTitle = canUserEdit ? siteSettings?.title : siteData?.title;

		const siteLogoId = canUserEdit
			? siteSettings?.site_logo
			: siteData?.site_logo;
		const mediaItem =
			siteLogoId &&
			select( coreStore ).getMedia( siteLogoId, {
				context: 'view',
			} );
		const isRequestingMediaItem =
			siteLogoId &&
			! select( coreStore ).hasFinishedResolution( 'getMedia', [
				siteLogoId,
				{ context: 'view' },
			] );

		return {
			siteTitle: decodeEntities( siteTitle ),
			siteLogo: mediaItem?.source_url,
			isRequestingMediaItem,
		};
	}, [] );

/**
 * Site info component.
 *
 * @param {Object} props           Component props.
 * @param {string} props.siteTitle Site title.
 * @param {string} props.siteLogo  Site logo URL.
 */
const SiteInfo = ( { siteTitle, siteLogo } ) => (
	<div className="sensei-showcase-card__site">
		{ siteLogo && (
			<div
				role="img"
				aria-label={ __( 'Site logo', 'sensei-pro' ) }
				className="sensei-showcase-card__site-icon"
				style={ {
					backgroundImage: `url(${ siteLogo })`,
				} }
			></div>
		) }

		{ siteTitle && (
			<div className="sensei-showcase-card__site-name">{ siteTitle }</div>
		) }
	</div>
);

export default SiteInfo;
