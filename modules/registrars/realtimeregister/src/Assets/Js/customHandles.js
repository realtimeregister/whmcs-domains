$('#propertiesModal').on('shown.bs.modal', function () {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'fetchProperties',
            module: 'realtimeregister',
        })
    }).then(async(response) => {
        const placeholder = document.getElementById('waiting-for-input');

        if (placeholder) {
            const res = await response.json();
            let placeholderReplacement = document.createElement('div');
            res.forEach((item) => {
                let elm = document.createElement('div');
                elm.classList.add('form-group');

                let label = document.createElement('label');
                label.classList.add('form-control-label');
                label.innerText = item.provider + ' (' + item.forType + ')';
                if (item.extra) {
                    label.innerText += ` (${item.extra})`
                }
                label.htmlFor = 'prop-' + item.provider + '-' + item.forType;

                let input = document.createElement('input');
                input.classList.add('form-control');
                input.id = 'prop-' + item.provider + '-' + item.forType;
                input.value = item.value;
                input.placeholder = 'Your custom handle';
                input.setAttribute('name', 'prop[' + item.provider + '][' + item.forType + ']');

                placeholderReplacement.appendChild(elm);
                placeholderReplacement.appendChild(label);
                placeholderReplacement.appendChild(input);

            });
            placeholder.replaceWith(placeholderReplacement);
        }
    })

    let closeButtonCustomProperties = document.getElementById('saveCustomProperties');

    closeButtonCustomProperties.addEventListener('click', onClose);

    async function onClose(e)
    {
        e.preventDefault();

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(new FormData(document.getElementById('propertiesForm')))
        }).then(async response => {
            let res = await response.json();

            if (res.result === 'success') {
                $('#propertiesModal').modal('hide');
                $('.bg-danger').hide();
                $('.handles-message').hide();
                $('.error-handles').hide();
                $('.error-message').hide();
                closeButtonCustomProperties.removeEventListener('click', onClose);
            } else if (res.handles) {
                $('.bg-danger').show();
                $('.handles-message').show();
                $('.error-handles').show().text(res.handles.join(', '))
            } else {
                $('.bg-danger').show();
                $('.error-message').show();
            }
        });
    }
});
