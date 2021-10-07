/**
 * External dependencies
 */
import {
	Redirect,
	Route,
	Switch,
	useRouteMatch,
	NavLink,
} from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { NoticeList } from '@ithemes/security-components';
import { usePages } from '../../page-registration';
import { Main, Navigation, Sidebar } from '../../components';
import './style.scss';

export default function Settings() {
	const pages = usePages();
	const { url, path } = useRouteMatch();

	return (
		<Switch>
			{ pages.map( ( { id, render: Component } ) => (
				<Route path={ `${ path }/:page(${ id })` } key={ id }>
					<Sidebar>
						<Navigation />
						<AdvancedNav url={ url } />
					</Sidebar>
					<Main>
						<NoticeList />
						<Component />
					</Main>
				</Route>
			) ) }

			<Route path={ url }>
				{ pages.length > 0 && (
					<Redirect
						to={ `${ url }/${
							pages.find( ( { priority } ) => priority !== false )
								.id
						}` }
					/>
				) }
				<Sidebar>
					<Navigation />
					<AdvancedNav url={ url } />
				</Sidebar>
				<Main />
			</Route>
		</Switch>
	);
}

function AdvancedNav( { url } ) {
	return (
		<ul className="itsec-settings-advanced-nav">
			<li>
				<NavLink
					to={ `${ url }/tools` }
					className="itsec-settings-advanced-nav--tools"
				>
					<span>{ __( 'Tools', 'better-wp-security' ) }</span>
				</NavLink>
			</li>
			<li>
				<NavLink
					to={ `${ url }/configure/advanced` }
					className="itsec-settings-advanced-nav--advanced"
				>
					<span>{ __( 'Advanced', 'better-wp-security' ) }</span>
				</NavLink>
			</li>
		</ul>
	);
}
