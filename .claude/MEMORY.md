# Project: BGI Binzmühle Apartments Bundle

## Tech Stack
- Contao CMS 5.3 with Twig templating
- Custom Symfony bundle: `Guycollegmbh\ApartmentsBundle`
- PHP namespace: `Guycollegmbh\ApartmentsBundle`
- DB table: `tl_apartments` (all object types: Wohnung, Jokerzimmer, Parkplatz, Keller, Atelier, Moto Hallenplatz)

## Key Files
- **Module**: `src/Module/ApartmentsListModule.php`, `ApartmentsDetailModule.php`
- **Templates**: `src/Resources/contao/templates/mod_apartments_list.html.twig`, `mod_apartments_detail.html.twig`
- **CSS**: `src/Resources/public/css/apartments_list.css`, `apartments_detail.css`
- **DCA**: `src/Resources/contao/dca/tl_apartments.php`, `tl_form_field.php`
- **Widget**: `src/Widget/FormApartmentSelect.php` (custom form select for apartment objects)
- **Widget Template**: `src/Resources/contao/templates/form_apartment_select.html5`

## Important Patterns
- Contao 5.3: `specialchars()` is NOT available as static method on Widget - use `Contao\StringUtil::specialchars()` instead
- `<optgroup>` and `<option>` styling in native `<select>` is browser-limited (color, font-weight ignored for disabled options)
- Workaround: Use `<option disabled>` with `──` line decoration for visual grouping
- Widget needs `parse()` override to set `$this->widget = $this->generate()` for template
- Parkplatz types (Parkplatz, Parkplatz IV, Parkplatz E-Mob, Parkplatz IV E-Mob) are grouped via `LIKE 'Parkplatz%'`
- Contao Leads: may auto-add `leadStore` to palettes via PaletteManipulator - test before manually adding

## mp_forms (terminal42/contao-mp_forms) v5
- Requires **minimum 2 `mp_form_pageswitch`** fields per form (NOT normal Contao fieldset/submit)
- No regular `submit` field type allowed - last `mp_form_pageswitch` acts as submit
- `isValidFormFieldCombination()` requires `getNumberOfSteps() > 1`
- Error "Step 0 does not exist" = form has no `mp_form_pageswitch` fields or invalid config
- Steps are detected by field type `mp_form_pageswitch`, NOT `fieldsetStart`/`fieldsetStop`
- After adding/removing mp_forms fields: MUST clear cache (`php vendor/bin/contao-console cache:clear`)
- LIVE server: `/home/baduvemo/public_html/binzmuehle.ch/contao53/`

## Mobile Optimizations
- Media query: `@media (max-width: 768px)`
- Card-based table layout for list (block display, data-labels)
- Detail page: removed border-radius, box-shadow, padding for mobile
- Title split: Bezeichnung + Objektnummer on separate lines (mobile only)
- Desktop: connected via `::after { content: "\00a0-\00a0" }` pseudo-element

## URL Parameters
- Bewerben link: `?objektnummer=XXX&zeile=X&adresse=XXX&etage=XXX`
- Sonderzeichen removed from objektnummer via Twig `replace()`
- "Zeile " prefix removed from zeile value
- adresse + etage via `|url_encode` mitgegeben
- Bewerben-Link ist aktuell **auskommentiert** (`{# ... #}`) im Template
- URL auf `/bewerben` (nicht mehr testform)

## Insert-Tags (whg_get)
- Custom Insert-Tag Listener: `src/EventListener/GetParameterInsertTagListener.php`
- Registriert via `#[AsHook('replaceInsertTags')]`
- Syntax: `{{whg_get::PARAMETERNAME}}`
- Verfügbare Parameter: `objektnummer`, `adresse`, `zeile`, `etage`
- Contao hat keinen eingebauten `{{get::...}}` Insert-Tag!
- HTML-Element auf Formularseite: `{{whg_get::objektnummer}} - {{whg_get::adresse}} - {{whg_get::zeile}} - {{whg_get::etage}}`

## FormApartmentSelect Widget
- Zeigt: `Objektnummer - Adresse - Etage` (leere Felder werden übersprungen)
- Query: `SELECT objektnummer, bezeichnung, adresse, etage FROM tl_apartments`

## mp_forms Bug - GELÖST
- **Ursache**: `inspiredminds/contao-fieldset-duplication` v2.2.0 hat einen Bug mit mp_forms v5
  - `FormHookListener::onCompileFormFields()` ruft `FormManagerFactory->forFormId()` auf ohne zu prüfen ob es ein Multi-Page Formular ist
  - Das crasht bei normalen Formularen ohne `mp_form_pageswitch`-Felder
- **Fix**: Version pinnen auf `"inspiredminds/contao-fieldset-duplication": "2.2.0"` in composer.json WARTEN bis Bug gefixt ist
- **Hinweis**: Bug sollte bei inspiredminds gemeldet werden
