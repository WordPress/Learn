/**
 * External dependencies
 */
import interpolateComponents from '@automattic/interpolate-components';

/**
 * WordPress dependencies
 */
import { ExternalLink, Notice } from '@wordpress/components';
import {
	PluginPrePublishPanel,
	PluginPostStatusInfo,
} from '@wordpress/edit-post';
import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default () => {
	const Instructions = (
		<p>
			{ createInterpolateElement(
				__(
					'To submit your course listing to the Sensei Showcase, click on the <strong>Submit for Review</strong> button above. The Sensei team will review your submission in the next few days.',
					'sensei-pro'
				),
				{
					strong: <strong />,
				}
			) }
		</p>
	);

	const TermsConditionsNotice = (
		<Notice
			isDismissible={ false }
			status="warning"
			className="sensei-pro-showcase-terms-conditions__notice"
		>
			{ interpolateComponents( {
				mixedString: __(
					'By submitting this course to the Sensei Showcase you agree to the {{link}}SenseiLMS.com Terms & Conditions{{/link}}.',
					'sensei-pro'
				),
				components: {
					link: <ExternalLink href="https://senseilms.com/terms/" />,
				},
			} ) }
		</Notice>
	);

	return (
		<>
			<PluginPrePublishPanel>
				{ Instructions }
				{ TermsConditionsNotice }
			</PluginPrePublishPanel>
			<PluginPostStatusInfo>
				{ TermsConditionsNotice }
			</PluginPostStatusInfo>
		</>
	);
};
