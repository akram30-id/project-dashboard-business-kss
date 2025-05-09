<script>
    $(document).ready(function() {
        const MONTHS_SHORT = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov",
            "Dec"];
        const CHART_COLORS = {
            revenue: "#4e73df",
            invoice: "#1cc88a",
            accrue: "#36b9cc",
        };

        let currentYear = new Date().getFullYear();
        let currentChartType = "bar";
        let yearlyChart;

        let annualData = {};
        let dataPerMonth = {}; // Perbaikan: pastikan format benar

        function getDataMonthly() {
            let year = new Date().getFullYear();
            let ajaxPromises = [];

            for (let i = 0; i < 5; i++) {
                let currentLoopYear = year--;
                const url = "{{ $data['url_get_list_monthly'] }}" + `&year=${currentLoopYear}`;

                let ajaxPromise = $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json"
                }).then(response => {
                    annualData[currentLoopYear] = response.data;

                    // Jika ini tahun sekarang, langsung render chart
                    if (currentLoopYear === currentYear) {
                        dataPerMonth = annualData[currentYear];
                        console.log("Data untuk chart:", dataPerMonth);

                        if (yearlyChart) {
                            yearlyChart.destroy();
                        }
                        initializeChart();
                    }
                });

                ajaxPromises.push(ajaxPromise);
            }
        }

        function initializeChart() {
            if (!dataPerMonth || !dataPerMonth.revenue || !dataPerMonth.invoice || !dataPerMonth.accrue) {
                console.error("Data tidak lengkap untuk chart.");
                return;
            }

            if (!elements.yearlyChart) {
                console.error("Canvas untuk chart tidak ditemukan!");
                return;
            }

            const ctx = elements.yearlyChart.getContext("2d");

            const config = createChartConfig();
            yearlyChart = new Chart(ctx, config);
        }

        function createChartConfig() {
            return {
                type: currentChartType,
                data: {
                    labels: MONTHS_SHORT,
                    datasets: [{
                            label: "Revenue",
                            data: dataPerMonth.revenue,
                            backgroundColor: CHART_COLORS.revenue,
                            borderColor: CHART_COLORS.revenue,
                            borderWidth: 2,
                        },
                        {
                            label: "Invoice",
                            data: dataPerMonth.invoice,
                            backgroundColor: CHART_COLORS.invoice,
                            borderColor: CHART_COLORS.invoice,
                            borderWidth: 2,
                        },
                        {
                            label: "Accrue",
                            data: dataPerMonth.accrue,
                            backgroundColor: CHART_COLORS.accrue,
                            borderColor: CHART_COLORS.accrue,
                            borderWidth: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true,
                                pointStyle: "circle",
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let value = context.raw;
                                    return `${context.dataset.label}: Rp ${value.toLocaleString()}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return `Rp ${value.toLocaleString()} Jt`;
                                },
                            },
                            grid: {
                                color: "rgba(0, 0, 0, 0.05)",
                                drawBorder: false
                            },
                        },
                    },
                },
            };
        }

        const elements = {
            yearlyChart: document.getElementById("yearlyChart"),
        };

        getDataMonthly();
    });
</script>
