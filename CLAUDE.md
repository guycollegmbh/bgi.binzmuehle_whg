# BGI Binzmühle – ApartmentsBundle CLAUDE.md

Vollständige Projektdokumentation für Claude Code. Letzter Stand: 2026-05-29.

---

## Projekt-Übersicht

Contao 5.3 CMS Bundle für die Wohnungsvermietungswebsite **binzmuehle.ch**.  
Verwaltet Mietwohnungen, Jokerzimmer und zugehörige Medien (Grundrisse, Lagepläne, PDFs).

- **Bundle**: `Guycollegmbh\ApartmentsBundle`
- **LIVE-Server**: `/home/baduvemo/public_html/binzmuehle.ch/contao53/`
- **DB-Tabelle**: `tl_apartments`

---

## Dateistruktur

```
src/
├── ApartmentsBundle.php
├── ContaoManager/Plugin.php
├── Controller/
│   ├── Backend/ApartmentsImportController.php   ← Excel-Import + Datei-Sync
│   └── FrontendModule/ApartmentsListController.php
├── DependencyInjection/ApartmentsExtension.php
├── EventListener/GetParameterInsertTagListener.php  ← {{whg_get::PARAM}}
├── Module/
│   ├── ApartmentsListModule.php    ← Listenansicht (PHP-Logik)
│   └── ApartmentsDetailModule.php  ← Detailseite (PHP-Logik)
├── Resources/
│   ├── contao/
│   │   ├── config/config.php
│   │   ├── dca/
│   │   │   ├── tl_apartments.php   ← DB-Schema + Backend-Felder
│   │   │   └── tl_form_field.php
│   │   ├── languages/en/
│   │   │   └── tl_apartments.php
│   │   └── templates/
│   │       ├── mod_apartments_list.html.twig    ← Listenansicht-Template
│   │       ├── mod_apartments_detail.html.twig  ← Detailseite-Template
│   │       ├── form_apartment_select.html5       ← Widget-Template
│   │       └── be_apartments_import.html5        ← Backend-Import-Template
│   └── public/
│       ├── css/
│       │   ├── apartments_list.css   ← Styles Listenansicht
│       │   └── apartments_detail.css ← Styles Detailseite
│       └── js/
│           └── apartments_list.js    ← DataTables, Filter, Hover-Vorschau
└── Widget/FormApartmentSelect.php    ← Custom Contao Widget
```

---

## DB-Felder (tl_apartments)

| Feld | Typ | Beschreibung |
|---|---|---|
| `id` | int, PK | Auto-Increment |
| `objektnummer` | string(255) | z.B. `30.1.1` |
| `bezeichnung` | string(255) | `Wohnung`, `Jokerzimmer`, `Parkplatz`, etc. |
| `bauetappe` | string(64) | `1` oder `2` |
| `zeile` | string(64) | `Zeile 1`–`Zeile 5` |
| `adresse` | string(64) | z.B. `Zelghalde 30` |
| `etage` | string(64) | `UG`, `EG`, `1.OG`, `2.OG`, `3.OG`, `DG`, `EG/1.OG` |
| `zimmer` | string(64) | `2`, `2.5`, `3.5`, `4.5`, `5.5` |
| `flaeche` | string(255) | m²-Wert als Text |
| `nettomietzins` | string(255) | CHF-Betrag als Text |
| `nebenkosten` | string(255) | CHF-Betrag als Text |
| `bruttomietzins` | string(255) | CHF-Betrag als Text |
| `imagegrundriss` | binary(16) | UUID → `files/apartments/visualGrundriss/` |
| `imageetage` | binary(16) | UUID → `files/apartments/visualEtage/` (Listenansicht-Hover) |
| `imageetagedetail` | binary(16) | UUID → `files/apartments/visualEtageDetails/` (Detailseite) |
| `grundrisspdf` | binary(16) | UUID → `files/apartments/pdfGrundriss/` |
| `status` | string(64) | `Frei`, `Vermietet`, `Reserviert` |
| `published` | boolean | Veröffentlicht ja/nein |

---

## Listenansicht (ApartmentsListModule.php)

**Anzeige:** Wohnungen + Jokerzimmer, Bauetappe 2 ausgeblendet.

```php
WHERE published = 1 AND (bezeichnung = 'Wohnung' OR bezeichnung = 'Jokerzimmer') AND bauetappe != '2'
```

**Filter (GET-Parameter):** `status` (Default: leer = alle Status sichtbar), `zimmer`, `bauetappe`, `zeile`, `etage`, `minPrice`, `maxPrice`, `minArea`, `maxArea`

**CHF-Formatierung:** Apostroph als Tausendertrennzeichen, z.B. `1'920`

**DataTables Spaltenindizes:**
- 0: Objektnummer, 1: Bezeichnung, 2: Bauetappe, 3: Zeile (versteckt)
- 4: Adresse, 5: Etage, 6: Zimmer, 7: Fläche, 8: Bruttomiete, 9: Status, 10: Details

**Etage-Sortierung** via `data-order` Attribut:
- UG=0, EG=1, EG/1.OG=2, `EG/ 1.OG`=2 (mit Leerzeichen!), 1.OG=3, 2.OG=4, 3.OG=5, DG=6

---

## Detailseite (ApartmentsDetailModule.php)

- URL-Parameter: `?id=OBJEKTNUMMER` (URL-encoded)
- Objektnummer-Matching: HTML-Entities dekodieren + Klammern entfernen
- Lageplan: `imageetagedetail` → Fallback `files/apartments/defaults/defaultetagedetails.png`
- Layout: 2-Spalten-Grid (`1fr 380px`)
  - Links: Grundriss-Bild mit Colorbox/Lightbox
  - Rechts: Info-Box → Lageplan → Download + Bewerben-Button

---

## Lageplan-Visualisierung (Listenansicht)

### Struktur
- **Preview-Column** (links): Lageplan-Bild + SVG-Overlay mit Zeilen-Beschriftung
- **Filter-Column** (rechts): Geschoss-Vorschau + Filter-Formular

### Lageplan-Vorschau (#preview-etage)
- Default-Bild: `files/apartments/defaults/defaultetage.png`
- Beim Hover auf Tabellenzeile: `data-etage="{{ apartment.imageetage_path }}"` aus `<tr>`
- Bilder: `files/apartments/visualEtage/OBJEKTNUMMER.png`

### Geschoss-Vorschau (#preview-geschoss)
- Default: `files/apartments/defaults/defaultgeschoss.png`
- Bilder: `files/apartments/visualEtageGeschoss/` → `EG.png`, `DG.png`, `UG.png`, `1OG.png`, `2OG.png`, `3OG.png`, `EG-1OG.png`
- Mapping via JS: `EG/1.OG` und `EG/ 1.OG` → `EG-1OG.png`
- Mobile: ausgeblendet

### SVG-Overlay (Zeilen-Beschriftung)
```html
<svg class="lageplan-overlay" viewBox="0 0 650 400">
    <text x="90"  y="35">Zeile 5</text>
    <text x="215" y="35">Zeile 4</text>
    <text x="338" y="35">Zeile 3</text>
    <text x="462" y="35">Zeile 2</text>
    <text x="578" y="35">Zeile 1</text>
    <text x="110" y="370" fill="#e67e00">Zeile 4 und 5 ab 2029</text>
</svg>
```

### Strassennamen-Labels
- `.street-left`: Hürtstrasse (rotiert -90°, links)
- `.street-right`: Kügeliloostrasse (rotiert 90°, rechts)
- `.street-bottom`: Binzmühlestrasse (unten zentriert)

---

## Auskommentierte Features (TODO: Später einblenden)

In `mod_apartments_list.html.twig` und `apartments_list.js` sind folgende Features auskommentiert:

1. **Grundriss-Vorschau** (`#preview-grundriss`): Hover-Vorschau für Grundriss-Bild
2. **Bauetappe-Filter**: Filter-Dropdown für Bauetappe
3. **Nettomiete-Spalte**: Tabellenspalte Nettomietzins
4. **Nebenkosten-Spalte**: Tabellenspalte Nebenkosten
5. **Grundriss PDF-Spalte**: Download-Button in Tabelle

---

## URL-Parameter (Bewerben-Link)

```
/bewerben-informationen?objektnummer=XXX&zeile=X&adresse=XXX&etage=XXX&bruttomiete=XXX&zimmer=XXX
```

- Nur sichtbar bei: `status == 'frei'` UND `bezeichnung != 'Jokerzimmer'` UND Frontend-Gruppe 3
- Bei `status == 'Reserviert'`: Detailseite zugänglich, aber Bewerben-Button ausgeblendet
- `objektnummer`: Sonderzeichen entfernt (nur Zahlen, Punkte)
- `zeile`: `Zeile ` / `zeile ` Prefix entfernt
- Alle anderen via `|url_encode`
- Öffnet `target="_blank" rel="noopener"`

---

## Insert-Tags

Syntax: `{{whg_get::PARAMETERNAME}}`  
Listener: `src/EventListener/GetParameterInsertTagListener.php`  
Verfügbar: `objektnummer`, `adresse`, `zeile`, `etage`, `bruttomiete`, `zimmer`

**Wichtig:** Contao hat keinen eingebauten `{{get::...}}` Insert-Tag!

---

## Sync Workflow (Bilder/PDFs)

1. Datei per FTP hochladen:
   - `files/apartments/visualGrundriss/` → Grundriss-Bild
   - `files/apartments/visualEtage/` → Lageplan für Listenansicht (Hover)
   - `files/apartments/visualEtageDetails/` → Lageplan für Detailseite
   - `files/apartments/pdfGrundriss/` → PDF
2. **Contao Dateimanager** → Synchronisieren
3. **Backend Wohnungsmodul** → Sync PDFs
4. **Cache leeren**

Dateiname = bereinigte Objektnummer (nur Zahlen + Punkte, z.B. `30.1.2.png`)

---

## Excel-Import (ApartmentsImportController.php)

- 3-Schritt-Prozess: Datei hochladen → Sheet auswählen → Vorschau → Importieren
- Erste Datenzeile: Zeile 5 (4 Header-Zeilen: Titel, Datum, Leer, Spaltenheader)
- Bruttomietzins (Spalte 10): `getCalculatedValue()` da oft Formel
- Update: Status und Dateifelder werden NICHT überschrieben
- Normalisierung via `normalizeObjektnummer()`: Klammern entfernen

---

## FormApartmentSelect Widget

Zeigt: `Objektnummer - Adresse - Etage - CHF Bruttomietzins`  
Gruppiert via `<option disabled>` mit `──` Trennlinie (kein `<optgroup>`)

**Nur `status = 'Frei'` Objekte werden angezeigt** — Reservierte Objekte sind im Formular-Dropdown ausgeblendet (Stand 2026-07-02).

**Bauetappe 2 – Jokerzimmer ausgeblendet:**
```php
if (stripos($bezeichnung, 'Jokerzimmer') !== false) {
    $query .= " AND adresse NOT LIKE ?";
    $params[] = 'Binderweg%';
}
```
Sobald Bauetappe 2 startet → diesen Block entfernen.

---

## Bauetappe 2 – Checkliste zum Einblenden

Wenn Bauetappe 2 (Binderweg) startet:
- [ ] `src/Widget/FormApartmentSelect.php`: Block mit `Binderweg%` entfernen
- [ ] `mod_apartments_list.html.twig`: Bauetappe-Filter-Kommentar entfernen
- [ ] `ApartmentsListModule.php`: `AND bauetappe != '2'` aus allen Queries entfernen

---

## Bekannte Bugs / Lösungen

### mp_forms Bug (GELÖST)
- **Ursache**: `inspiredminds/contao-fieldset-duplication` v2.2.0 crasht bei mp_forms v5
- **Fix**: Version auf `"inspiredminds/contao-fieldset-duplication": "2.2.0"` pinnen

### mp_forms v5 Regeln
- Mind. **2 `mp_form_pageswitch`** Felder pro Formular
- Kein normales `submit` Feld – letzter `pageswitch` = Submit
- Nach Änderungen: Cache leeren (`php vendor/bin/contao-console cache:clear`)

---

## Contao 5.3 Besonderheiten

- `TL_ROOT` existiert NICHT → `System::getContainer()->getParameter('kernel.project_dir')`
- `specialchars()` als statische Methode auf Widget NICHT verfügbar → `Contao\StringUtil::specialchars()`
- `<optgroup>` / `<option>` Styling via CSS (color, font-weight) ist Browser-begrenzt

---

## Filter-State sessionStorage

- Key: `'apartments_filter'`
- Speichern: `beforeunload`
- Wiederherstellen: nur wenn `document.referrer` die Detailseite enthält (`wohnungen/details`)
- "Filter zurücksetzen" → Seite neu laden → kein Wiederherstellen

---

## Mobile (≤768px)

- `.preview-column { display: none }` – Lageplan ausgeblendet
- `.geschoss-preview-box { display: none }` – Geschoss ausgeblendet
- Tabelle als Card-Layout (`display: block`, `data-label` Attribute)
- Desktop: `<tr>` klickbar → Detailseite; Mobile: nur Details-Button

---

## CSS-Klassen Übersicht (apartments_list.css)

| Klasse | Zweck |
|---|---|
| `.preview-filter-container` | 2-Spalten Grid (sticky, z-index 100) |
| `.preview-column` | Linke Spalte: Lageplan |
| `.filter-column` | Rechte Spalte: Geschoss + Filter |
| `.preview-box` | Container für Vorschau-Bild |
| `.geschoss-preview-box` | Geschoss-Vorschau-Container |
| `.lageplan-wrapper` | relative Box für SVG-Overlay |
| `.lageplan-overlay` | SVG absolute über Lageplan |
| `.street-label` | Strassennamen-Labels |
| `.status-frei/reserviert/vermietet` | Status-Badge Farben |

---

## Adress-Zuordnung Zeilen

| Zeile | Adresse | Strasse |
|---|---|---|
| Zeile 1 | Kügeliloostrasse 65/67/69 | Kügeliloostrasse |
| Zeile 2 | Zelghalde 30–39 | Zelghalde |
| Zeile 3 | Zelghalde 30–39 | Zelghalde |
| Zeile 4 | Binderweg 1–10 | (ab 2029) |
| Zeile 5 | Binderweg 1–10 | (ab 2029) |
