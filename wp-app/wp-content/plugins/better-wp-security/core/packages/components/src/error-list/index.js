/**
 * Internal dependencies
 */
import { transformApiErrorToList } from '@ithemes/security-utils';
import { MessageList } from '../';

export default function ErrorList( {
	errors = [],
	apiError,
	schemaError,
	title,
	className,
} ) {
	const all = [
		...errors,
		...transformApiErrorToList( apiError ),
		...( schemaError || [] ).map( ( error ) => error.stack ),
	];

	if ( ! all.length ) {
		return null;
	}

	return (
		<MessageList
			messages={ all }
			title={ title }
			className={ className }
			type="error"
		/>
	);
}
