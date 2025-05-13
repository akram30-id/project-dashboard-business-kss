{{-- <script src="{{ asset('') }}assets/js/yearly-report.js"></script> --}}

@include('pages.report.js.annual-chart')
{{-- @include('pages.report.js.annual-chart_backup') --}}

<script>
    // Data sampel (bisa diganti dengan data dari backend)

    const showDetailTableAnnualy = function(page = 1, limit = 10, year = new Date().getFullYear()) {
        $("#tableBody").html(`<tr>
            <td class="text-center" colspan="9">Loading . . .</td>
        </tr>`);

        const urlDetailAnnual = "{{ $data['url_get_invoice_detail_annualy'] }}&year=" + year + "&page=" + page +
            "&limit=" + limit;

        $.ajax({
            type: "GET",
            url: urlDetailAnnual,
            dataType: "json",
            success: function(response) {
                console.info(response);

                const data = response.data;

                let detailAnnualRows;

                let no = 1;
                data.forEach(detailAnnual => {
                    detailAnnualRows += `
                        <tr>
                            <td>${no++}</td>
                            <td>${detailAnnual.work_title}</td>
                            <td>${detailAnnual.customer_name}</td>
                            <td>${(detailAnnual.co_no == null) ? '-' : detailAnnual.co_no}</td>
                            <td>${detailAnnual.co_date}</td>
                            <td>${(detailAnnual.do_no == null) ? '-' : detailAnnual.do_no}</td>
                            <td>${detailAnnual.do_date}</td>
                            <td>${detailAnnual.amount}</td>
                            <td>${detailAnnual.status}</td>
                        </tr>
                    `
                });

                $("#tableBody").html(detailAnnualRows);
            }
        });
    }

    $("#btnDetailInvoiceAnnual").on("click", function() {
        showDetailTableAnnualy();
    });

    $("#yearSelect").on("change", function() {
        const selecteYearDetail = $(this).val();

        showDetailTableAnnualy(1, 10, selecteYearDetail);
    });


    const reportData = [{
            no: 1,
            pekerjaan: "Sewa Kapal Alpine",
            customer: "PT. Salam",
            co: "CO.102301230",
            tgl_co: "12/01/2025",
            do: "DO.192737912",
            tgl_do: "23/01/2025",
            nominal: "Rp. 1.200.000,00",
            status: "Terkirim"
        },
        {
            no: 2,
            pekerjaan: "Pemeliharaan Mesin",
            customer: "CV. Maju Jaya",
            co: "CO.102301231",
            tgl_co: "15/02/2025",
            do: "DO.192737913",
            tgl_do: "25/02/2025",
            nominal: "Rp. 2.500.000,00",
            status: "Belum Terkirim"
        },
        {
            no: 3,
            pekerjaan: "Transportasi Barang",
            customer: "PT. Logistik Nusantara",
            co: "CO.102301232",
            tgl_co: "20/03/2025",
            do: "DO.192737914",
            tgl_do: "30/03/2025",
            nominal: "Rp. 3.750.000,00",
            status: "Terkirim"
        },
        {
            no: 4,
            pekerjaan: "Sewa Gudang",
            customer: "PT. Prima Solusi",
            co: "CO.102301233",
            tgl_co: "05/04/2025",
            do: "DO.192737915",
            tgl_do: "15/04/2025",
            nominal: "Rp. 1.800.000,00",
            status: "Dibatalkan"
        },
        {
            no: 5,
            pekerjaan: "Jasa Konsultasi",
            customer: "CV. Berkah Abadi",
            co: "CO.102301234",
            tgl_co: "10/05/2025",
            do: "DO.192737916",
            tgl_do: "20/05/2025",
            nominal: "Rp. 4.000.000,00",
            status: "Terkirim"
        },
        {
            no: 6,
            pekerjaan: "Pengiriman Kontainer",
            customer: "PT. Samudra Jaya",
            co: "CO.102301235",
            tgl_co: "15/06/2025",
            do: "DO.192737917",
            tgl_do: "25/06/2025",
            nominal: "Rp. 5.500.000,00",
            status: "Belum Terkirim"
        },
        {
            no: 7,
            pekerjaan: "Sewa Kapal Tanker",
            customer: "PT. Energi Laut",
            co: "CO.102301236",
            tgl_co: "20/07/2025",
            do: "DO.192737918",
            tgl_do: "30/07/2025",
            nominal: "Rp. 6.200.000,00",
            status: "Terkirim"
        },
        {
            no: 8,
            pekerjaan: "Pemeliharaan Kapal",
            customer: "CV. Lautan Sejahtera",
            co: "CO.102301237",
            tgl_co: "25/08/2025",
            do: "DO.192737919",
            tgl_do: "05/09/2025",
            nominal: "Rp. 2.300.000,00",
            status: "Terkirim"
        },
    ];

    const itemsPerPage = 5; // Jumlah item per halaman
    let currentPage = 1;
    let autoPaginationInterval = null;

    function renderTable(data, page) {
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const paginatedData = data.slice(start, end);

        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        paginatedData.forEach(item => {
            const row = `
                    <tr>
                        <td>${item.no}</td>
                        <td>${item.pekerjaan}</td>
                        <td>${item.customer}</td>
                        <td>${item.co}</td>
                        <td>${item.tgl_co}</td>
                        <td>${item.do}</td>
                        <td>${item.tgl_do}</td>
                        <td>${item.nominal}</td>
                        <td>${item.status}</td>
                    </tr>
                `;
            tbody.innerHTML += row;
        });
    }

    function renderPagination(data) {
        const totalPages = Math.ceil(data.length / itemsPerPage);
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';

        // Previous button
        pagination.innerHTML += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a>
                </li>
            `;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            pagination.innerHTML += `
                    <li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                    </li>
                `;
        }

        // Next button
        pagination.innerHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a>
                </li>
            `;
    }

    function changePage(page) {
        const totalPages = Math.ceil(reportData.length / itemsPerPage);
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable(reportData, currentPage);
            renderPagination(reportData);
        }
    }

    function startAutoPagination() {
        stopAutoPagination(); // Hentikan interval sebelumnya jika ada
        autoPaginationInterval = setInterval(() => {
            const totalPages = Math.ceil(reportData.length / itemsPerPage);
            currentPage = currentPage >= totalPages ? 1 : currentPage + 1;
            renderTable(reportData, currentPage);
            renderPagination(reportData);
        }, 5000); // 10 detik
    }

    function stopAutoPagination() {
        if (autoPaginationInterval) {
            clearInterval(autoPaginationInterval);
            autoPaginationInterval = null;
        }
    }

    // Fullscreen handling
    const modal = document.getElementById('reportTableModal');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const monthSelectorContainer = document.getElementById('monthSelectorContainer');
    const fullscreenIcon = fullscreenBtn.querySelector('i');
    const closeButton = modal.querySelector('.btn-close');
    const bsModal = new bootstrap.Modal(modal);

    fullscreenBtn.addEventListener('click', function() {
        if (!document.fullscreenElement) {
            modal.requestFullscreen().then(() => {
                fullscreenIcon.classList.remove('bi-arrows-fullscreen');
                fullscreenIcon.classList.add('bi-fullscreen-exit');
                monthSelectorContainer.style.display = 'none';
            });
        } else {
            document.exitFullscreen().then(() => {
                fullscreenIcon.classList.remove('bi-fullscreen-exit');
                fullscreenIcon.classList.add('bi-arrows-fullscreen');
                monthSelectorContainer.style.display = 'block';
            });
        }
    });

    document.addEventListener('fullscreenchange', function() {
        const modalDialog = document.querySelector('#reportTableModal .modal-dialog');
        if (document.fullscreenElement) {
            modalDialog.style.maxWidth = '100vw';
            modalDialog.style.height = '100vh';
            modalDialog.style.margin = '0';
        } else {
            modalDialog.style.maxWidth = '95vw';
            modalDialog.style.height = 'auto';
            modalDialog.style.margin = '1.75rem auto';
        }
    });

    closeButton.addEventListener('click', function() {
        if (document.fullscreenElement) {
            document.exitFullscreen().then(() => bsModal.hide());
        } else {
            bsModal.hide();
        }
    });

    // Initialize table and pagination when modal is shown
    modal.addEventListener('shown.bs.modal', function() {
        renderTable(reportData, currentPage);
        renderPagination(reportData);
        startAutoPagination(); // Mulai auto pagination saat modal dibuka
    });

    // Stop auto pagination when modal is hidden
    modal.addEventListener('hidden.bs.modal', function() {
        stopAutoPagination(); // Hentikan auto pagination saat modal ditutup
    });

    // Responsive table styling
    const style = document.createElement('style');
    style.textContent = `
            @media (max-width: 768px) {
                .table-responsive {
                    font-size: 0.8rem;
                }
                .table th, .table td {
                    padding: 0.5rem;
                }
                #wrapper, #content-wrapper {
                    min-width: 100%;
                }
            }
            .table-responsive {
                width: 100%;
            }
            .modal-content {
                width: 100%;
            }
        `;
    document.head.appendChild(style);
</script>
