
//alert("sdfs");
//WebSocket settings
JSPM.JSPrintManager.auto_reconnect = true;
JSPM.JSPrintManager.start();
JSPM.JSPrintManager.WS.onStatusChanged = function () {
    if (jsPrintManagerWSStatus()) {
        //get client installed printers
        JSPM.JSPrintManager.getPrinters().then(function (myPrinters) {
            var labelOptions = '<option>Select Printer</option>';
            var invoiceOptions = '<option>Select Printer</option>';
            var selected = '';

            for (var i = 0; i < myPrinters.length; i++) {
                selected = (Cookies.get('label_printer') == myPrinters[i]) ? 'selected' : '';
                labelOptions += '<option ' + selected + '>' + myPrinters[i] + '</option>';
            }
            for (var i = 0; i < myPrinters.length; i++) {
                selected = (Cookies.get('invoice_printer') == myPrinters[i]) ? 'selected' : '';
                invoiceOptions += '<option ' + selected + '>' + myPrinters[i] + '</option>';
            }
            $('#label_printer select').html(labelOptions);
            $('#invoice_printer select').html(invoiceOptions);
        });
    }
};

//Check JSPM WebSocket status
function jsPrintManagerWSStatus() {
    if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Open) {
        swal.close();
        return true;
    }

    else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Closed) {
        var text = "If it is not installed, please download it by clicking the button";
        swal({
                title: 'JSPrintManager (JSPM) is not installed or not running!',
                text: text,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Download'
            },
            function (isConfirm) {
                if (isConfirm) {
                    window.open("https://neodynamic.com/downloads/jspm/jspm-2.0.19.1203-win.exe");
                }
                else {
                    return false;
                }
            });


        return false;
    }
    else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.BlackListed) {
        alert('JSPM has blacklisted this website!');
        return false;
    }
}

$(document).ready(
    function () {
        $('#label_printer select').on('change', function () {
            Cookies.set('label_printer', this.value, { expires: 365 });
        });
        $('#invoice_printer select').on('change', function () {
            Cookies.set('invoice_printer', this.value, { expires: 365 });
        });
    }
);