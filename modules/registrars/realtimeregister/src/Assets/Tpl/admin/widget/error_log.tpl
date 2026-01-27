<div class="log-container">
    <form class="form-inline mx-1_5 pb-1" id="log-search-form">
        <div class="form-group">
            <input id="log-search-term" class="form-control" placeholder="{$LANG.rtr.widgets.errorlog.search_placeholder}">
        </div>
        <button id="log-search-submit" type="button" class="btn btn-default">{$LANG.rtr.widgets.errorlog.search}</button>
    </form>
    <div id="log-overview" class="mx-1_5">
        <span id="results-log-waiting-for-input">{$LANG.rtr.widgets.errorlog.loader}</span>
        <div id="results-log-page"></div>
        <div>
            <input id="current-log-page" type="hidden" value="1">
            <button id="previous-log-page" class="btn btn-default">{$LANG.rtr.widgets.errorlog.previous}</button>
            <button id="next-log-page" class="btn btn-default">{$LANG.rtr.widgets.errorlog.next}</button>
        </div>
        <div class="alert alert-success hidden" id="results-log-empty" style="margin: 1em;">{$LANG.rtr.widgets.errorlog.empty}</div>
    </div>

    <div class="modal fade" id="log-modal" tabindex="-1" role="dialog" aria-labelledby="myLogModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{$LANG.rtr.widgets.errorlog.close}"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$LANG.rtr.widgets.errorlog.details}</h4>
                </div>
                <div class="modal-body">
                    <div>
                        <strong>{$LANG.rtr.widgets.errorlog.filename}: </strong><span class="log-filename"></span>
                    </div>
                    <div>
                        <strong>{$LANG.rtr.widgets.errorlog.classname}: </strong><span class="log-classname"></span>
                    </div>
                    <div>
                        <strong>{$LANG.rtr.widgets.errorlog.linenumber}: </strong><span class="log-linenumber"></span>
                    </div>
                    <div>
                        <strong>{$LANG.rtr.widgets.errorlog.message}: </strong><span class="log-message"></span>
                    </div>
                    <div>
                        <strong>{$LANG.rtr.widgets.errorlog.time}: </strong><span class="log-time"></span>
                    </div>
                    <div>
                        <strong class="block">{$LANG.rtr.widgets.errorlog.stacktrace}: </strong><span class="stacktrace"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const logModal = $('#log-modal');
    let logs = new Map();

    const debounce = (callback, wait) => {
        let timeoutId = null;

        return (...args) => {
            window.clearTimeout(timeoutId);

            timeoutId = window.setTimeout(() => {
                callback.apply(null, args);
            }, wait);
        };
    }

    function onLogClick(elm) {
        let i = elm.target.getAttribute('data-log-id');
        let logItem = logs.get(parseInt(i));

        $('#log-modal .log-filename').text(logItem.filename);
        $('#log-modal .log-classname').text(logItem.exception_class);
        $('#log-modal .log-message').text(logItem.message);
        $('#log-modal .log-linenumber').text(logItem.line);
        $('#log-modal .log-time').text(logItem.created_at);

        $('#log-modal .stacktrace').html(logItem.details.split("\n").join("<br>"));
        logModal.modal("show");
    }

    const logSearchButton = document.getElementById('log-search-submit');
    document.getElementById('next-log-page').addEventListener('click', function () {
        let currentPage = parseInt(document.getElementById('current-log-page').value);
        fetchContent(currentPage + 1, document.getElementById('log-search-term').value);
    });

    document.getElementById('previous-log-page').addEventListener('click', function () {
        let currentPage = parseInt(document.getElementById('current-log-page').value);
        fetchContent(currentPage - 1, document.getElementById('log-search-term').value);
    });

    document.getElementById('log-search-term').addEventListener('keyup', debounce(() => {
        document.getElementById('current-log-page').value = '1';
        fetchContent(1, document.getElementById('log-search-term').value);
    }, 250));

    document.getElementById('log-search-submit').addEventListener('click', function () {
        document.getElementById('current-log-page').value = '1';
        fetchContent(1, document.getElementById('log-search-term').value);
    });

    const placeholder = document.getElementById('results-log-waiting-for-input');

    const resultLogPage = document.getElementById('results-log-page');

    function fetchContent(pageId, query) {
        document.getElementById('results-log-waiting-for-input').classList.remove('hidden');
        document.getElementById('log-search-form').disabled = true;
        document.getElementById('results-log-page').style.opacity = 0.3;
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'fetchErrorLogEntries',
                module: 'realtimeregister',
                pageId: pageId,
                searchTerm: query
            })
        }).then(async(asyncResponse) => {
            const response = await asyncResponse.json();
            if (response.result === 'success') {
                logs.clear();
                resultLogPage.innerHTML = '';

                response.logEntries.forEach(log => {
                    let elm = document.createElement('div');
                    elm.className = 'log';

                    let smallElm = document.createElement('span');
                    smallElm.className = 'log-created';
                    smallElm.innerText = log.created_at;
                    elm.appendChild(smallElm);

                    let buttonElm = document.createElement('button');
                    buttonElm.classList.add(...['log-details', 'btn', 'btn-default', 'block']);
                    buttonElm.innerHTML = '<small data-log-id="' + log.id + '">{$LANG.rtr.widgets.errorlog.show_detail}</small>';
                    buttonElm.setAttribute('data-log-id', log.id);
                    buttonElm.addEventListener('click', onLogClick);
                    elm.appendChild(buttonElm);

                    let logMessageElm = document.createElement('span');
                    logMessageElm.classList.add('log-message');
                    logMessageElm.innerHTML = log.message;
                    elm.appendChild(logMessageElm);

                    elm.appendChild(document.createElement('hr'));

                    resultLogPage.appendChild(elm);

                    logs.set(log.id, log);
                });

                document.getElementById('previous-log-page').classList.add('hidden');
                document.getElementById('next-log-page').classList.add('hidden');
                document.getElementById('current-log-page').value = response.pageId;
                if (response.pageId > 1) {
                   document.getElementById('previous-log-page').classList.remove('hidden');
                }
               document.getElementById('next-log-page').classList.remove('hidden');
                document.getElementById('results-log-waiting-for-input').classList.add('hidden');

                if (pageId === 1 && response.logEntries.length === 0) {
                    document.getElementById('results-log-empty').className.remove('hidden');
                }
                document.getElementById('results-log-page').style.opacity = 1;
            }
            document.getElementById('log-search-form').disabled = false;
        });
    }
    // Initial request
    fetchContent(1, document.getElementById('log-search-term').value);
</script>

<style>
    .log-container {
        height: 500px;
        overflow-y: scroll;

        &:first-child {
            margin-top: 1rem;
        }
    }
    .log {
        position: relative;
        padding: 0 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .log-created {
        position: absolute;
        left: 1.5rem;
    }
    .log-details {
        cursor: pointer;
        align-self: end;
        padding: 0 12px;
    }
    .block {
        display: block;
    }

    .mx-1_5 {
        margin-left: 1.5rem;
        margin-right: 1.5rem;
    }

    .pb-1 {
        padding-bottom: 1rem;
    }

</style>