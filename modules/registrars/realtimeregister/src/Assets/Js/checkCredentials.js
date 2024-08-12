$(document).ready(function () {
    const moduleName = 'realtimeregister';
    // Container
    const config_container = $('#' + moduleName + 'config');

    // Credentials
    const handleField = config_container.find('[name="customer_handle"]');
    const apiField = config_container.find('[name="rtr_api_key"]');
    const oteField = config_container.find('[type="checkbox"][name="test_mode"]');
    const ignoreSslField = config_container.find('[type="checkbox"][name="ignore_ssl"]');

    config_container.on('click', '.check-connection', function (e) {
        e.preventDefault();

        const btn = $(this);
        const text = btn.html();
        const result = config_container.find('.credentials-result');

        btn.css({'width': btn.outerWidth()});
        btn.html("<i class=\"fa fa-spin fa-spinner\"></i>");
        btn.addClass("disabled");
        btn.attr("disabled", "disabled");
        $.post(
            window.location.href,
            {
                action: 'checkConnection',
                module: moduleName,
                handle: handleField.val(),
                apiKey: apiField.val(),
                ote: oteField.is(":checked"),
                ignore_ssl: ignoreSslField.is(":checked"),
            }, function (response) {
                btn.html(text);
                btn.removeClass("disabled");
                if (response.connection === "true") {
                    result.html('<span class="status success">SUCCESSFUL</span>');
                } else {
                    result.html('<span class="status error"><strong>FAILED:</strong> ' + response.msg + '</span>');
                }
                btn.attr("disabled", false);
            }, "json"
        ).fail(function (e) {
            btn.html(text);
            btn.removeClass("disabled");
            btn.attr("disabled", false);
            result.html('<span class="status error"><strong>FAILED:</strong> Something went wrong!</span>');
        });
    });
});