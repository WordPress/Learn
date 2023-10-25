/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { BEM } from '../helpers/bem';

/**
 * External dependencies
 */
import { motion, AnimatePresence } from 'framer-motion';

const circleVariants = {
	initial: {
		scale: 0,
	},
	enter: {
		scale: [ 0, 0.6, 1.3, 1 ],
		rotate: 360,
		transition: {
			scale: {
				duration: 0.3,
			},
			rotate: {
				repeat: Infinity,
				duration: 0.8,
				ease: 'linear',
			},
		},
	},
};

const hatVariants = {
	initial: {
		scale: 0,
	},
	enter: {
		scale: [ 0, 0.6, 1.3, 1 ],
		transition: {
			scale: {
				delay: 0.15,
				duration: 0.3,
			},
		},
	},
};

const wrapperVariants = {
	enter: {
		backgroundColor: '#43AF99',
		transition: { duration: 0.2 },
	},
	exit: {
		opacity: 0,
		transition: { delay: 0.3 },
	},
};

const iconVariation = {
	exit: {
		scale: 0,
		transition: { duration: 0.3 },
	},
};

const info = {
	initial: {
		opacity: 0,
		y: 20,
	},
	enter: {
		opacity: 1,
		y: 0,
		transition: {
			duration: 0.3,
			delay: 0.4,
		},
	},

	exit: {
		y: 20,
		opacity: 0,
	},
};

export const Loading = ( { isLoading } ) => {
	return (
		<AnimatePresence>
			{ isLoading && (
				<motion.div
					className={ BEM( { e: 'loading' } ) }
					aria-hidden="true"
					variants={ wrapperVariants }
					animate="enter"
					exit="exit"
					key="loading"
				>
					<motion.svg
						width="57"
						height="56"
						viewBox="0 0 57 56"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						key="icon"
						animate="enter"
						exit="exit"
						variants={ iconVariation }
					>
						<motion.path
							fillRule="evenodd"
							clipRule="evenodd"
							d="M29.3442 16.4836C28.8917 16.2825 28.3752 16.2825 27.9227 16.4836L15.8635 21.8433C15.2315 22.1241 14.8242 22.7508 14.8242 23.4424C14.8242 24.134 15.2315 24.7607 15.8635 25.0416L20.1836 26.9616V34.8022C20.1836 35.3931 20.4818 35.9441 20.9764 36.2673L21.9336 34.8022C20.9764 36.2673 20.9767 36.2674 20.9769 36.2676L20.9775 36.268L20.979 36.2689L20.983 36.2715L20.9951 36.2794L21.0355 36.3053C21.0696 36.3271 21.1178 36.3576 21.1787 36.3955C21.3005 36.4712 21.4741 36.5772 21.6898 36.7033C22.1196 36.9546 22.724 37.2906 23.424 37.6293C24.7537 38.2728 26.6921 39.0594 28.4769 39.0829C30.3262 39.1072 32.3447 38.3172 33.7293 37.6647C34.4584 37.3211 35.0885 36.9779 35.5367 36.7205C35.7615 36.5914 35.9425 36.4828 36.0696 36.405C36.1331 36.3661 36.1833 36.3349 36.2188 36.3125L36.261 36.2859L36.2736 36.2778L36.2777 36.2752L36.2792 36.2742L36.2798 36.2738C36.2801 36.2736 36.2803 36.2735 35.3327 34.8022L36.2803 36.2735C36.7805 35.9513 37.0827 35.3972 37.0827 34.8022V26.9619L41.4034 25.0416C42.0354 24.7607 42.4427 24.134 42.4427 23.4424C42.4427 22.7508 42.0354 22.1241 41.4034 21.8433L29.3442 16.4836ZM33.5827 28.5175L29.3442 30.4013C28.8917 30.6024 28.3752 30.6024 27.9227 30.4013L23.6836 28.5172V33.8128C24.029 34.0093 24.4638 34.2442 24.9486 34.4788C26.2151 35.0917 27.5598 35.5705 28.5229 35.5832C29.519 35.5963 30.9168 35.1209 32.2373 34.4986C32.7549 34.2547 33.2181 34.009 33.5827 33.805V28.5175ZM28.6335 26.887L20.8831 23.4424L28.6335 19.9978L36.3838 23.4424L28.6335 26.887Z"
							fill="#0E0A1A"
							variants={ hatVariants }
							animate="enter"
							key="hat"
						/>
						<motion.path
							d="M8.43308 27.7666C8.43308 16.6841 17.4172 7.69992 28.4997 7.69992M48.5664 27.7666C48.5664 38.8491 39.5823 47.8333 28.4997 47.8333"
							stroke="#0E0A1A"
							strokeWidth="3.5"
							strokeLinecap="round"
							variants={ circleVariants }
							animate="enter"
							key="circle"
						/>
					</motion.svg>
					<motion.p
						key="info"
						variants={ info }
						animate="enter"
						exit="exit"
						initial="initial"
					>
						{ __(
							'AI is analyzing your text. This may take a few moments, up to 4 minutes.',
							'sensei-pro'
						) }
					</motion.p>
				</motion.div>
			) }
		</AnimatePresence>
	);
};
