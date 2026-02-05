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
        $options = $this->getApartmentOptions();

        $strOptions = '<option value="">-</option>';

        foreach ($options as $option) {
            $selected = ($option['value'] === $this->value) ? ' selected' : '';
            $strOptions .= sprintf(
                '<option value="%s"%s>%s</option>',
                StringUtil::specialchars($option['value']),
                $selected,
                StringUtil::specialchars($option['label'])
            );
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
        $options = [];
        $bezeichnung = $this->apartment_bezeichnung ?: 'Parkplatz';

        $result = Database::getInstance()
            ->prepare("SELECT objektnummer, bezeichnung FROM tl_apartments WHERE published = ? AND bezeichnung LIKE ? ORDER BY objektnummer ASC")
            ->execute(1, $bezeichnung . '%');

        while ($result->next()) {
            $options[] = [
                'value' => $result->objektnummer,
                'label' => $result->bezeichnung . ' - ' . $result->objektnummer,
            ];
        }

        return $options;
    }

    public function validate(): void
    {
        $value = $this->getPost($this->strName);
        $this->value = $value;
    }
}
