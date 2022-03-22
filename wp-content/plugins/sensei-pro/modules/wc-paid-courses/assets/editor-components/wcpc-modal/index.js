/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { Modal, Button } from '@wordpress/components';
import { closeSmall } from '@wordpress/icons';

/**
 * @typedef ModalAction
 *
 * @property {string}  id          Action ID.
 * @property {string}  label       Action label.
 * @property {boolean} inverted    Inverted position.
 * @property {Object}  buttonProps Button props.
 */
/**
 * WCPC modal component.
 *
 * @param {Object}        props
 * @param {string}        props.className    Modal class name.
 * @param {string}        props.contentLabel Modal content label.
 * @param {string}        props.title        Modal title.
 * @param {string}        props.intro        Intro text.
 * @param {ModalAction[]} props.actions      Modal actions.
 * @param {Object}        props.formProps    Form props.
 * @param {Function}      props.onClose      Close callback.
 * @param {Object}        props.children     Modal content.
 */
const WCPCModal = ( {
	className,
	contentLabel,
	title,
	intro,
	actions,
	formProps,
	onClose,
	children,
} ) => {
	const Wrapper = formProps ? 'form' : Fragment;

	return (
		<Modal
			className={ classnames( className, 'sensei-wcpc-modal' ) }
			onRequestClose={ onClose }
			contentLabel={ contentLabel }
		>
			<header className="sensei-wcpc-modal__header">
				<h1 className="sensei-wcpc-modal__title">{ title }</h1>
				{ intro && (
					<p className="sensei-wcpc-modal__intro">{ intro }</p>
				) }
			</header>

			<Button
				className="sensei-wcpc-modal__close-button"
				onClick={ onClose }
				icon={ closeSmall }
				label={ __( 'Close modal', 'sensei-pro' ) }
			/>

			<Wrapper { ...( formProps || {} ) }>
				{ children }

				{ actions && (
					<ul className="sensei-wcpc-modal__buttons-list">
						{ actions.map(
							( { id, label, inverted, buttonProps } ) => (
								<li
									key={ id }
									className={ classnames( {
										'sensei-wcpc-modal__buttons-list__text-item': ! buttonProps,
										'sensei-wcpc-modal__buttons-list__inverted-position-item': inverted,
									} ) }
								>
									{ buttonProps ? (
										<Button { ...buttonProps }>
											{ label }
										</Button>
									) : (
										label
									) }
								</li>
							)
						) }
					</ul>
				) }
			</Wrapper>
		</Modal>
	);
};

export default WCPCModal;
