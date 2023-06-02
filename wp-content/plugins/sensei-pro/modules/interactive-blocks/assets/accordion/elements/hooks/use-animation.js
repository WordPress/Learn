/**
 * HeadingLevelDropdown props.
 *
 * @typedef UseAnimationResponse
 *
 * @property {string}  initial        Indicate the initial animation state
 * @property {boolean} animationState Indicate the current animation state
 * @property {Object}  variants       The Framer-motion Animation variations
 */

/**
 * Hook to manage the open/close animation.
 * It is using the maxHeight instead of height to avoid any calculation issue,
 * so if the calculated height > the expected height it will be ignored by the
 * rendering process.
 *
 * @param {Object}  ref    React ref to get the dom element reference and calculate the height properly
 * @param {boolean} isOpen Indicate if the state is open or close
 * @return {UseAnimationResponse} Auxiliary flags and animation variants
 */
const useAnimation = ( ref, isOpen ) => {
	const variants = {
		// + Extra to ensure support blocks that grows its height after interaction like the question block.
		open: () => ( {
			maxHeight: `${ ref.current?.scrollHeight + 100 }px`,
			transition: { ease: 'easeInOut', duration: 0.4 },
		} ),
		close: () => ( {
			maxHeight: `${
				ref.current?.querySelector( 'summary' )?.offsetHeight
			}px`,
			transition: { ease: 'easeOut', duration: 0.4 },
		} ),
	};

	return {
		initial: 'close',
		animationState: isOpen ? 'open' : 'close',
		variants,
	};
};

export default useAnimation;
