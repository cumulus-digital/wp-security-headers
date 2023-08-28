/**
 * Adopted from MocioF/No-unsafe-inline
 */
import parse from 'style-to-object';

const camelize = ( str ) =>
	str
		.split( '-' )
		.map( ( word, i ) =>
			i == 0 ? word : word[ 0 ].toUpperCase() + word.slice( 1 )
		)
		.join( '' );

const applyStyles = ( el, styles ) => {
	const parsed = parse( styles );
	Object.keys( parsed ).forEach(
		( key ) => ( el.style[ camelize( key ) ] = parsed( key ) )
	);
};

class MyRegExp extends RegExp {
	[ Symbol.split ]( str, limit ) {
		let result = RegExp.prototype[ Symbol.split ].call( this, str, limit );
		return result.map( ( x ) => '(' + x + ')' );
	}
}

/**
 * Replace Element.setAttribute for CSP-safe inline styles
 */
const _setAttribute = Element.prototype.setAttribute; // Save source of Elem.setAttribute funct
Element.prototype.setAttribute = function ( attr, val ) {
	if ( attr.toLowerCase() !== 'style' ) {
		return _setAttribute.apply( this, [ attr, val ] );
	}

	applyStyles( this, val );
};

if ( window.jQuery ) {
	window.jQuery.htmlPrefilter = function ( html ) {
		return ( html + '' ).replace( / style=/gi, ' data-wpsh_style=' );
	};
	const tags = document.querySelectorAll( '[data-wpsh_style]' );
	tags.forEach( ( tag ) => {
		const styles = tag.getAttribute( 'data-wpsh_style' );
		applyStyles( tag, styles );
	} );
}
