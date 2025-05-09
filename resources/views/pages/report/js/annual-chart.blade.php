<script>
    let chartCtx = document.getElementById('yearlyChart')?.getContext('2d');
    if (!chartCtx) {
        console.error("Canvas #yearlyChart tidak ditemukan!");
    }

    let months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Initial empty chart
    let monthlyChart = new Chart(chartCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                    label: 'Revenue',
                    backgroundColor: '#4e73df',
                    data: []
                },
                {
                    label: 'Invoice',
                    backgroundColor: '#1cc88a',
                    data: []
                },
                {
                    label: 'Accrue',
                    backgroundColor: '#36b9cc',
                    data: []
                }
            ]
        },
        options: {
            responsive: true,
            animation: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                        }
                    }
                }
            }
        }
    });

    window.getDataForYearlyChart = function(year) {
        const url = "{{ $data['url_get_list_monthly'] }}&year=" + year;

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                let data = response.data[year];
                let revenue = data.revenue;
                let invoice = data.invoice;
                let accrue = data.accrue;

                let index = 0;

                function renderNextMonth() {
                    if (index >= 12) return;

                    monthlyChart.data.labels.push(months[index]);
                    monthlyChart.data.datasets[0].data.push(revenue[index]);
                    monthlyChart.data.datasets[1].data.push(invoice[index]);
                    monthlyChart.data.datasets[2].data.push(accrue[index]);

                    monthlyChart.update();
                    index++;
                    setTimeout(renderNextMonth, 300); // Adjust delay as needed
                }

                // baru render lagi
                renderNextMonth();
            },
            error: function() {
                console.error("Failed to load chart data.");
            }
        });
    }

    $(document).ready(function() {
        getDataForYearlyChart(new Date().getFullYear()); // panggil manual

        $(".year-pill").click(function(e) {
            e.preventDefault();

            // kosongin chartnya dulu
            monthlyChart.data.labels = [];
            monthlyChart.data.datasets.forEach(ds => ds.data = []);
            monthlyChart.update();

            const chartYearSelected = $(this).data("year");
            getDataForYearlyChart(chartYearSelected);
        })
    });
</script>
