/**
 * External dependencies
 */
import { Router, Switch, Route, Redirect } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ErrorBoundary } from 'react-error-boundary';

/**
 * WordPress components
 */
import {
	SlotFillProvider,
	Popover,
	Flex,
	FlexBlock,
} from '@wordpress/components';
import { PluginArea } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import PageRegistration from './page-registration';
import Pages, { Onboard, Settings } from './pages';
import { ConfigContext } from './utils';
import { Main, Sidebar, ErrorRenderer } from './components';
import useSearchProviders from './search';
import './stores';
import './style.scss';

export default function App( {
	history,
	serverType,
	installType,
	onboardComplete,
} ) {
	useSearchProviders();
	const redirect = onboardComplete ? '/settings' : '/onboard';

	return (
		<div className="itsec-settings">
			<ConfigContext.Provider
				value={ { serverType, installType, onboardComplete } }
			>
				<Router history={ history }>
					<QueryParamProvider ReactRouterRoute={ Route }>
						<SlotFillProvider>
							<ErrorBoundary
								FallbackComponent={ GlobalErrorBoundary }
							>
								<PageRegistration>
									<Pages />
									<PluginArea />
									<Popover.Slot />
									<Switch>
										<Route
											path="/:root(settings)"
											component={ Settings }
										/>
										<Route
											path="/:root(onboard)"
											component={ Onboard }
										/>

										<Route path="/">
											<Redirect to={ redirect } />
											<Sidebar />
											<Main />
										</Route>
									</Switch>
								</PageRegistration>
							</ErrorBoundary>
						</SlotFillProvider>
					</QueryParamProvider>
				</Router>
			</ConfigContext.Provider>
		</div>
	);
}

function GlobalErrorBoundary( props ) {
	return (
		<Flex>
			<FlexBlock>
				<ErrorRenderer { ...props } />
			</FlexBlock>
		</Flex>
	);
}
