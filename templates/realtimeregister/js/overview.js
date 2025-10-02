document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll("table.dns-overview-form select");

    // Bind to all and future selects
    selects.forEach(elm => {
            elm.addEventListener('change', () => onChangeEvent(elm));
            onChangeEvent(elm);
        }
    );

    const deleteBtn = document.querySelectorAll("table.dns-overview-form .delete-row-btn");

    // Bind to all and future delete buttons
    deleteBtn.forEach(elm => {
            elm.addEventListener('click', () => {
                // we don't want to remove the last row, because it is our template, instead we wipe the content
                if (elm.parentElement.parentElement.parentElement.childElementCount === 1 ) {
                    for (let child of elm.parentElement.parentElement.children) {
                        for (let i= 0; i < child.children.length; i++) {
                           if (child.children[i].tagName.toLowerCase() === 'input' || child.children[i].tagName.toLowerCase() === 'select') {
                               child.children[i].value = '';
                           }
                        }
                    }
                } else {
                    elm.parentElement.parentElement.remove();
                }
            });
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

        // Extract the current index from any input name like dns-items[1][...]
        const regex = /dns-items\[(\d+)\]/;
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
        newRow.querySelectorAll("input, select, button, textarea").forEach(el => {
            // Clear values
            if (el.tagName === "SELECT") {
                el.selectedIndex = 0;
                el.addEventListener('change', function() {
                    onChangeEvent(this);
                });
            } else if(el.tagName === 'BUTTON') {
                el.addEventListener('click', () => el.parentElement.parentElement.remove());
            } else if(el.tagName === 'DIV') {
                el.remove();
            } else {
                el.value = "";
            }
            el.classList.remove('is-invalid');

            // Update name attribute
            if (el.name) {
                el.name = el.name.replace(regex, `dns-items[${newIndex}]`);
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