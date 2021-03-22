/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';


export default function Edit( { setAttributes, attributes } ) {
	const blockProps = useBlockProps();

    const postType = useSelect(
        ( select ) => select( 'core/editor' ).getCurrentPostType(),
        []
    );

	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	//console.log( meta);

	//Temporary work around for https://core.trac.wordpress.org/ticket/52787
	for ( const [ key, value ] of Object.entries( meta ) ) {
		if ( Array.isArray( value ) && 0 === value.length ) {
			delete meta[ key ];
		}
	}

    function updateMetaValue( key, value ) {
	    meta[ key ] = value;

        setMeta( { meta } );
    }

    return (
        <div { ...blockProps }>

	        {/* change all these to more specific UI components that match their data, e.g., dropdowns, etc*/}

	        <p>
	            <TextControl
	                label="duration"
	                value={ meta['duration'] }
	                onChange={ value => updateMetaValue( 'duration', value ) }
	            />
	            {/* hour minite seconds - have to present ui in sep fields, or can combine and still keep separate in db?
	            use <time> ? */}
			</p>

	        <p>
	            <TextControl
	                label="presenter_wporg_username"
	                value={ ( meta['presenter_wporg_username'] || [] ).join( ', ' ) }
	                onChange={ value => updateMetaValue( 'presenter_wporg_username', value.replace( ' ', '' ).split( ',' ) ) }
	            />
	            {/*
					- selectwoo multi? no b/c 12m users.
					- tags instead of freeform? no b/c already using postmeta. unless can keep in postmeta but use tag ui
	            */}
            </p>

	        <p>
	            Language
		        <select>
			        <option>
				        Esperantu
			        </option>
		        </select>
		        {/* selectWoo or the native G components corey's working on */}
	        </p>

	    <p>
		        Captions
	</p>

		<p>
		        Linked Quiz
		        <select>
			        <option>
				        Esperantu
			        </option>
		        </select>
		        {/* selectWoo or the native G components corey's working on */}
	</p>

	        <p>
		        <button>join a group discussion</button>
</p>

			<p>
			    You must agree to our <a href="%s">Code of Conduct</a> in order to participate
						{/*https://learn.wordpress.org/code-of-conduct/*/}
			</p>
        </div>

	    // translate strings
	    // make sure the html, css, etc for all ^ matches the front end, maybe make it DRY
	    // how are old posts affected, especially on the front end? need a flag to separate old from new?
	    // make sure to test all the optional fields on the front end
    );

	/*

	move old stuff here
	any changes to front end?
	make sure update all code that references this stuff - should be limited though b/c still storing in postmeta

	pr explain
		better UX b/c WYSIWYG, don't have to deal w/ expanding/collapsing inspector panels
		reduces the problems laid out in #195 by consoloading into a single block
		lets us add/edit future posts w/out having to change past posts (won't apply retoriactively)

	*/
}
