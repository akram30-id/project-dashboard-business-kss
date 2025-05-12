<!-- chart-container.blade.php -->
<div class="chart-container">
    <div class="chart-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Monthly Comparison <span class="year-label">2025</span></h5>
            <div>
                <div class="btn-group chart-type-selector" role="group">
                    <button type="button" class="btn btn-sm btn-light active" data-chart-type="bar" data-target="yearly">
                        <i class="fas fa-chart-bar"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light" data-chart-type="line" data-target="yearly">
                        <i class="fas fa-chart-line"></i>
                    </button>
                </div>
                <button id="toggleViewYearly" class="btn btn-sm btn-light ms-2 btn-toggle-view">
                    <i class="fas fa-table"></i> Show Table
                </button>
            </div>
        </div>
    </div>
    <div class="chart-body">
        <!-- Chart View -->
        <div id="chartViewYearly">
            <canvas id="yearlyChart" style="height: 300px;"></canvas>
        </div>

        <!-- Table View (hidden by default) -->
        <div id="tableViewYearly" class="d-none">
            <div class="table-responsive">
                <table class="table monthly-comparison-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Revenue</th>
                            <th>Invoice</th>
                            <th>Accrue</th>
                            <th>#####</th>
                        </tr>
                    </thead>
                    <tbody id="comparisonTableBody">
                        <!-- Table rows will be populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL OF DETAIL INVOICE MONTHLY --}}
    <div class="modal fade" id="monthDetailsModal" tabindex="-1" aria-labelledby="monthDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width: 100%; max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monthDetailsModalLabel"><span id="detail-month-year"></span> Financial Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold mb-3">Detail Information</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Pekerjaan</th>
                                    <th scope="col">Nama Customer</th>
                                    <th scope="col">No.CO</th>
                                    <th scope="col">Tanggal CO</th>
                                    <th scope="col">No.DO</th>
                                    <th scope="col">Tanggal DO</th>
                                    <th scope="col">Nominal Invoice</th>
                                    <th scope="col">Status Invoice</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-detail-invoice">
                                <!-- Table rows will be populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="chart-footer">
        <div class="row">
            <div class="col-md-4 text-center mb-2 mb-md-0">
                <p class="mb-1">Total Revenue</p>
                <h5 class="revenue mb-0" id="revenue-total">Rp 156.2.000.000,-</h5>
            </div>
            <div class="col-md-4 text-center mb-2 mb-md-0">
                <p class="mb-1">Total Invoice</p>
                <h5 class="invoice mb-0" id="invoice-total">Rp 135.5.000.000,-</h5>
            </div>
            <div class="col-md-4 text-center">
                <p class="mb-1">Total Accrue</p>
                <h5 class="accrue mb-0" id="accrue-total">Rp 117.7.000.000,-</h5>
            </div>
        </div>
    </div> --}}
    {{-- @include('pages.report.js.annual-chart') --}}
</div>
