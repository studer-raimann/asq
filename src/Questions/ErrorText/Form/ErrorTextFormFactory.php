<?php
declare(strict_types = 1);

namespace srag\asq\Questions\ErrorText\Form;

use srag\asq\UserInterface\Web\Form\QuestionFormFactory;
use srag\asq\Domain\Model\Answer\Option\EmptyDefinitionFactory;
use srag\asq\UserInterface\Web\PathHelper;
use ilLanguage;

/**
 * Class ErrorTextFormFactory

 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class ErrorTextFormFactory extends QuestionFormFactory
{
    use PathHelper;

    public function __construct(ilLanguage $language)
    {
        parent::__construct(
            new ErrorTextEditorConfigurationFactory($language),
            new ErrorTextScoringConfigurationFactory($language),
            new EmptyDefinitionFactory($language),
            new ErrorTextScoringDefinitionFactory($language));
    }

    /**
     * {@inheritDoc}
     * @see \srag\asq\UserInterface\Web\Form\QuestionFormFactory::getScripts()
     */
    public function getScripts() : array
    {
        return [ $this->getBasePath(__DIR__) . 'src/Questions/ErrorText/ErrorTextAuthoring.js' ];
    }
}