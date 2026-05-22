/* global activityKitStats, Chart */

( function () {
	const { restUrl, nonce } = activityKitStats;

	let chart = null;

	const filterMetric = document.getElementById( 'ak-filter-metric' );
	const filterRange = document.getElementById( 'ak-filter-range' );
	const filterKit = document.getElementById( 'ak-filter-kit' );
	const tableBody = document.getElementById( 'ak-stats-table-body' );
	const chartCanvas = document.getElementById( 'ak-stats-chart' );

	if (
		! filterMetric ||
		! filterRange ||
		! filterKit ||
		! tableBody ||
		! chartCanvas
	) {
		return;
	}

	function getParams() {
		const params = new URLSearchParams( window.location.search );
		return {
			metric: filterMetric.value || params.get( 'metric' ) || 'both',
			range: filterRange.value || params.get( 'range' ) || 'all',
			kit: filterKit.value || params.get( 'kit' ) || '',
		};
	}

	function initFromUrl() {
		const params = new URLSearchParams( window.location.search );
		if ( params.get( 'metric' ) ) {
			filterMetric.value = params.get( 'metric' );
		}
		if ( params.get( 'range' ) ) {
			filterRange.value = params.get( 'range' );
		}
		if ( params.get( 'kit' ) ) {
			filterKit.value = params.get( 'kit' );
		}
	}

	function pushState( p ) {
		const url = new URL( window.location.href );
		Object.entries( p ).forEach( ( [ k, v ] ) => {
			if ( v ) {
				url.searchParams.set( k, v );
			} else {
				url.searchParams.delete( k );
			}
		} );
		window.history.pushState( {}, '', url );
	}

	async function fetchStats( params ) {
		const url = new URL( restUrl );
		url.searchParams.set( 'metric', params.metric );
		url.searchParams.set( 'range', params.range );
		if ( params.kit ) {
			url.searchParams.set( 'kit', params.kit );
		}

		const response = await fetch( url.toString(), {
			headers: { 'X-WP-Nonce': nonce },
		} );

		if ( ! response.ok ) {
			throw new Error( 'Failed to fetch stats.' );
		}

		return response.json();
	}

	function renderTable( data, metric ) {
		if ( ! data.length ) {
			tableBody.innerHTML =
				'<tr><td colspan="3">No data found.</td></tr>';
			return;
		}

		tableBody.innerHTML = data
			.map(
				( row ) => `
			<tr>
				<td>${ row.title }</td>
				<td>${ 'downloads' !== metric ? row.views ?? '—' : '—' }</td>
				<td>${ 'views' !== metric ? row.downloads ?? '—' : '—' }</td>
			</tr>
		`
			)
			.join( '' );
	}

	function renderChart( data, metric ) {
		const labels = data.map( ( r ) => r.title );
		const datasets = [];

		if ( 'downloads' !== metric ) {
			datasets.push( {
				label: 'Views',
				data: data.map( ( r ) => r.views ?? 0 ),
				backgroundColor: 'rgba(0, 115, 170, 0.6)',
			} );
		}

		if ( 'views' !== metric ) {
			datasets.push( {
				label: 'Downloads',
				data: data.map( ( r ) => r.downloads ?? 0 ),
				backgroundColor: 'rgba(0, 163, 42, 0.6)',
			} );
		}

		if ( chart ) {
			chart.destroy();
		}

		chart = new Chart( chartCanvas, {
			type: 'bar',
			data: { labels, datasets },
			options: {
				responsive: true,
				plugins: {
					legend: { position: 'top' },
				},
				scales: {
					y: { beginAtZero: true, ticks: { stepSize: 1 } },
				},
			},
		} );
	}

	async function update() {
		const params = getParams();
		pushState( params );

		tableBody.innerHTML = '<tr><td colspan="3">Loading…</td></tr>';

		try {
			const data = await fetchStats( params );
			renderChart( data, params.metric );
			renderTable( data, params.metric );
		} catch ( e ) {
			tableBody.innerHTML = `<tr><td colspan="3">Error loading stats: ${ e.message }</td></tr>`;
		}
	}

	[ filterMetric, filterRange, filterKit ].forEach( ( el ) => {
		el.addEventListener( 'change', update );
	} );

	initFromUrl();
	update();
} )();
