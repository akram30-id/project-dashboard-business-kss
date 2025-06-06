<script>
    let chartCtx = document.getElementById('yearlyChart')?.getContext('2d');
    if (!chartCtx) {
        console.error("Canvas #yearlyChart tidak ditemukan!");
    }

    let months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const MONTHS_FULL = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

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
                    label: 'Unbilled',
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
        getDataForYearlyChart(new Date().getFullYear());

        let year = new Date().getFullYear();

        $(".year-pill").click(function(e) {
            e.preventDefault();

            // kosongin chartnya dulu
            monthlyChart.data.labels = [];
            monthlyChart.data.datasets.forEach(ds => ds.data = []);
            monthlyChart.update();

            const chartYearSelected = $(this).data("year");
            year = chartYearSelected;
            getDataForYearlyChart(chartYearSelected);
        });

        // Toggle between chart and table view
        $("#toggleViewYearly").click(function() {
            const chartView = $("#chartViewYearly");
            const tableView = $("#tableViewYearly");
            const $button = $(this);

            if (chartView.hasClass("d-none")) {
                // Switch to chart view
                chartView.removeClass("d-none");
                tableView.addClass("d-none");
                $button.html('<i class="fas fa-table"></i> Show Table');
            } else {
                // Switch to table view
                chartView.addClass("d-none");
                tableView.removeClass("d-none");
                $button.html('<i class="fas fa-chart-bar"></i> Show Chart');

                // Populate table with current chart data
                populateComparisonTable();
            }
        });

        // Function to populate table with chart data
        function populateComparisonTable() {
            if (!monthlyChart.data || !monthlyChart.data.labels) return;

            const tableBody = $("#comparisonTableBody");
            tableBody.empty();

            // Format currency function
            function formatCurrency(value) {
                // Format number with thousands separators
                const formattedNumber = new Intl.NumberFormat('id-ID').format(value);
                return `Rp ${formattedNumber},-`;
            }

            // Calculate growth percentages
            function calculateGrowth(currentIndex, dataArray) {
                if (currentIndex === 0) return 0.5; // Default for January
                const prevValue = dataArray[currentIndex - 1];
                const currentValue = dataArray[currentIndex];
                if (prevValue === 0) return 0;
                return ((currentValue - prevValue) / prevValue).toFixed(1);
            }

            // Get full month name from abbreviation
            function getFullMonthName(abbr) {
                const monthNames = {
                    'Jan': 'January',
                    'Feb': 'February',
                    'Mar': 'March',
                    'Apr': 'April',
                    'May': 'May',
                    'Jun': 'June',
                    'Jul': 'July',
                    'Aug': 'August',
                    'Sep': 'September',
                    'Oct': 'October',
                    'Nov': 'November',
                    'Dec': 'December'
                };
                return monthNames[abbr] || abbr;
            }

            // Create table rows
            monthlyChart.data.labels.forEach((month, index) => {
                const revenue = monthlyChart.data.datasets[0].data[index];
                const invoice = monthlyChart.data.datasets[1].data[index];
                const accrue = monthlyChart.data.datasets[2].data[index];

                // Calculate growth percentages
                const revenueGrowth = calculateGrowth(index, monthlyChart.data.datasets[0].data);
                const invoiceGrowth = calculateGrowth(index, monthlyChart.data.datasets[1].data);
                const accrueGrowth = calculateGrowth(index, monthlyChart.data.datasets[2].data);

                const row = `
                <tr>
                    <td>${getFullMonthName(month)}</td>
                    <td>${formatCurrency(revenue)} <span class="text-success">(+${revenueGrowth})</span></td>
                    <td>${formatCurrency(invoice)} <span class="text-success">(+${invoiceGrowth})</span></td>
                    <td>${formatCurrency(accrue)} <span class="text-success">(+${accrueGrowth})</span></td>
                    <td><button class="btn btn-sm btn-warning view-month-details" data-toggle="modal" data-target="#monthDetailsModal" data-month="${index}" data-year="${year}">Detail</button></td>
                </tr>
            `;
                tableBody.append(row);
            });
        }

        $("#tableViewYearly").on("click", ".view-month-details", function() {
            const detailYear = $(this).data('year');
            const detailMonth = parseInt($(this).data('month')) + 1;
            const monthIndex = $(this).data('month');

            const monthName = MONTHS_FULL[monthIndex];
            $("#detail-month-year").text(`${monthName} ${year}`)

            showMonthDetails(detailYear, monthIndex, detailMonth);
        });

        function showMonthDetails(year, monthIndex, detailMonth, page = 1, limit = 10) {
            $("#tbody-detail-invoice").html(`<tr>
                <td class="text-center" colspan="9">Loading . . .</td>
            </tr>`);

            if (page <= 1) {
                $("#prevDetailMonthly").attr("class", "page-item disabled");
            } else {
                $("#prevDetailMonthly").attr("class", "page-item");
            }

            const urlDetailInvoiceMonthly = "{{ $data['url_get_invoice_detail_monthly'] }}&year=" +
                year + "&month=" + detailMonth + "&page=" + page + "&limit=" + limit;

            $.ajax({
                type: "GET",
                url: urlDetailInvoiceMonthly,
                dataType: "json",
                success: function(response) {
                    console.info(response);
                    const data = response.data;

                    let no = 1;

                    let htmlDetailInvoiceMonthly;

                    data.forEach(detailInvoice => {
                        htmlDetailInvoiceMonthly += `
                            <tr>
                                <td>${no++}</td>
                                <td>${detailInvoice.work_title}</td>
                                <td>${detailInvoice.customer_name}</td>
                                <td>${(detailInvoice.co_no == null) ? '-' : detailInvoice.co_no}</td>
                                <td>${detailInvoice.co_date}</td>
                                <td>${(detailInvoice.do_no == null ) ? '-' : detailInvoice.do_no}</td>
                                <td>${detailInvoice.do_date}</td>
                                <td>${new Intl.NumberFormat('id-ID').format(detailInvoice.amount)}</td>
                                <td>${detailInvoice.status}</td>
                            </tr>
                        `;
                    });

                    $("#tbody-detail-invoice").html(htmlDetailInvoiceMonthly);

                    $("#nextDetailMonthly").off("click").on("click", function() {
                        const nextPage = ++page;
                        showMonthDetails(year, monthIndex, detailMonth, nextPage, limit);
                    });

                    $("#prevDetailMonthly").off("click").on("click", function() {
                        const prevPage = --page;
                        showMonthDetails(year, monthIndex, detailMonth, prevPage, limit);
                    });

                    $("#detailLimitMonthly").off("change").on("change", function() {
                        const limitValue = $(this).val();
                        showMonthDetails(year, monthIndex, detailMonth, page, limitValue);
                    });
                }
            });
        }
    });
</script>
