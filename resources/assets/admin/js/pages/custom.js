$(document).ready(function () {
    /* Delete Item */
    $('.sure-that button').on('click', function (e) {
        e.preventDefault();
        var form = $(this).parent('form');
        var title = form.data('title') ? form.data('title') : 'Are you sure?';
        var text = form.data('text') ? form.data('text') : "You won't be able to revert this!";
        var type = form.data('type') ? form.data('type') : 'warning';
        var confirmButtonText = form.data('button') ? form.data('button') : 'Yes, delete it!';

        swal({
                title: title,
                text: text,
                type: type,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmButtonText,
                closeOnConfirm: false
            },
            function (isConfirm) {
                if (isConfirm) {
                    form.submit();
                }
                else {
                    return false;
                }
            });
    });

    /* Check all inputs */
    $("#check_all").on("change", function () {
        $("input:checkbox.check_all").prop('checked', this.checked);

        if ($(this).hasClass('styled')) {
            $.uniform.update("input:checkbox.check_all");
        }
    });

    /* List action button */
    $(".do-list-action").on("click", function () {
        var route = $(this).data('route');
        var value = $(this).data('value');
        var key = $(this).data('key');

        var ids = [];
        $.each($("input.check_all:checked"), function () {
            ids.push($(this).val());
        });

        // provide route
        if (!route) {
            swal({
                title: 'Warning',
                text: 'Please provide route name',
                type: 'warning'
            });

            return false;
        }

        // check if ids are empty
        if (ids.length === 0) {
            swal({
                title: 'Warning',
                text: 'Please choose at least one item',
                type: 'warning'
            });

            return false;
        }

        var title = $(this).data('title') ? $(this).data('title') : 'Are you sure?';
        var text = $(this).data('text') ? $(this).data('text') : "You won't be able to revert this!";
        var type = $(this).data('type') ? $(this).data('type') : 'warning';
        var confirmButtonText = $(this).data('button') ? $(this).data('button') : 'Yes, do it!';

        swal({
                title: title,
                text: text,
                type: type,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmButtonText,
                closeOnConfirm: false
            },
            function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        method: "POST",
                        url: route,
                        data: {ids: ids, value: value, key: key}
                    })
                        .done(function (response) {
                            swal({
                                title: 'Updated',
                                text: response.message,
                                type: 'success'
                            });
                            window.location.reload(false);
                        })
                        .error(function (response) {

                            console.log(response);

                            swal({
                                title: 'Warning',
                                text: "There isn't any data to update!",
                                type: 'warning'
                            });
                        });
                }
                else {
                    return false;
                }
            });
    });
});
