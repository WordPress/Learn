/**
 * Internal dependencies
 */
import { registerBlockFrontend } from '../shared/block-frontend';
import { Flip } from './flip';
import { CompletedStatus } from '../shared/supports-required/elements';
import { CardWrapper } from './elements';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

registerBlockFrontend( {
	name: 'sensei-pro/flashcard',
	run: function FlashcardRun( { children, attributes, setAttributes } ) {
		const setCompleted = ( completed ) => setAttributes( { completed } );

		return (
			<CardWrapper>
				{ attributes.required && (
					<CompletedStatus
						message={ __(
							'Required - Flip the card to complete.',
							'sensei-pro'
						) }
						className="sensei-lms-flashcards__completed-status"
						completed={ !! attributes.completed }
					/>
				) }
				<Flip setCompleted={ setCompleted }>{ children }</Flip>
			</CardWrapper>
		);
	},
} );
