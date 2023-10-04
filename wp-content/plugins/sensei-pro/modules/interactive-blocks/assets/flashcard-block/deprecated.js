/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

// "Flip Card" text is not translated anymore in the saved version to avoid
// errors when changing the site language.
const v1 = {
	attributes: {},
	supports: {
		html: false,
		sensei: {
			blockId: true,
			frontend: true,
			required: true,
		},
	},
	save: ( { children, ...props } ) => {
		return (
			<div
				className={ classnames(
					'sensei-lms-flashcards__card-wrapper',
					props.className
				) }
			>
				<div className="sensei-lms-flip sensei-lms-flip--flipped-front">
					{ children }
					<button className="sensei-lms-flip__button" tabIndex={ 0 }>
						{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid -- Interaction provided by button */ }
						<a tabIndex={ -1 }>
							{
								// translators: verb + noun, refers to an action of flipping a card.
								__( 'Flip Card', 'sensei-pro' )
							}
						</a>
					</button>
				</div>
			</div>
		);
	},
};

const deprecated = [ v1 ];

export default deprecated;
