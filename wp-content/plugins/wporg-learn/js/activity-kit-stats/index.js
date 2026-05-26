/* global activityKitStats, Chart */

( function () {
	const { restUrl, nonce } = activityKitStats;

	// ── Constants ──
	const CHART_PAGE = 8;

	// ── State ──
	let allData = [];
	let chart = null;
	let activeMetric = 'both';
	let activeRange = 'all';
	let activeKit = '';
	let sortCol = 'views';
	let sortDir = 'desc';
	let chartOffset = 0;
	let customFrom = '';
	let customTo = '';

	// ── DOM refs ──
	const filterKit = document.getElementById( 'ak-filter-kit' );
	const tableBody = document.getElementById( 'ak-stats-table-body' );
	const chartCanvas = document.getElementById( 'ak-stats-chart' );

	if ( ! filterKit || ! tableBody || ! chartCanvas ) {
		return;
	}

	const summaryViews = document.getElementById( 'ak-summary-views' );
	const summaryDownloads = document.getElementById( 'ak-summary-downloads' );
	const summaryRate = document.getElementById( 'ak-summary-rate' );
	const boxKits = document.getElementById( 'ak-box-kits' );
	const boxViews = document.getElementById( 'ak-box-views' );
	const boxDownloads = document.getElementById( 'ak-box-downloads' );
	const boxRate = document.getElementById( 'ak-box-rate' );
	const totalKits = document.getElementById( 'ak-total-kits' );
	const chartTitle = document.getElementById( 'ak-chart-title' );
	const chartSubtitle = document.getElementById( 'ak-chart-subtitle' );
	const legendViews = document.getElementById( 'ak-legend-views' );
	const legendDownloads = document.getElementById( 'ak-legend-downloads' );
	const tableSubtitle = document.getElementById( 'ak-table-subtitle' );
	const backLinkBar = document.getElementById( 'ak-back-link-bar' );
	const kitBanner = document.getElementById( 'ak-kit-banner' );
	const kitBannerName = document.getElementById( 'ak-kit-banner-name' );
	const thViews = document.getElementById( 'ak-th-views' );
	const thDownloads = document.getElementById( 'ak-th-downloads' );
	const exportBtn = document.getElementById( 'ak-export-csv' );
	const customRangePicker = document.getElementById(
		'ak-custom-range-picker'
	);
	const dateFromInput = document.getElementById( 'ak-date-from' );
	const dateToInput = document.getElementById( 'ak-date-to' );
	const applyCustomRange = document.getElementById( 'ak-apply-custom-range' );
	const chartSliderWrap = document.getElementById( 'ak-chart-slider-wrap' );
	const chartSlider = document.getElementById( 'ak-chart-slider' );
	const chartSliderLabel = document.getElementById( 'ak-chart-slider-label' );

	const metricBtns = document.querySelectorAll( '[data-ak-metric]' );
	const rangeBtns = document.querySelectorAll( '[data-ak-range]' );

	// ── Helpers ──
	function fmt( num ) {
		return ( num ?? 0 ).toLocaleString();
	}

	function formatDate( dateStr ) {
		if ( ! dateStr ) {
			return '—';
		}
		const date = new Date( dateStr + 'T00:00:00' );
		return date.toLocaleDateString( 'en-US', {
			month: 'short',
			day: 'numeric',
			year: 'numeric',
		} );
	}

	function escHtml( s ) {
		return String( s )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	function rangeLabel() {
		const labels = {
			'7d': 'Last 7 days',
			'30d': 'Last 30 days',
			'90d': 'Last 90 days',
			all: 'All time',
		};
		if ( activeRange === 'custom' && customFrom && customTo ) {
			return customFrom + ' – ' + customTo;
		}
		return labels[ activeRange ] || 'All time';
	}

	const metricLabel = {
		both: 'Views vs. Downloads',
		views: 'Views',
		downloads: 'Downloads',
	};

	// ── Fetch ──
	async function fetchStats() {
		const url = new URL( restUrl );
		url.searchParams.set( 'metric', 'both' );

		if ( activeRange === 'custom' ) {
			url.searchParams.set( 'range', 'custom' );
			if ( customFrom ) {
				url.searchParams.set( 'date_from', customFrom );
			}
			if ( customTo ) {
				url.searchParams.set( 'date_to', customTo );
			}
		} else {
			url.searchParams.set( 'range', activeRange );
		}

		if ( activeKit ) {
			url.searchParams.set( 'kit', activeKit );
		} else {
			url.searchParams.delete( 'kit' );
		}

		const resp = await fetch( url.toString(), {
			headers: { 'X-WP-Nonce': nonce },
		} );
		if ( ! resp.ok ) {
			throw new Error( 'Failed to fetch stats.' );
		}
		return resp.json();
	}

	// ── Summary ──
	function updateSummary( data ) {
		const isSingle = !! activeKit;
		const totalV = data.reduce( ( s, r ) => s + ( r.views ?? 0 ), 0 );
		const totalD = data.reduce( ( s, r ) => s + ( r.downloads ?? 0 ), 0 );
		const rate =
			totalV > 0 ? ( ( totalD / totalV ) * 100 ).toFixed( 1 ) + '%' : '—';

		if ( summaryViews ) {
			summaryViews.textContent = fmt( totalV );
		}
		if ( summaryDownloads ) {
			summaryDownloads.textContent = fmt( totalD );
		}
		if ( summaryRate ) {
			summaryRate.textContent = rate;
		}
		if ( totalKits ) {
			totalKits.textContent = isSingle ? '1' : data.length;
		}

		if ( boxKits ) {
			boxKits.style.display = isSingle ? 'none' : '';
		}
		if ( boxViews ) {
			boxViews.style.display = activeMetric === 'downloads' ? 'none' : '';
		}
		if ( boxDownloads ) {
			boxDownloads.style.display = activeMetric === 'views' ? 'none' : '';
		}
		if ( boxRate ) {
			boxRate.style.display = isSingle ? '' : 'none';
		}
	}

	// ── Chart ──
	function initChart() {
		const ctx = chartCanvas.getContext( '2d' );
		chart = new Chart( ctx, {
			type: 'bar',
			data: { labels: [], datasets: [] },
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: ( c ) =>
								' ' +
								c.dataset.label +
								': ' +
								c.parsed.y.toLocaleString(),
						},
					},
				},
				scales: {
					x: {
						grid: { display: false },
						ticks: {
							font: { size: 11 },
							color: '#646970',
							maxRotation: 30,
						},
						border: { color: '#c3c4c7' },
					},
					y: {
						beginAtZero: true,
						grid: { color: '#f0f0f1' },
						ticks: { font: { size: 11 }, color: '#646970' },
						border: { color: '#c3c4c7' },
					},
				},
			},
		} );
	}

	function renderChart( data ) {
		if ( ! chart ) {
			return;
		}

		const total = data.length;
		const paged = total > CHART_PAGE;
		const sliced = paged
			? data.slice( chartOffset, chartOffset + CHART_PAGE )
			: data;

		// Slider visibility and state.
		if ( chartSliderWrap ) {
			chartSliderWrap.classList.toggle( 'is-visible', paged );
		}
		if ( paged && chartSlider ) {
			const maxOffset = Math.max( 0, total - CHART_PAGE );
			chartSlider.max = maxOffset;
			chartSlider.value = chartOffset;
		}
		if ( paged && chartSliderLabel ) {
			const end = Math.min( chartOffset + CHART_PAGE, total );
			chartSliderLabel.textContent =
				chartOffset + 1 + '–' + end + ' of ' + total;
		}

		const labels = sliced.map( ( r ) =>
			r.title.length > 20 ? r.title.slice( 0, 18 ) + '…' : r.title
		);
		const datasets = [];

		if ( activeMetric !== 'downloads' ) {
			datasets.push( {
				label: 'Views',
				backgroundColor: '#3858e9',
				barPercentage: 0.75,
				categoryPercentage: 0.7,
				data: sliced.map( ( r ) => r.views ?? 0 ),
			} );
		}
		if ( activeMetric !== 'views' ) {
			datasets.push( {
				label: 'Downloads',
				backgroundColor: '#9fb1ff',
				barPercentage: 0.75,
				categoryPercentage: 0.7,
				data: sliced.map( ( r ) => r.downloads ?? 0 ),
			} );
		}

		chart.data.labels = labels;
		chart.data.datasets = datasets;
		chart.update();
	}

	// ── Table ──
	function renderTable( data ) {
		if ( ! data.length ) {
			tableBody.innerHTML =
				'<tr><td colspan="5">No data found.</td></tr>';
			return;
		}

		const sorted = [ ...data ].sort( ( a, b ) => {
			if ( sortCol === 'title' ) {
				const na = a.title.toLowerCase();
				const nb = b.title.toLowerCase();
				if ( sortDir === 'asc' ) {
					if ( na < nb ) {
						return -1;
					}
					if ( na > nb ) {
						return 1;
					}
					return 0;
				}
				if ( nb < na ) {
					return -1;
				}
				if ( nb > na ) {
					return 1;
				}
				return 0;
			}
			if ( sortCol === 'updated' ) {
				return sortDir === 'asc'
					? ( a.updated || '' ).localeCompare( b.updated || '' )
					: ( b.updated || '' ).localeCompare( a.updated || '' );
			}
			let va;
			if ( sortCol === 'views' ) {
				va = a.views ?? 0;
			} else if ( sortCol === 'downloads' ) {
				va = a.downloads ?? 0;
			} else {
				va =
					( a.views ?? 0 ) > 0
						? ( a.downloads ?? 0 ) / ( a.views ?? 0 )
						: 0;
			}
			let vb;
			if ( sortCol === 'views' ) {
				vb = b.views ?? 0;
			} else if ( sortCol === 'downloads' ) {
				vb = b.downloads ?? 0;
			} else {
				vb =
					( b.views ?? 0 ) > 0
						? ( b.downloads ?? 0 ) / ( b.views ?? 0 )
						: 0;
			}
			return sortDir === 'asc' ? va - vb : vb - va;
		} );

		tableBody.innerHTML = '';
		sorted.forEach( ( row ) => {
			const v = row.views ?? 0;
			const d = row.downloads ?? 0;
			const rate = v > 0 ? ( ( d / v ) * 100 ).toFixed( 1 ) + '%' : '—';
			const isSelected = row.slug === activeKit;
			const tr = document.createElement( 'tr' );
			if ( isSelected ) {
				tr.classList.add( 'is-selected' );
			}

			const viewsClass =
				'ak-col-number' +
				( activeMetric === 'downloads' ? ' ak-hidden-col' : '' );
			const dlClass =
				'ak-col-number' +
				( activeMetric === 'views' ? ' ak-hidden-col' : '' );

			tr.innerHTML = `
				<td><a href="#" data-slug="${ escHtml( row.slug ) }">${ escHtml(
					row.title
				) }</a></td>
				<td class="${ viewsClass }">${ fmt( v ) }</td>
				<td class="${ dlClass }">${ fmt( d ) }</td>
				<td class="ak-col-number">${ rate }</td>
				<td>${ formatDate( row.updated ) }</td>
			`;

			tr.querySelector( 'a' ).addEventListener( 'click', ( e ) => {
				e.preventDefault();
				setKit( e.currentTarget.dataset.slug );
			} );
			tr.addEventListener( 'click', ( e ) => {
				if ( e.target.tagName !== 'A' ) {
					setKit( row.slug );
				}
			} );

			tableBody.appendChild( tr );
		} );

		// Update sort arrows.
		document
			.querySelectorAll( '#ak-stats-table thead th' )
			.forEach( ( th ) => {
				const col = th.dataset.col;
				const arrow = th.querySelector( '.ak-sort-arrow' );
				th.classList.remove( 'is-sorted' );
				if ( arrow ) {
					arrow.textContent = '';
				}
				if ( col === sortCol && arrow ) {
					th.classList.add( 'is-sorted' );
					arrow.textContent = sortDir === 'asc' ? ' ↑' : ' ↓';
				}
			} );
	}

	// ── UI state ──
	function updateUI() {
		const isSingle = !! activeKit;
		const kitObj = isSingle
			? allData.find( ( r ) => r.slug === activeKit )
			: null;

		if ( chartTitle ) {
			chartTitle.textContent =
				metricLabel[ activeMetric ] +
				' — ' +
				( isSingle && kitObj ? kitObj.title : 'All Kits' );
		}
		if ( chartSubtitle ) {
			chartSubtitle.textContent = rangeLabel();
		}

		if ( legendViews ) {
			legendViews.style.display =
				activeMetric === 'downloads' ? 'none' : '';
		}
		if ( legendDownloads ) {
			legendDownloads.style.display =
				activeMetric === 'views' ? 'none' : '';
		}

		if ( thViews ) {
			thViews.classList.toggle(
				'ak-hidden-col',
				activeMetric === 'downloads'
			);
		}
		if ( thDownloads ) {
			thDownloads.classList.toggle(
				'ak-hidden-col',
				activeMetric === 'views'
			);
		}

		if ( backLinkBar ) {
			backLinkBar.classList.toggle( 'is-visible', isSingle );
		}
		if ( kitBanner ) {
			kitBanner.classList.toggle( 'is-visible', isSingle );
			if ( isSingle && kitObj && kitBannerName ) {
				kitBannerName.textContent = kitObj.title;
			}
		}

		if ( tableSubtitle ) {
			tableSubtitle.textContent = isSingle
				? 'Showing single kit'
				: 'Click a row to drill into a single kit';
		}
	}

	// ── Main render ──
	async function render() {
		tableBody.innerHTML = '<tr><td colspan="5">Loading…</td></tr>';

		try {
			allData = await fetchStats();
			const data = activeKit
				? allData.filter( ( r ) => r.slug === activeKit )
				: allData;
			updateSummary( data );
			updateUI();
			renderChart( data );
			renderTable( data );
		} catch ( e ) {
			tableBody.innerHTML = `<tr><td colspan="5">Error loading stats: ${ escHtml(
				e.message
			) }</td></tr>`;
		}
	}

	// ── Setters ──
	function setMetric( m ) {
		activeMetric = m;
		metricBtns.forEach( ( b ) =>
			b.classList.toggle( 'is-active', b.dataset.akMetric === m )
		);
		const data = activeKit
			? allData.filter( ( r ) => r.slug === activeKit )
			: allData;
		updateSummary( data );
		updateUI();
		renderChart( data );
		renderTable( data );
	}

	function setRange( r ) {
		activeRange = r;
		rangeBtns.forEach( ( b ) =>
			b.classList.toggle( 'is-active', b.dataset.akRange === r )
		);

		const isCustom = r === 'custom';
		if ( customRangePicker ) {
			customRangePicker.classList.toggle( 'is-visible', isCustom );
		}

		if ( ! isCustom ) {
			render();
		}
	}

	function setKit( slug ) {
		activeKit = slug === activeKit ? '' : slug;
		chartOffset = 0;
		if ( filterKit ) {
			filterKit.value = activeKit;
		}
		render();
	}

	function resetKit() {
		activeKit = '';
		chartOffset = 0;
		if ( filterKit ) {
			filterKit.value = '';
		}
		render();
	}

	// ── Export CSV ──
	function exportCSV() {
		const data = activeKit
			? allData.filter( ( r ) => r.slug === activeKit )
			: allData;
		const rows = [
			[
				'Kit Name',
				'Views',
				'Downloads',
				'Download Rate',
				'Last Updated',
			],
		];
		data.forEach( ( r ) => {
			const v = r.views ?? 0;
			const d = r.downloads ?? 0;
			const rate = v > 0 ? ( ( d / v ) * 100 ).toFixed( 1 ) + '%' : '0%';
			rows.push( [ r.title, v, d, rate, r.updated || '' ] );
		} );
		const csv = rows
			.map( ( row ) =>
				row
					.map( ( c ) => `"${ String( c ).replace( /"/g, '""' ) }"` )
					.join( ',' )
			)
			.join( '\n' );
		const blob = new Blob( [ csv ], { type: 'text/csv' } );
		const url = URL.createObjectURL( blob );
		const a = document.createElement( 'a' );
		a.href = url;
		a.download = 'activity-kit-stats.csv';
		a.click();
		URL.revokeObjectURL( url );
	}

	// ── Event listeners ──
	metricBtns.forEach( ( btn ) => {
		btn.addEventListener( 'click', () =>
			setMetric( btn.dataset.akMetric )
		);
	} );

	rangeBtns.forEach( ( btn ) => {
		btn.addEventListener( 'click', () => setRange( btn.dataset.akRange ) );
	} );

	filterKit.addEventListener( 'change', () => {
		activeKit = filterKit.value;
		chartOffset = 0;
		render();
	} );

	if ( applyCustomRange ) {
		applyCustomRange.addEventListener( 'click', () => {
			customFrom = dateFromInput ? dateFromInput.value : '';
			customTo = dateToInput ? dateToInput.value : '';
			if ( customFrom && customTo ) {
				render();
			}
		} );
	}

	const clearCustomRange = document.getElementById( 'ak-clear-custom-range' );
	if ( clearCustomRange ) {
		clearCustomRange.addEventListener( 'click', () => {
			customFrom = '';
			customTo = '';
			if ( dateFromInput ) {
				dateFromInput.value = '';
			}
			if ( dateToInput ) {
				dateToInput.value = '';
			}
			setRange( 'all' );
		} );
	}

	if ( chartSlider ) {
		chartSlider.addEventListener( 'input', () => {
			chartOffset = parseInt( chartSlider.value, 10 );
			const data = activeKit
				? allData.filter( ( r ) => r.slug === activeKit )
				: allData;
			renderChart( data );
		} );
	}

	const backLink = document.getElementById( 'ak-back-link' );
	const bannerBack = document.getElementById( 'ak-kit-banner-back' );
	if ( backLink ) {
		backLink.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			resetKit();
		} );
	}
	if ( bannerBack ) {
		bannerBack.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			resetKit();
		} );
	}
	if ( exportBtn ) {
		exportBtn.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			exportCSV();
		} );
	}

	// Sortable column headers.
	document.querySelectorAll( '#ak-stats-table thead th' ).forEach( ( th ) => {
		th.addEventListener( 'click', () => {
			const col = th.dataset.col;
			if ( ! col ) {
				return;
			}
			if ( sortCol === col ) {
				sortDir = sortDir === 'asc' ? 'desc' : 'asc';
			} else {
				sortCol = col;
				sortDir = th.dataset.type === 'number' ? 'desc' : 'asc';
			}
			const data = activeKit
				? allData.filter( ( r ) => r.slug === activeKit )
				: allData;
			renderTable( data );
		} );
	} );

	// ── Init ──
	initChart();
	render();
} )();
