import { Chart } from 'chart.js/auto';

/* global activityKitStats */

const { restUrl, nonce, jetpackAvailable } = activityKitStats;

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

// ── DOM refs ──
const filterKit = document.getElementById( 'ak-filter-kit' );
const tableBody = document.getElementById( 'ak-stats-table-body' );
const chartCanvas = document.getElementById( 'ak-stats-chart' );

if ( filterKit && tableBody && chartCanvas ) {
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

	function rangeLabel() {
		const labels = {
			'7d': 'Last 7 days',
			'30d': 'Last 30 days',
			'90d': 'Last 90 days',
			all: 'All time',
		};
		return labels[ activeRange ] || 'All time';
	}

	const metricLabel = {
		both: 'Views vs. Downloads',
		views: 'Views',
		downloads: 'Downloads',
	};

	// Creates a single full-width message row for the stats table.
	function msgRow( text ) {
		const row = document.createElement( 'tr' );
		const cell = document.createElement( 'td' );
		cell.colSpan = 5;
		cell.textContent = text;
		row.appendChild( cell );
		return row;
	}

	// ── Fetch ──
	async function fetchStats() {
		const url = new URL( restUrl );
		url.searchParams.set( 'metric', 'both' );
		url.searchParams.set( 'range', activeRange );

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
		const totalV = data.reduce( ( sum, row ) => sum + ( row.views ?? 0 ), 0 );
		const totalD = data.reduce( ( sum, row ) => sum + ( row.downloads ?? 0 ), 0 );
		const rate = totalV > 0 ? ( ( totalD / totalV ) * 100 ).toFixed( 1 ) + '%' : '—';

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
							label: ( context ) =>
								' ' + context.dataset.label + ': ' + context.parsed.y.toLocaleString(),
						},
					},
				},
				scales: {
					// eslint-disable-next-line id-length
					x: {
						grid: { display: false },
						ticks: {
							font: { size: 11 },
							color: '#646970',
							maxRotation: 30,
						},
						border: { color: '#c3c4c7' },
					},
					// eslint-disable-next-line id-length
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
		const sliced = paged ? data.slice( chartOffset, chartOffset + CHART_PAGE ) : data;

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
			chartSliderLabel.textContent = chartOffset + 1 + '–' + end + ' of ' + total;
		}

		const labels = sliced.map( ( row ) => ( row.title.length > 20 ? row.title.slice( 0, 18 ) + '…' : row.title ) );
		const datasets = [];

		if ( activeMetric !== 'downloads' ) {
			datasets.push( {
				label: 'Views',
				backgroundColor: '#3858e9',
				barPercentage: 0.75,
				categoryPercentage: 0.7,
				data: sliced.map( ( row ) => row.views ?? 0 ),
			} );
		}
		if ( activeMetric !== 'views' ) {
			datasets.push( {
				label: 'Downloads',
				backgroundColor: '#9fb1ff',
				barPercentage: 0.75,
				categoryPercentage: 0.7,
				data: sliced.map( ( row ) => row.downloads ?? 0 ),
			} );
		}

		chart.data.labels = labels;
		chart.data.datasets = datasets;
		chart.update();
	}

	// ── Table ──
	function renderTable( data ) {
		if ( ! data.length ) {
			tableBody.replaceChildren( msgRow( 'No data found.' ) );
			return;
		}

		const sorted = [ ...data ].sort( ( a, b ) => {
			if ( sortCol === 'title' ) {
				const titleA = a.title.toLowerCase();
				const titleB = b.title.toLowerCase();
				if ( sortDir === 'asc' ) {
					if ( titleA < titleB ) {
						return -1;
					}
					if ( titleA > titleB ) {
						return 1;
					}
					return 0;
				}
				if ( titleB < titleA ) {
					return -1;
				}
				if ( titleB > titleA ) {
					return 1;
				}
				return 0;
			}
			if ( sortCol === 'updated' ) {
				return sortDir === 'asc'
					? ( a.updated || '' ).localeCompare( b.updated || '' )
					: ( b.updated || '' ).localeCompare( a.updated || '' );
			}
			let valueA;
			if ( sortCol === 'views' ) {
				valueA = a.views ?? 0;
			} else if ( sortCol === 'downloads' ) {
				valueA = a.downloads ?? 0;
			} else {
				valueA = ( a.views ?? 0 ) > 0 ? ( a.downloads ?? 0 ) / ( a.views ?? 0 ) : 0;
			}
			let valueB;
			if ( sortCol === 'views' ) {
				valueB = b.views ?? 0;
			} else if ( sortCol === 'downloads' ) {
				valueB = b.downloads ?? 0;
			} else {
				valueB = ( b.views ?? 0 ) > 0 ? ( b.downloads ?? 0 ) / ( b.views ?? 0 ) : 0;
			}
			return sortDir === 'asc' ? valueA - valueB : valueB - valueA;
		} );

		tableBody.innerHTML = '';
		sorted.forEach( ( row ) => {
			const views = row.views ?? 0;
			const downloads = row.downloads ?? 0;
			const rate = views > 0 ? ( ( downloads / views ) * 100 ).toFixed( 1 ) + '%' : '—';
			const isSelected = row.slug === activeKit;
			const tableRow = document.createElement( 'tr' );
			if ( isSelected ) {
				tableRow.classList.add( 'is-selected' );
			}

			const viewsClass = 'ak-col-number' + ( activeMetric === 'downloads' ? ' ak-hidden-col' : '' );
			const dlClass = 'ak-col-number' + ( activeMetric === 'views' ? ' ak-hidden-col' : '' );

			const tdTitle = document.createElement( 'td' );
			const link = document.createElement( 'a' );
			link.href = '#';
			link.dataset.slug = row.slug;
			link.textContent = row.title;
			link.addEventListener( 'click', ( event ) => {
				event.preventDefault();
				setKit( event.currentTarget.dataset.slug );
			} );
			tdTitle.appendChild( link );

			const tdViews = document.createElement( 'td' );
			tdViews.className = viewsClass;
			tdViews.textContent = fmt( views );

			const tdDl = document.createElement( 'td' );
			tdDl.className = dlClass;
			tdDl.textContent = fmt( downloads );

			const tdRate = document.createElement( 'td' );
			tdRate.className = 'ak-col-number';
			tdRate.textContent = rate;

			const tdUpdated = document.createElement( 'td' );
			tdUpdated.textContent = formatDate( row.updated );

			tableRow.append( tdTitle, tdViews, tdDl, tdRate, tdUpdated );
			tableRow.addEventListener( 'click', ( event ) => {
				if ( event.target.tagName !== 'A' ) {
					setKit( row.slug );
				}
			} );

			tableBody.appendChild( tableRow );
		} );

		// Update sort arrows.
		document.querySelectorAll( '#ak-stats-table thead th' ).forEach( ( tableHeader ) => {
			const col = tableHeader.dataset.col;
			const arrow = tableHeader.querySelector( '.ak-sort-arrow' );
			tableHeader.classList.remove( 'is-sorted' );
			if ( arrow ) {
				arrow.textContent = '';
			}
			if ( col === sortCol && arrow ) {
				tableHeader.classList.add( 'is-sorted' );
				arrow.textContent = sortDir === 'asc' ? ' ↑' : ' ↓';
			}
		} );
	}

	// ── UI state ──
	function updateUI() {
		const isSingle = !! activeKit;
		const kitObj = isSingle ? allData.find( ( row ) => row.slug === activeKit ) : null;

		if ( chartTitle ) {
			chartTitle.textContent =
				metricLabel[ activeMetric ] + ' — ' + ( isSingle && kitObj ? kitObj.title : 'All Kits' );
		}
		if ( chartSubtitle ) {
			chartSubtitle.textContent = rangeLabel();
		}

		if ( legendViews ) {
			legendViews.style.display = activeMetric === 'downloads' ? 'none' : '';
		}
		if ( legendDownloads ) {
			legendDownloads.style.display = activeMetric === 'views' ? 'none' : '';
		}

		if ( thViews ) {
			thViews.classList.toggle( 'ak-hidden-col', activeMetric === 'downloads' );
		}
		if ( thDownloads ) {
			thDownloads.classList.toggle( 'ak-hidden-col', activeMetric === 'views' );
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
			tableSubtitle.textContent = isSingle ? 'Showing single kit' : "Click a row to see a single kit's stats";
		}
	}

	// ── Main render ──
	async function render() {
		if ( ! jetpackAvailable ) {
			tableBody.replaceChildren(
				msgRow(
					'Jetpack Stats is not connected. View and download counts require a Jetpack connection to WordPress.com.'
				)
			);
			return;
		}

		tableBody.replaceChildren( msgRow( 'Loading…' ) );

		try {
			allData = await fetchStats();
			const data = activeKit ? allData.filter( ( row ) => row.slug === activeKit ) : allData;
			updateSummary( data );
			updateUI();
			renderChart( data );
			renderTable( data );
		} catch ( error ) {
			tableBody.replaceChildren( msgRow( 'Error loading stats: ' + error.message ) );
		}
	}

	// ── Setters ──
	function setMetric( metric ) {
		activeMetric = metric;
		metricBtns.forEach( ( button ) => button.classList.toggle( 'is-active', button.dataset.akMetric === metric ) );
		const data = activeKit ? allData.filter( ( row ) => row.slug === activeKit ) : allData;
		updateSummary( data );
		updateUI();
		renderChart( data );
		renderTable( data );
	}

	function setRange( range ) {
		activeRange = range;
		rangeBtns.forEach( ( button ) => button.classList.toggle( 'is-active', button.dataset.akRange === range ) );
		render();
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
	function csvCell( value ) {
		const str = String( value );
		// Prefix formula trigger characters to prevent spreadsheet formula injection.
		const safe = /^[=+\-@\t\r]/.test( str ) ? "'" + str : str;
		return '"' + safe.replace( /"/g, '""' ) + '"';
	}

	function exportCSV() {
		const data = activeKit ? allData.filter( ( row ) => row.slug === activeKit ) : allData;
		const rows = [ [ 'Kit Name', 'Views', 'Downloads', 'Download Rate', 'Last Updated' ] ];
		data.forEach( ( row ) => {
			const views = row.views ?? 0;
			const downloads = row.downloads ?? 0;
			const rate = views > 0 ? ( ( downloads / views ) * 100 ).toFixed( 1 ) + '%' : '0%';
			rows.push( [ row.title, views, downloads, rate, row.updated || '' ] );
		} );
		const csv = rows.map( ( row ) => row.map( csvCell ).join( ',' ) ).join( '\n' );
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
		btn.addEventListener( 'click', () => setMetric( btn.dataset.akMetric ) );
	} );

	rangeBtns.forEach( ( btn ) => {
		btn.addEventListener( 'click', () => setRange( btn.dataset.akRange ) );
	} );

	filterKit.addEventListener( 'change', () => {
		activeKit = filterKit.value;
		chartOffset = 0;
		render();
	} );

	if ( chartSlider ) {
		chartSlider.addEventListener( 'input', () => {
			chartOffset = parseInt( chartSlider.value, 10 );
			const data = activeKit ? allData.filter( ( row ) => row.slug === activeKit ) : allData;
			renderChart( data );
		} );
	}

	const backLink = document.getElementById( 'ak-back-link' );
	const bannerBack = document.getElementById( 'ak-kit-banner-back' );
	if ( backLink ) {
		backLink.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			resetKit();
		} );
	}
	if ( bannerBack ) {
		bannerBack.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			resetKit();
		} );
	}
	if ( exportBtn ) {
		exportBtn.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			exportCSV();
		} );
	}

	// Sortable column headers.
	document.querySelectorAll( '#ak-stats-table thead th' ).forEach( ( tableHeader ) => {
		tableHeader.addEventListener( 'click', () => {
			const col = tableHeader.dataset.col;
			if ( ! col ) {
				return;
			}
			if ( sortCol === col ) {
				sortDir = sortDir === 'asc' ? 'desc' : 'asc';
			} else {
				sortCol = col;
				sortDir = tableHeader.dataset.type === 'number' ? 'desc' : 'asc';
			}
			const data = activeKit ? allData.filter( ( row ) => row.slug === activeKit ) : allData;
			renderTable( data );
		} );
	} );

	// ── Init ──
	initChart();
	render();
}
