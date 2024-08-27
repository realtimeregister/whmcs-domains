$(document).ready(
    function () {
        const moduleName = 'realtimeregister';
        const configContainer = $('#' + moduleName + 'config');

        configContainer.on(
            'click', '.import-wizard', function (e) {
                e.preventDefault();

                const contentArea = $('#contentarea');

                $.post(
                    window.location.href,
                    {
                        action: 'importWizard',
                        module: moduleName,
                    },
                    function (response) {
                        contentArea.html(response);
                        window.scrollTo(0,0);
                    },
                    "html"
                ).fail(
                    function (e) {
                    }
                );
            }
        );
    }
);