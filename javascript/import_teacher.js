(function ($) {

    $(function () {
        // define all selectboxes by this
        $('.exacomp-schooltype-grid-mapper-for-all').on('change', function () {
            var valueForAll = $(this).val();
            $('.exacomp-schooltype-grid-mapper').val(valueForAll);
        });

        // reset "for-all" selectboxes if some another was changed
        $('.exacomp-schooltype-grid-mapper').on('change', function () {
            $('.exacomp-schooltype-grid-mapper-for-all').val(0);
        });
    });

})(jQueryExacomp);