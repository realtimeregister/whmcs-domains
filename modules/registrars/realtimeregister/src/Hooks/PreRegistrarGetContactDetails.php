<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;

class PreRegistrarGetContactDetails extends Hook
{
    public function __invoke(DataObject $vars)
    {
        App::assets()->head(<<<html
<script>
$(document).ready(function() {
    $('form#frmDomainContactModification > div.row > div[class^=col-]').each(function () {
        let self = $(this);
        let radio_contact = self.find('input[type=radio][name^=wc][value=contact]');
        let role = radio_contact.attr('name').match(/\[([^\]]+)/)[1];
        let contact_custom_fields = self.find('> .form-group');

        if (contact_ids && contact_ids[role]) {
            radio_contact.click();
            self.find('select[name^=sel] option[value=' + contact_ids[role] + ']').prop('selected', true);
            contact_custom_fields.hide();
        }

        let options = {duration: 'fast', easing: 'linear'};
        radio_contact.on('click', function () { contact_custom_fields.hide(options); });
        self.find('input[type=radio][name^=wc][value=custom]').on('click', function () { contact_custom_fields.show(options); });
    })
});

</script>
html);
    }
}
