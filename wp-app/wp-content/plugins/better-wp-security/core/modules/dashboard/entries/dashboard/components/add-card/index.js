/**
 * External dependencies
 */
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { compose, withState } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { SelectControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

const buildCardOptions = memize( ( cards ) => [
	{ value: '', label: '' },
	...cards.map( ( card ) => ( { value: card.href, label: card.title } ) ),
] );

function AddCard( { cards, addCard, isAdding, selected, setState } ) {
	const onSubmit = ( e ) => {
		e.preventDefault();

		addCard( selected, {} );
	};

	const onChange = ( newSelected ) => setState( { selected: newSelected } );

	return (
		<form onSubmit={ onSubmit } className="itsec-add-card">
			<SelectControl
				label={ __( 'Choose a Card', 'better-wp-security' ) }
				value={ selected }
				onChange={ onChange }
				options={ buildCardOptions( cards ) }
			/>
			<Button
				isPrimary
				type="submit"
				isBusy={ isAdding }
				disabled={ isAdding }
			>
				{ __( 'Add Card', 'better-wp-security' ) }
			</Button>
		</form>
	);
}

export default compose( [
	withState( { selected: '' } ),
	withSelect( ( select, props ) => ( {
		cards: select( 'ithemes-security/dashboard' ).getDashboardAddableCards(
			props.dashboardId
		),
		isAdding: select( 'ithemes-security/dashboard' ).isAddingCard(
			props.selected,
			{}
		),
	} ) ),
	withDispatch( ( dispatch ) => ( {
		addCard( ep, card ) {
			return dispatch( 'ithemes-security/dashboard' ).addDashboardCard(
				ep,
				card
			);
		},
	} ) ),
] )( AddCard );
