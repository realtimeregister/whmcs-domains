const renewButton = document.querySelector('input[type="button"][data-toggle="modal"][data-target="#modalRenew"]');

if (renewButton) {
    renewButton.addEventListener('click', () => {
        if (document.getElementsByName('regperiod')[1].value !== '1') {
            $('#modalRenew').on('show.bs.modal', function (e) {
                document.getElementById('modalRenewBody').innerHTML = 'Please pay attention, the registration period is set to <b>' + document.getElementsByName('regperiod')[1].value + ' years</b>, are you sure you want to send the domain renewal request to the registrar?';
            });
        } else {
            document.getElementById('modalRenewBody').innerHTML = 'Are you sure you want to send the domain renewal request to the registrar?';
        }
    });
}