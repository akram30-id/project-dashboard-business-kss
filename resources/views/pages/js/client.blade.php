@yield('pages.index')
<script>

$(document).ready(function () {
    const dashboard = function (e) {

        let invoice = 0;
        let revenue = 0;

        const getInvoice = $.ajax({
            type: "GET",
            url: "{{ $url_total_invoice }}",
            data: null,
            dataType: "json",
            success: function (response) {
                invoice = response.data;

                $("#invoice-total").text("Rp " + number_format(response.data));
            }
        });

        const getRevenue = $.ajax({
            type: "GET",
            url: "{{ $url_total_revenue }}",
            data: null,
            dataType: "json",
            success: function (response) {
                revenue = response.data;

                $("#revenue-total").text("Rp " + number_format(response.data));
            }
        });

        $.when(getInvoice, getRevenue).done(function () {
            let accrue = invoice - revenue;

            $("#accrue-total").text("Rp " + number_format(accrue));
        });
    }

    dashboard();
})

</script>