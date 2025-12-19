/* ========== TODO SPÄTER WIEDER EINBLENDEN: JavaScript für Grundriss-Vorschau ========== */
/* Zum Einblenden alle auskommentierte Zeilen mit "TODO: Später wieder aktivieren" reaktivieren */
/* Betrifft: Zeilen 383, 387, 409, 412-427, 438, 447-452, 456 */

document.addEventListener('DOMContentLoaded', function() {
    // ========== Element-Referenzen ========== //
    // TODO: Später wieder aktivieren - Grundriss-Element
    // const previewGrundriss = document.getElementById('preview-grundriss');
    const previewEtage = document.getElementById('preview-etage');

    // ========== DataTable initialisieren (MUSS ZUERST KOMMEN) ========== //
    const table = $('#apartments-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json'
        },
        pageLength: 50,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [1, 9, 10] }
        ],
        dom: 'rtip',
        lengthChange: false,
        info: true
    });

    // ========== Filter ohne Seitenneuladung ========== //
    const filterForm = document.querySelector('.filter-form');
    const filterSelects = filterForm.querySelectorAll('select');

    // Custom Search Function für DataTables
    $.fn.dataTable.ext.search.push(
        function(settings, searchData, index, rowData, counter) {
            if (settings.nTable.id !== 'apartments-table') {
                return true;
            }

            const status = document.getElementById('status').value;
            const zimmer = document.getElementById('zimmer').value;
            const bauetappe = document.getElementById('bauetappe').value;
            const zeile = document.getElementById('zeile').value;
            const minPrice = parseInt(document.getElementById('minPrice').value);
            const maxPrice = parseInt(document.getElementById('maxPrice').value);

            if (bauetappe && searchData[2] !== bauetappe) {
                return false;
            }

            if (zimmer && searchData[6] !== zimmer) {
                return false;
            }

            if (zeile && searchData[3] !== zeile) {
                return false;
            }

            if (status && searchData[9].indexOf(status) === -1) {
                return false;
            }

            if (searchData[8]) {
                const price = parseFloat(searchData[8].replace(/[^\d.]/g, ''));
                if (!isNaN(price) && (price < minPrice || price > maxPrice)) {
                    return false;
                }
            }

            return true;
        }
    );

    function applyFilters() {
        table.draw();
    }

    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            applyFilters();
        });
    });

    // ========== Price Range Slider ========== //
    const minPriceSlider = document.getElementById('minPrice');
    const maxPriceSlider = document.getElementById('maxPrice');
    const minPriceDisplay = document.getElementById('minPriceDisplay');
    const maxPriceDisplay = document.getElementById('maxPriceDisplay');

    const urlParams = new URLSearchParams(window.location.search);
    const urlMinPrice = urlParams.get('minPrice');
    const urlMaxPrice = urlParams.get('maxPrice');

    if (urlMinPrice) {
        minPriceSlider.value = urlMinPrice;
        minPriceDisplay.textContent = urlMinPrice;
    }
    if (urlMaxPrice) {
        maxPriceSlider.value = urlMaxPrice;
        maxPriceDisplay.textContent = urlMaxPrice;
    }

    let priceTimeout;

    minPriceSlider.addEventListener('input', function() {
        let minVal = parseInt(this.value);
        let maxVal = parseInt(maxPriceSlider.value);

        if (minVal >= maxVal) {
            this.value = maxVal - 50;
            minVal = maxVal - 50;
        }

        minPriceDisplay.textContent = minVal;

        clearTimeout(priceTimeout);
        priceTimeout = setTimeout(() => applyFilters(), 500);
    });

    maxPriceSlider.addEventListener('input', function() {
        let minVal = parseInt(minPriceSlider.value);
        let maxVal = parseInt(this.value);

        if (maxVal <= minVal) {
            this.value = minVal + 50;
            maxVal = minVal + 50;
        }

        maxPriceDisplay.textContent = maxVal;

        clearTimeout(priceTimeout);
        priceTimeout = setTimeout(() => applyFilters(), 500);
    });

    // ========== Default-Bilder ========== //
    // TODO: Später wieder aktivieren - Grundriss-Default
    // const defaultGrundriss = 'files/apartments/defaults/defaultgrundriss.png';
    const defaultEtage = 'files/apartments/defaults/defaultetage.png';

    function imageExists(src) {
        // TODO: Später wieder hinzufügen - && src !== defaultGrundriss
        return src && src !== '' && src !== defaultEtage;
    }

    // ========== Hover-Effekt für Tabellenzeilen ========== //
    $('#apartments-table tbody').on('mouseenter', 'tr', function() {
        // TODO: Später wieder aktivieren - Grundriss-Pfad aus data-Attribut holen
        // const grundrissPath = $(this).data('grundriss');
        const etagePath = $(this).data('etage');

        // Fade out - Start
        // TODO: Später wieder aktivieren - Grundriss Fade-out
        // previewGrundriss.style.opacity = '0.3';
        previewEtage.style.opacity = '0.3';

        setTimeout(function() {
            // ========== TODO: SPÄTER WIEDER AKTIVIEREN - GRUNDRISS-UPDATE - START ========== //
            /*
            if (imageExists(grundrissPath)) {
                previewGrundriss.src = grundrissPath;
                previewGrundriss.classList.add('has-image');
            } else {
                previewGrundriss.src = defaultGrundriss;
                previewGrundriss.classList.remove('has-image');
            }
            */
            // ========== TODO: SPÄTER WIEDER AKTIVIEREN - GRUNDRISS-UPDATE - ENDE ========== //

            if (imageExists(etagePath)) {
                previewEtage.src = etagePath;
                previewEtage.classList.add('has-image');
            } else {
                previewEtage.src = defaultEtage;
                previewEtage.classList.remove('has-image');
            }

            // Fade in - Ende
            // TODO: Später wieder aktivieren - Grundriss Fade-in
            // previewGrundriss.style.opacity = '1';
            previewEtage.style.opacity = '1';
        }, 150);
    });

    // ========== Bei Verlassen der Tabelle zu Default zurück ========== //
    $('#apartments-table').on('mouseleave', function() {
        setTimeout(function() {
            // Fade out - Start
            // TODO: Später wieder aktivieren - Grundriss Fade-out
            // previewGrundriss.style.opacity = '0.3';
            previewEtage.style.opacity = '0.3';

            setTimeout(function() {
                // Zurück zu Default-Bildern
                // TODO: Später wieder aktivieren - Grundriss zurücksetzen
                // previewGrundriss.src = defaultGrundriss;
                previewEtage.src = defaultEtage;
                // TODO: Später wieder aktivieren - Grundriss has-image Klasse entfernen
                // previewGrundriss.classList.remove('has-image');
                previewEtage.classList.remove('has-image');

                // Fade in - Ende
                // TODO: Später wieder aktivieren - Grundriss Fade-in
                // previewGrundriss.style.opacity = '1';
                previewEtage.style.opacity = '1';
            }, 150);
        }, 300);
    });
});
