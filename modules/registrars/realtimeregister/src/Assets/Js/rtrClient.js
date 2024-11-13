(function ($) {
    // Controllers format: rtr.routes.<controller>.
    rtr.routes.removeRenewButton = function () {
        $('[href="cart.php?gid=renewals"]').hide();
    };

    $(document).ready(
        function () {
            // Remove language code selection when not needed
            $('#frmConfigureDomains').find('> .row > div:contains("Language Code")').parent().each(
                function () {
                    let self = $(this);
                    let domain = self.prevAll('.sub-heading').first().find('span').text();
                    if (domain.match(/^([0-9a-z\-]+\.)+([a-z\-]+|xn--[a-z0-9\-]+)$/) && !domain.startsWith('xn--')) {
                        self.remove();
                    }
                }
            );

            let key = $('input[name="totalIPS"]').val();

            $(document).on(
                'click',
                '.delete-ns',
                function (e) {
                    e.preventDefault();

                    let self = this, host = $(self).attr('data-ns');

                    if (confirm("Do you want to delete host: " + host)) {
                        $(self).closest('li').find('.spinner').addClass('active');
                        $.post(
                            document.location.href,
                            {
                                hostAction: "delete",
                                hostName: host
                            },
                            function (response) {
                                $(self).closest('li').remove();
                            }
                        );
                    }
                }
            );

            $(".remove-key-data").click(
                function (e) {
                    e.preventDefault();
                    $(this).closest('tr').remove();
                }
            );

            $(".add-keydata").click(
                function (e) {
                    e.preventDefault();
                    let total = $(this).closest('form').find('input[name="totalDNSsec"]').val();

                    $(this).closest('form').find('input[name="totalDNSsec"]').val(parseInt(total)  + 1);

                    // Flags
                    let flags = $('<select/>').attr(
                        {
                            name: 'flags[' + total + ']',
                            class: 'form-control'
                        }
                    ), options = [{key: '256', text: '256 (ZSK)'}, {key: '257', text: '257 (KSK)'}];

                    for (let i = 0; i < options.length; i++) {
                        flags.append($('<option/>').attr('value', options[i].key).text(options[i].text));
                    }

                    let algorithms = $('<select/>').attr(
                        {
                            name: 'algorithm[' + total + ']',
                            class: 'form-control'
                        }
                    ), algorithmsOptions = [
                    {key: '3', text: '3 (DSA/SHA1)'},
                    {key: '5', text: '5 (RSA/SHA-1)'},
                    {key: '6', text: '6 (DSA-NSEC3-SHA1)'},
                    {key: '7', text: '7 (RSASHA1-NSEC3-SHA1)'},
                    {key: '8', text: '8 (RSA/SHA-256)'},
                    {key: '10', text: '10 (RSA/SHA-512)'},
                    {key: '12', text: '12 (GOST R 34.10-2001)'},
                    {key: '13', text: '13 (ECDSA Curve P-256 with SHA-256)'},
                    {key: '14', text: '14 (ECDSA Curve P-384 with SHA-384)'},
                    {key: '15', text: '15 (Ed25519)'},
                    {key: '16', text: '16 (Ed448)'},
                    ];

                    for (let i = 0; i < algorithmsOptions.length; i++) {
                        algorithms.append($('<option/>').attr('value', algorithmsOptions[i].key).text(algorithmsOptions[i].text));
                    }

                    let textarea = $('<textarea/>').attr(
                        {
                            name: 'publicKey[' + total + ']',
                            type: 'text',
                            rows: 6,
                            class: 'form-control',
                            placeholder: 'Public key',
                            value: ''
                        }
                    );

                    $(this).closest('form').find('table tbody').find('tr:nth-child(1)').before(
                        $('<tr>')
                        .append(
                            $('<td>')
                            .append(flags)
                        )
                        .append(
                            $('<td>')
                            .append('3')
                        )
                        .append(
                            $('<td>')
                            .append(algorithms)
                        )
                        .append(
                            $('<td>')
                            .append(textarea)
                        )
                        .append(
                            $('<td class="remove">')
                            .append('<a href="#" class="text-danger remove-key-data"><i class="fas fa-trash fa-fw"></i></a>')
                        )
                    );
                    $(".remove-key-data").click(
                        function (e) {
                            e.preventDefault();
                            $(this).closest('tr').remove();
                        }
                    );
                }
            );

            $(".remove-ip").click(
                function (e) {
                    e.preventDefault();
                    $(this).closest('tr').remove();
                }
            );

            $('.add-ip').on('click',
                function (e) {
                    e.preventDefault();
                    let total = $(this).closest('form').find('input[name="totalIPS"]').val();

                    $(this).closest('form').find('input[name="totalIPS"]').val(parseInt(total)  + 1);

                    if (key === 13) {
                        return false;
                    }

                    let version = $('<select/>').attr(
                        {
                            name: 'ipVersion[' + total + ']',
                            class: 'ip-version'
                        }
                    ), options = ['V4', 'V6'];

                    for (let i = 0; i < options.length; i++) {
                        version.append($('<option/>').attr('value', options[i]).text(options[i]));
                    }

                    let input = $('<input/>').attr(
                        {
                            name: 'host[' + total + ']',
                            type: 'text',
                            placeholder: 'Your IP address.',
                            value: ''
                        }
                    );

                    $(this).closest('form').find('table').find('tr:nth-child(2)').before(
                        $('<tr>')
                        .append(
                            $('<td class="ip">')
                            .append(input)
                        )
                        .append(
                            $('<td class="version">')
                            .append(version)
                        )
                        .append(
                            $('<td class="remove">')
                            .append('<a href="#" class="text-danger remove-ip"><i class="fas fa-trash"></i></a>')
                        )
                    );

                    $(".remove-ip").click(
                        function (e) {
                            e.preventDefault();
                            $(this).closest('tr').remove();
                        }
                    );

                    key++;
                }
            );
        }
    );
})(jQuery);
