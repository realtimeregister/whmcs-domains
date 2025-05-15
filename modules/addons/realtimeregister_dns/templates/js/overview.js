document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll("table.dns-overview-form select");

    // Bind to all selects
    selects.forEach(elm => {
            elm.addEventListener('change', () => onChangeEvent(elm));
            onChangeEvent(elm);
        }
    );

    const button = document.getElementById("add-row-btn");
    button.addEventListener("click", function () {
        const table = document.querySelector("table.dns-overview-form");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));

        if (rows.length === 0) {
            return;
        }

        const lastRow = rows[rows.length - 1];
        const newRow = lastRow.cloneNode(true);

        // Extract the current index from any input name like dns-item[1][...]
        const regex = /dns-item\[(\d+)\]/;
        const lastInputs = lastRow.querySelectorAll("input, select, textarea");
        let currentIndex = 1;

        for (const input of lastInputs) {
            const match = input.name.match(regex);
            if (match) {
                currentIndex = Math.max(currentIndex, parseInt(match[1], 10));
            }
        }

        const newIndex = currentIndex + 1;

        // Update cloned row's inputs
        newRow.querySelectorAll("input, select, textarea").forEach(el => {
            // Clear values
            if (el.tagName === "SELECT") {
                el.selectedIndex = 0;
                el.addEventListener('change', function() {
                    onChangeEvent(this);
                });
            } else {
                el.value = "";
            }

            // Update name attribute
            if (el.name) {
                el.name = el.name.replace(regex, `dns-item[${newIndex}]`);
            }
        });

        tbody.appendChild(newRow);
    });

});

function onChangeEvent(elm)
{
    let prioElement = elm.parentElement.parentElement.querySelector('[name$="[prio]"]');

    if (elm.value === 'MX' || elm.value === 'SRV') {
        prioElement.style.display = '';
    } else {
        prioElement.style.display = 'none';
    }
}