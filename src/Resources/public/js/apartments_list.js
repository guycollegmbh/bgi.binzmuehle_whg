/* ========== TODO SPÄTER WIEDER EINBLENDEN: JavaScript für Grundriss-Vorschau ========== */
/* Zum Einblenden alle auskommentierte Zeilen mit "TODO: Später wieder aktivieren" reaktivieren */
/* Betrifft: Zeilen 383, 387, 409, 412-427, 438, 447-452, 456 */

document.addEventListener('DOMContentLoaded', function() {
    // ========== Dynamische Top-Position für Table Header ========== //
    function updateTableHeaderPosition() {
        const previewContainer = document.querySelector('.preview-filter-container');
        const tableHead = document.querySelector('#apartments-table thead');

        if (previewContainer && tableHead) {
            const containerHeight = previewContainer.offsetHeight;
            tableHead.style.top = containerHeight + 'px';
        }
    }

    // Initial setzen
    updateTableHeaderPosition();

    // Bei Fenstergrößenänderung neu berechnen
    window.addEventListener('resize', updateTableHeaderPosition);

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
            { orderable: false, targets: [1, 9, 10] },
            { orderData: [5], targets: [5] }
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

            const statusEl = document.getElementById('status');
            const zimmerEl = document.getElementById('zimmer');
            const bauetappeEl = document.getElementById('bauetappe');
            const zeileEl = document.getElementById('zeile');
            const etageEl = document.getElementById('etage');

            const status = statusEl ? statusEl.value : '';
            const zimmer = zimmerEl ? zimmerEl.value : '';
            const bauetappe = bauetappeEl ? bauetappeEl.value : '';
            const zeile = zeileEl ? zeileEl.value : '';
            const etage = etageEl ? etageEl.value : '';
            // TODO: Wieder aktivieren wenn Bruttomiete-Spalte eingeblendet wird
            // const minPrice = parseInt(document.getElementById('minPrice').value);
            // const maxPrice = parseInt(document.getElementById('maxPrice').value);

            // Spaltenindizes (aktuell):
            // 0: Objektnummer
            // 1: Bezeichnung
            // 2: Bauetappe
            // 3: Zeile (versteckt)
            // 4: Adresse
            // 5: Etage
            // 6: Zimmer
            // 7: Fläche
            // 8: Bruttomiete
            // 9: Status
            // 10: Details

            const minAreaEl = document.getElementById('minArea');
            const maxAreaEl = document.getElementById('maxArea');
            const minArea = minAreaEl ? parseFloat(minAreaEl.value) : 0;
            const maxArea = maxAreaEl ? parseFloat(maxAreaEl.value) : 99999;

            if (bauetappe && searchData[2] !== bauetappe) {
                return false;
            }

            if (zimmer && searchData[6] !== zimmer) {
                return false;
            }

            if (zeile && searchData[3] !== zeile) {
                return false;
            }

            if (etage && searchData[5].trim() !== etage.trim()) {
                return false;
            }

            if (status && searchData[9].indexOf(status) === -1) {
                return false;
            }

            if (searchData[7]) {
                const area = parseFloat(searchData[7].replace(/[^\d.]/g, ''));
                if (!isNaN(area) && (area < minArea || area > maxArea)) {
                    return false;
                }
            }

            const minPriceEl = document.getElementById('minPrice');
            const maxPriceEl = document.getElementById('maxPrice');
            const minPrice = minPriceEl ? parseFloat(minPriceEl.value) : 0;
            const maxPrice = maxPriceEl ? parseFloat(maxPriceEl.value) : 99999;

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
    // TODO: Wieder aktivieren wenn Bruttomiete Range-Slider eingeblendet wird
    const minPriceSlider = document.getElementById('minPrice');
    const maxPriceSlider = document.getElementById('maxPrice');
    const minPriceDisplay = document.getElementById('minPriceDisplay');
    const maxPriceDisplay = document.getElementById('maxPriceDisplay');

    if (minPriceSlider && maxPriceSlider) {
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
    }

    // ========== Area Range Slider ========== //
    const minAreaSlider = document.getElementById('minArea');
    const maxAreaSlider = document.getElementById('maxArea');
    const minAreaDisplay = document.getElementById('minAreaDisplay');
    const maxAreaDisplay = document.getElementById('maxAreaDisplay');

    if (minAreaSlider && maxAreaSlider) {
        let areaTimeout;

        minAreaSlider.addEventListener('input', function() {
            let minVal = parseInt(this.value);
            let maxVal = parseInt(maxAreaSlider.value);

            if (minVal >= maxVal) {
                this.value = maxVal - 1;
                minVal = maxVal - 1;
            }

            minAreaDisplay.textContent = minVal;

            clearTimeout(areaTimeout);
            areaTimeout = setTimeout(() => applyFilters(), 500);
        });

        maxAreaSlider.addEventListener('input', function() {
            let minVal = parseInt(minAreaSlider.value);
            let maxVal = parseInt(this.value);

            if (maxVal <= minVal) {
                this.value = minVal + 1;
                maxVal = minVal + 1;
            }

            maxAreaDisplay.textContent = maxVal;

            clearTimeout(areaTimeout);
            areaTimeout = setTimeout(() => applyFilters(), 500);
        });
    }

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
