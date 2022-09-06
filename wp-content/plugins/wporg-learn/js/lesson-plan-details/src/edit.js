/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { Placeholder } from "@wordpress/components";

export default function Edit() {
	return (
		<Placeholder label={__("Lesson Plan Details", "wporg-learn")}>
			<p>
				{__(
					"This will be dynamically populated based on settings in the Lesson Plan Details meta box.",
					"wporg-learn"
				)}
			</p>
		</Placeholder>
	);
}
