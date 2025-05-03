{{-- @php
    echo '<pre>';
    print_r($data['url_get_list_annual']);
    return;
@endphp --}}

<!-- stats-cards.blade.php -->
<div class="row">
    <div class="col-md-12">
        <div class="stats-card primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 id="revenue-total-report">Loading . . .</h2>
                    <p>Total Revenue <span class="year-label">Loading . . .</span></p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 id="invoice-total-report">Loading . . .</h2>
                    <p>Total Invoice <span class="year-label">Loading . . .</span></p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="stats-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 id="accrue-total-report">Loading . . .</h2>
                    <p>Total Accrue <span class="year-label">Loading . . .</span></p>
                </div>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $(".year-pill").click(function(e) {
            e.preventDefault();

            $("#invoice-total-report").text("Loading . . .");
            $("#revenue-total-report").text("Loading . . .");
            $("#accrue-total-report").text("Loading . . .");

            const selectedYear = $(this).data('year');

            showListAnnualReport(selectedYear);
        });

        const showListAnnualReport = function(year) {
            let url = "{{ $data['url_get_list_annual'] }}" + `&year=${year}`;

            if (!year) {
                url = "{{ $data['url_get_list_annual'] }}";
            }

            $.ajax({
                type: "GET",
                url: url,
                dataType: "json",
                success: function(response) {
                    const data = response.data;
                    $("#invoice-total-report").text("Rp " + number_format(data
                        .current_annual_invoice));
                    $("#revenue-total-report").text("Rp " + number_format(data
                        .current_annual_revenue));
                    $("#accrue-total-report").text("Rp " + number_format(data
                        .current_annual_accrue));
                }
            });
        }

        showListAnnualReport(null);
    });
</script>
