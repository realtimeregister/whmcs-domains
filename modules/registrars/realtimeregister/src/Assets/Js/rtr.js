(function ($) {
    rtr = $.extend(
        {
            controller: [],
            params: null,
            routes: {},
            error: null
        },
        rtr
    );

    $(document).ready(
        function () {
            // Initialize router controller.
            $.each(
                rtr.controller,
                function (i, controller) {
                    if ($.isFunction(rtr.routes[controller])) {
                        rtr.routes[controller]();
                    }
                }
            );
        }
    );
})(jQuery);
