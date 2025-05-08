@yield('pages.index')
<script>

$(document).ready(function () {
    const dashboard = function (e) {

        let invoice = 0;
        let accrue = 0;

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

        const getAccrue = $.ajax({
            type: "GET",
            url: "{{ $url_total_accrue }}",
            data: null,
            dataType: "json",
            success: function (response) {
                accrue = response.data;

                $("#accrue-total").text("Rp " + number_format(response.data));
            }
        });

        $.when(getInvoice, getAccrue).done(function () {
            let revenue = invoice + accrue;

            $("#revenue-total").text("Rp " + number_format(revenue));
        });
    }

    dashboard();
})

</script>