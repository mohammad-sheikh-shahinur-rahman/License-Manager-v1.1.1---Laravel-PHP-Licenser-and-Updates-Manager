class LicenseManagerWidgets {
    static initLicensesShareChart() {
        const chartData = window.licensesShareData || {}
        const $chart = $('#licenses-share-chart')

        if (!$chart.length) return

        const total = (chartData.valid || 0) + (chartData.invalid || 0) + (chartData.blocked || 0)

        if (total === 0) {
            $chart.html('<div class="text-center text-muted py-5">' + (chartData.noDataText || 'No data available.') + '</div>')
            return
        }

        new Morris.Donut({
            element: 'licenses-share-chart',
            data: [
                { label: chartData.labels?.valid || 'Valid', value: chartData.valid || 0 },
                { label: chartData.labels?.invalid || 'Invalid', value: chartData.invalid || 0 },
                { label: chartData.labels?.blocked || 'Blocked', value: chartData.blocked || 0 }
            ],
            colors: ['#2fb344', '#f76707', '#d63939'],
            formatter: function(y) {
                return y
            }
        })
    }

    static initLicensesActivationsChart() {
        const chartData = window.licensesActivationsData || {}
        const $chart = $('#licenses-activations-chart')

        if (!$chart.length) return

        if (!chartData.data || chartData.data.length === 0) {
            $chart.html('<div class="text-center text-muted py-5">' + (chartData.noDataText || 'No data available.') + '</div>')
            return
        }

        new Morris.Line({
            element: 'licenses-activations-chart',
            data: chartData.data,
            xkey: 'date',
            ykeys: ['licenses', 'activations', 'downloads'],
            labels: [
                chartData.labels?.licenses || 'License Added/Modified',
                chartData.labels?.activations || 'Valid Activations',
                chartData.labels?.downloads || 'Updates Downloaded'
            ],
            lineColors: ['#206bc4', '#2fb344', '#0ca678'],
            lineWidth: 2,
            pointSize: 3,
            hideHover: 'auto',
            resize: true,
            gridTextSize: 11,
            parseTime: true,
            xLabelFormat: function(d) {
                return ('0' + d.getDate()).slice(-2) + ' ' + d.toLocaleString('default', { month: 'short' })
            }
        })
    }
}

$(() => {
    const $licensesShare = $('#widget-licenses-share')
    const $licensesActivationsChart = $('#widget-licenses-activations-chart')

    if ($licensesShare.length) {
        BDashboard.loadWidget(
            $licensesShare.find('.widget-content'),
            $licensesShare.data('url'),
            null,
            () => LicenseManagerWidgets.initLicensesShareChart()
        )
    }

    if ($licensesActivationsChart.length) {
        BDashboard.loadWidget(
            $licensesActivationsChart.find('.widget-content'),
            $licensesActivationsChart.data('url'),
            null,
            () => LicenseManagerWidgets.initLicensesActivationsChart()
        )
    }

    const $recentActivities = $('#widget-recent-activities')
    if ($recentActivities.length) {
        BDashboard.loadWidget(
            $recentActivities.find('.widget-content'),
            $recentActivities.data('url')
        )
    }

    const $topCustomers = $('#widget-top-customers')
    if ($topCustomers.length) {
        BDashboard.loadWidget(
            $topCustomers.find('.widget-content'),
            $topCustomers.data('url')
        )
    }

    const $topProducts = $('#widget-top-products')
    if ($topProducts.length) {
        BDashboard.loadWidget(
            $topProducts.find('.widget-content'),
            $topProducts.data('url')
        )
    }
})
