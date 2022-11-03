/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';

const EditorPlayerContext = createContext( undefined );

/**
 * Editor player provider.
 *
 * @param {Object} props       Component props.
 * @param {Object} props.value Player instance.
 */
export const EditorPlayerProvider = EditorPlayerContext.Provider;

/**
 * Hook to get the editor player from the context.
 *
 * @return {Object} Player instance.
 */
export const useContextEditorPlayer = () => useContext( EditorPlayerContext );

/**
 * Editor player context.
 */
export default EditorPlayerContext;
