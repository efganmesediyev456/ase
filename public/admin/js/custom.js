function updateURLParameter(url, param, paramVal) {
    var TheAnchor = null;
    var newAdditionalURL = "";
    var tempArray = url.split("?");
    var baseURL = tempArray[0];
    var additionalURL = tempArray[1];
    var temp = "";

    if (additionalURL) {
        var tmpAnchor = additionalURL.split("#");
        var TheParams = tmpAnchor[0];
        TheAnchor = tmpAnchor[1];
        if (TheAnchor)
            additionalURL = TheParams;

        tempArray = additionalURL.split("&");

        for (var i = 0; i < tempArray.length; i++) {
            if (tempArray[i].split('=')[0] != param) {
                newAdditionalURL += temp + tempArray[i];
                temp = "&";
            }
        }
    } else {
        var tmpAnchor = baseURL.split("#");
        var TheParams = tmpAnchor[0];
        TheAnchor = tmpAnchor[1];

        if (TheParams)
            baseURL = TheParams;
    }

    if (TheAnchor)
        paramVal += "#" + TheAnchor;

    var rows_txt = temp + "" + param + "=" + paramVal;
    return baseURL + "?" + newAdditionalURL + rows_txt;
}

function updateCell(id) {
        $("select[name='cell']").val(id);
        var form = $("#form-package");
        var seri = form.serialize();
        var _route = form.attr('action');
	if(_route == null) {
           form = $("#form-track");
           seri = form.serialize();
           _route = form.attr('action')+'?track=1';
	}
        $("#updated").hide();
        $.post(_route, seri, function (data) {
            $("#updated").text("The packages is in " + id).show();
	    var audio = new Audio('/sounds/scan_cell.mp3');
	    audio.play();
        });
}

$(document).ready(function () {

    $(".sort_id i").on("click", function (e) {
        e.preventDefault();
        var sort = $(this).data('sort');
        var key = $(this).data('key');
        var newSort = key + "__" + sort;
        var newURL = updateURLParameter(window.location.href, 'sort', newSort);
        location.replace(newURL);
    });

    $(".select_cell").on("click", function () {
        var id = $(this).data('id');
	updateCell(id);
    });

    $("#export").on('click', function (e) {
        e.preventDefault();

        var ids = [];

        $.each($("input.check_all:checked"), function () {
            ids.push($(this).val());
        });

        if (ids.length == 0) {
            // Submit search form
            $("input[name='search_type']").val('export');
            $("#search_form").submit();
            $("input[name='search_type']").val(null);
        } else {
            //
            $("input[name='hidden_items']").val(ids.join(","));
            $("#export_form").submit();
            $("input[name='search_type']").val(null);
        }
    });

    $(".tab_it").on("click", function () {
        var bagsId = $(this).data('bags');
        var packagesId = $(this).data('packages');
        var tabsId = $(this).data('tabs');
        if ($(this).hasClass('opened')) {
            $("." + bagsId).hide();

            $("." + packagesId).hide();
            $("." + tabsId).removeClass('opened').addClass('closed');
            $(this).removeClass('opened').addClass('closed');
        } else {
            $("." + bagsId).show();
            $(this).removeClass('closed').addClass('opened');
        }
    });

    $(".tab_it2").on("click", function () {
        var packagesId = $(this).data('packages');
        if ($(this).hasClass('opened')) {
            $("." + packagesId).hide();
            $(this).removeClass('opened').addClass('closed');
        } else {
            $("." + packagesId).show();
            $(this).removeClass('closed').addClass('opened');
        }
    });


    // Initialize
    $('a[data-plugin="c-editable"]').editable({
        ajaxOptions: {
            type: 'post'
        },
        select2: {
            width: 200,
            allowClear: true
        }
    });


    $('a[data-ajax-request]').on("click", function (e) {
        e.preventDefault();
        var url = $(this).data('ajax-request');
        $("#loading").show();
        $.ajax({
            method: "GET",
            url: url,
        }).done(function( html ) {
            $("#loading").hide();
        });
    });


    $("#by select").on('change', function () {
       var val = $(this).val();
       $(".campaign_content").hide();
       $("#" + val + "_content").show();
    });

    if ($('.table-responsive').length > 0) {
        const slider = document.querySelector('.table-responsive');
        let isDown = false;
        let startX;
        let scrollLeft;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        slider.addEventListener('mouseleave', () => {
            isDown = false;
        });
        slider.addEventListener('mouseup', () => {
            isDown = false;
        });
        slider.addEventListener('mousemove', (e) => {
            if ($(".editable-popup").length == 0) {
                if(!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 3; //scroll-fast
                slider.scrollLeft = scrollLeft - walk;
            }
        });
    }

    if ($("#matched").length) {

        $("#form-campaign input, #form-campaign select").change(function() {
            var seri = $("#form-campaign").serialize();
            var _route = $("#matched").data('route') + "?" + seri;

            $.get(_route, function (data) {
                $("#matched").text(data);
            })
        });

        $("#export_matched").on("click", function () {
            var seri = $("#form-campaign").serialize();
            var _route = $("#matched").data('route') + "?" + seri + "&export=true";
            window.open(_route);
        })
    }


    $(".select2-ajax").each(function () {
        var s2 = $(this);
        s2.select2({
            minimumInputLength: 3,
            ajax: {
                url: s2.data("url"),
                dataType: 'json',
            },
        });
    });



});
