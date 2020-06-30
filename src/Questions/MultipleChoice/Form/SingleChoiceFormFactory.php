<?php
declare(strict_types = 1);

namespace srag\asq\Questions\MultipleChoice\Form;

use srag\asq\UserInterface\Web\Form\QuestionFormFactory;
use srag\asq\Domain\Model\Answer\Option\ImageAndTextDefinitionFactory;
use srag\asq\UserInterface\Web\PathHelper;
use ilLanguage;

/**
 * Class SingleChoiceFormFactory

 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class SingleChoiceFormFactory extends QuestionFormFactory
{
    use ChoiceQuestionPostProcessing;
    use PathHelper;

    public function __construct(ilLanguage $language)
    {
        parent::__construct(
            new SingleChoiceEditorConfigurationFactory($language),
            new MultipleChoiceScoringConfigurationFactory($language),
            new ImageAndTextDefinitionFactory($language),
            new SingleChoiceScoringDefinitionFactory($language)
        );
    }

    /**
     * {@inheritDoc}
     * @see \srag\asq\UserInterface\Web\Form\QuestionFormFactory::getScripts()
     */
    public function getScripts() : array
    {
        return [ $this->getBasePath(__DIR__) . 'src/Questions/MultipleChoice/MultipleChoiceAuthoring.js' ];
    }
}
