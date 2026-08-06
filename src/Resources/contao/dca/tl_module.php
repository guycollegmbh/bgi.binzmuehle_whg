<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_module']['palettes']['apartments_list'] = '{title_legend},name,headline,type,hide_bewerben,jumpTo;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['apartments_detail'] = '{title_legend},name,headline,type,hide_bewerben;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['fields']['hide_bewerben'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo']['eval']['tl_class'] = 'clr';
