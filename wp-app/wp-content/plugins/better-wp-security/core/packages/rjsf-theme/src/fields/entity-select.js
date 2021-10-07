/**
 * External dependencies
 */
import { utils } from '@rjsf/core';
import { createGlobalState } from 'react-hooks-global-state';
import { mapValues, keyBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { AsyncSelect, Markup } from '@ithemes/security-components';

const { getUiOptions } = utils;

const { useGlobalState } = createGlobalState( { cache: {} } );

export default function EntitySelectField( {
	uiSchema,
	schema,
	idSchema,
	name,
	formData,
	disabled,
	readonly,
	onChange,
} ) {
	const [ cache, setCache ] = useGlobalState( 'cache' );

	const options = getUiOptions( uiSchema );
	const {
		path,
		query = {},
		labelAttr,
		idAttr = 'id',
		searchArg = 'search',
	} = options;

	const id = idSchema.$id;
	const isMultiple = schema.type === 'array';
	const label = uiSchema[ 'ui:title' ] || schema.title || name;
	const description = uiSchema[ 'ui:description' ] || schema.description;

	const [ input, setInput ] = useState( '' );
	const loader = useLoader(
		path,
		query,
		labelAttr,
		idAttr,
		searchArg,
		cache,
		setCache
	);
	let value;

	if ( isMultiple ) {
		value = ( formData || [] )
			.filter( ( item ) => item !== undefined )
			.map( ( itemId ) => ( {
				value: itemId,
				label: cache[ path ]?.[ itemId ] || itemId,
			} ) );
	} else if ( formData ) {
		value = {
			value: formData,
			label: cache[ path ]?.[ formData ] || formData,
		};
	}

	return (
		<BaseControl
			className="itsec-rjsf-entity-select"
			label={ label }
			help={ <Markup noWrap content={ description } /> }
			id={ id }
		>
			<AsyncSelect
				aria-label={ label }
				aria-describedby={ description ? id + '__help' : undefined }
				classNamePrefix="itsec-rjsf-entity-select-control"
				inputId={ id }
				isDisabled={ disabled || readonly }
				isMulti={ isMultiple }
				cacheOptions
				defaultOptions
				loadOptions={ loader }
				value={ value }
				onChange={ ( nextValue ) =>
					onChange(
						isMultiple
							? ( nextValue || [] ).map( ( item ) => item.value )
							: nextValue?.value
					)
				}
				inputValue={ input }
				onInputChange={ setInput }
			/>
		</BaseControl>
	);
}

function useLoader(
	path,
	query,
	labelAttr,
	idAttr,
	searchArg,
	cache,
	setCache
) {
	return useCallback(
		( search ) => {
			return apiFetch( {
				path: addQueryArgs( path, { ...query, [ searchArg ]: search } ),
			} )
				.then( ( response ) =>
					response.map( ( item ) => ( {
						value: item[ idAttr ],
						label: item[ labelAttr ],
					} ) )
				)
				.then( ( items ) => {
					setCache( {
						...cache,
						[ path ]: {
							...( cache[ path ] || {} ),
							...mapValues( keyBy( items, 'value' ), 'label' ),
						},
					} );

					return items;
				} );
		},
		[ path, query, labelAttr, idAttr, searchArg, cache ]
	);
}
