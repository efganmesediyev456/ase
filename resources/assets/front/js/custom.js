$(document).ready(function () {
    $("#ajaxModal").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);
        $(this).find(".modal-body").load(link.attr("href"));
    });

    var dateChange = $(".changeTypeToDate");
    if (dateChange.length > 0) {
        $(".changeTypeToDate").attr('type', 'date');
    }


    $(":input").inputmask();

    if ($("#crisp-chatbox").length > 0) {
        $(".crisp-1uswakw").style("display", "none !important");
        $(".crisp-1uswakw").remove();
    }
});