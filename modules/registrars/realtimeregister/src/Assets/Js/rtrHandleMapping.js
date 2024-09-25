$(document).ready(
    function () {
        $('form#frmDomainContactModification > div.row > div[class^=col-]').each(
            function () {
                let c = $(this);
                let radio_contact = c.find('input[type=radio][name^=wc][value=contact]');
                let role = radio_contact.attr('name').match(/\[([^\]]+)/)[1];
                let contact_custom_fields = c.find('> .form-group');

                if (contact_ids[role]) {
                    radio_contact.click();
                    c.find('select[name^=sel] option[value=' + contact_ids[role] + ']').prop('selected', true);
                    contact_custom_fields.hide();
                }

                let options = {duration: 'fast', easing: 'linear'};
                radio_contact.on(
                    'click',
                    function () {
                        contact_custom_fields.hide(options); }
                );
                c.find('input[type=radio][name^=wc][value=custom]').on(
                    'click',
                    function () {
                        contact_custom_fields.show(options);
                    }
                );
            }
        )
    }
);
