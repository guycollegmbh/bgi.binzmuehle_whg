<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Widget;

use Contao\Database;
use Contao\StringUtil;
use Contao\Widget;

class FormApartmentSelect extends Widget
{
    protected $blnSubmitInput = true;
    protected $strTemplate = 'form_apartment_select';
    protected $strPrefix = 'widget widget-select';

    public function generate(): string
    {
        $grouped = $this->getApartmentOptions();

        $strOptions = '<option value="">-</option>';

        foreach ($grouped as $group => $options) {
            $strOptions .= sprintf(
                '<option disabled class="optgroup-label">── %s ──</option>',
                StringUtil::specialchars($group)
            );

            foreach ($options as $option) {
                $selected = ($option['value'] === $this->value) ? ' selected' : '';
                $strOptions .= sprintf(
                    '<option value="%s"%s>&nbsp;&nbsp;&nbsp;%s</option>',
                    StringUtil::specialchars($option['value']),
                    $selected,
                    StringUtil::specialchars($option['label'])
                );
            }
        }

        return sprintf(
            '<select name="%s" id="ctrl_%s" class="select%s"%s>%s</select>',
            $this->strName,
            $this->strId,
            ($this->strClass ? ' ' . $this->strClass : ''),
            $this->getAttributes(),
            $strOptions
        );
    }

    public function parse($arrAttributes = null): string
    {
        $this->widget = $this->generate();

        return parent::parse($arrAttributes);
    }

    protected function getApartmentOptions(): array
    {
        $grouped = [];
        $bezeichnung = $this->apartment_bezeichnung ?: 'Parkplatz';

        $query = "SELECT objektnummer, bezeichnung, adresse, etage, bruttomietzins FROM tl_apartments WHERE published = ? AND bezeichnung LIKE ?";
        $params = [1, $bezeichnung . '%'];

        if (stripos($bezeichnung, 'Jokerzimmer') !== false) {
            $query .= " AND adresse NOT LIKE ?";
            $params[] = 'Binderweg%';
        }

        $query .= " ORDER BY bezeichnung ASC, objektnummer ASC";

        $result = Database::getInstance()
            ->prepare($query)
            ->execute(...$params);

        while ($result->next()) {
            $parts = [$result->objektnummer];
            if ($result->adresse) {
                $parts[] = $result->adresse;
            }
            if ($result->etage) {
                $parts[] = $result->etage;
            }
            if ($result->bruttomietzins) {
                $parts[] = 'CHF ' . $result->bruttomietzins . '.-';
            }
            $label = implode(' - ', $parts);

            $grouped[$result->bezeichnung][] = [
                'value' => $result->objektnummer,
                'label' => $label,
            ];
        }

        return $grouped;
    }

    public function validate(): void
    {
        $value = $this->getPost($this->strName);
        $this->value = $value;
    }
}
