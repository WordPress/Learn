/**
 * WordPress dependencies
 */
import { applyFilters } from '@wordpress/hooks';
import { Spinner, Notice } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { useSenseiColorTheme } from 'sensei/assets/react-hooks/use-sensei-color-theme';
import Header from 'sensei/assets/home/header';
import { Col, Grid } from 'sensei/assets/home/grid';
import GetHelp from 'sensei/assets/home/sections/get-help';
import LatestNews from 'sensei/assets/home/sections/latest-news';
import 'sensei/assets/shared/data/api-fetch-preloaded-once';
import Notices from 'sensei/assets/home/notices';

const Main = () => {
	useSenseiColorTheme();
	const [ data, setData ] = useState( {} );
	const [ error, setError ] = useState( null );
	const [ isFetching, setIsFetching ] = useState( true );

	useEffect( () => {
		async function fetchAndSetData() {
			try {
				const remoteData = await apiFetch( {
					path: '/sensei-internal/v1/home',
					method: 'GET',
				} );
				setData( remoteData );
				setIsFetching( false );
			} catch ( exceptionError ) {
				setError( exceptionError );
				setIsFetching( false );
			}
		}
		fetchAndSetData();
	}, [] );

	let content = null;
	const notices = data?.notices ?? {};

	if ( isFetching ) {
		content = <Spinner />;
	} else if ( error ) {
		content = (
			<Col as="section" className="sensei-home__section" cols={ 12 }>
				<Notice status="error" isDismissible={ false }>
					{ __(
						'An error has occurred while fetching the data. Please try again later!',
						'sensei-pro'
					) }
					<br />
					{ __( 'Error details:', 'sensei-pro' ) } { error.message }
				</Notice>
			</Col>
		);
	} else {
		content = (
			<>
				{ data.help && data.help.length > 0 && (
					<Col
						as="section"
						className="sensei-home__section"
						cols={ 6 }
					>
						<GetHelp categories={ data.help } />
					</Col>
				) }

				{ data.news && data.news?.items.length > 0 && (
					<Col
						as="section"
						className="sensei-home__section"
						cols={ 6 }
					>
						<LatestNews data={ data.news } />
					</Col>
				) }
			</>
		);
	}

	const { dismissNoticesNonce } = window.sensei_home;

	/**
	 * Filters the component that will be injected on the top of the Sensei Home
	 *
	 * @since 1.8.0
	 * @param {JSX.Element} element The element to be injected
	 * @return {JSX.Element} Filtered element.
	 */
	const topRow = applyFilters( 'sensei.home.top', null );

	return (
		<>
			<Grid as="main" className="sensei-home">
				<Col as="section" className="sensei-home__section" cols={ 12 }>
					<Header />
				</Col>

				{ Object.keys( notices ).length > 0 ? (
					<Col
						as="section"
						className="sensei-home__section sensei-home__notices"
						cols={ 12 }
					>
						<Notices
							notices={ notices }
							dismissNonce={ dismissNoticesNonce }
						/>
					</Col>
				) : null }

				{ topRow }

				{ content }
			</Grid>
		</>
	);
};

export default Main;
