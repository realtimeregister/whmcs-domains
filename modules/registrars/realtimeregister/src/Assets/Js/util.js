function setProgress(total, current)
{
    progress = (100 / total * current);
    if (progress < 100) {
        $(".rtr-progress-bar .progress-label").text(Math.floor(progress) + '%')
            .stop(true, true)
            .animate(
                {
                    width: Math.floor(progress) + '%'
                }, 200
            );
    } else {
        progressComplete();
    }
}

function showProgressBar()
{
    $(".rtr-progress-bar").show();
}

function progressComplete()
{
    $('.rtr-progress-bar .progress-label').addClass('complete');
    $(".rtr-progress-bar .progress-label").text('Complete!')
        .stop(true, true)
        .animate(
            {
                width: '100%'
            }, 200
        );
}