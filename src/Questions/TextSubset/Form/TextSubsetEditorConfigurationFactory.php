<?php
declare(strict_types = 1);

namespace srag\asq\Questions\TextSubset\Form;

use srag\CQRS\Aggregate\AbstractValueObject;
use srag\asq\UserInterface\Web\Form\AbstractObjectFactory;
use srag\asq\Questions\TextSubset\TextSubsetEditorConfiguration;
use ilNumberInputGUI;

/**
 * Class TextSubsetEditorConfigurationFactory

 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class TextSubsetEditorConfigurationFactory extends AbstractObjectFactory
{
    const VAR_REQUESTED_ANSWERS = 'tse_requested_answers';

    /**
     * {@inheritDoc}
     * @see \srag\asq\UserInterface\Web\Form\IObjectFactory::getFormfields()
     */
    public function getFormfields(?AbstractValueObject $value) : array
    {
        $fields = [];

        $requested_answers = new ilNumberInputGUI($this->language->txt('asq_label_requested_answers'), self::VAR_REQUESTED_ANSWERS);
        $requested_answers->setRequired(true);
        $requested_answers->setSize(2);
        $fields[self::VAR_REQUESTED_ANSWERS] = $requested_answers;

        if ($value !== null) {
            $requested_answers->setValue($value->getNumberOfRequestedAnswers());
        }

        return $fields;
    }

    /**
     * @return TextSubsetEditorConfiguration
     */
    public function readObjectFromPost() : AbstractValueObject
    {
        return TextSubsetEditorConfiguration::create($this->readInt(self::VAR_REQUESTED_ANSWERS));
    }

    /**
     * @return TextSubsetEditorConfiguration
     */
    public function getDefaultValue() : AbstractValueObject
    {
        return TextSubsetEditorConfiguration::create();
    }
}
