<?php
declare(strict_types = 1);

namespace srag\asq\UserInterface\Web\Form;

use srag\asq\Domain\Model\QuestionPlayConfiguration;
use srag\asq\Domain\Model\Answer\Option\AnswerOption;
use srag\asq\Domain\Model\Answer\Option\AnswerOptions;
use srag\asq\UserInterface\Web\Fields\AsqTableInputFieldDefinition;
use srag\asq\Domain\QuestionDto;

/**
 * Class QuestionFormFactory
 *
 * Contains all the factories needed to create a Question object
 *
 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class QuestionFormFactory
{
    /**
     * @var IObjectFactory
     */
    protected $editor_config_factory;

    /**
     * @var IObjectFactory
     */
    protected $scoring_config_factory;

    /**
     * @var IAnswerOptionFactory
     */
    protected $editor_definition_factory;

    /**
     * @var IAnswerOptionFactory
     */
    protected $scoring_definition_factory;

    /**
     * @param IObjectFactory $editor_config_factory
     * @param IObjectFactory $scoring_config_factory
     * @param IAnswerOptionFactory $editor_definition_factory
     * @param IAnswerOptionFactory $scoring_definition_factory
     */
    public function __construct(
        IObjectFactory $editor_config_factory,
        IObjectFactory $scoring_config_factory,
        IAnswerOptionFactory $editor_definition_factory,
        IAnswerOptionFactory $scoring_definition_factory)
    {
        $this->editor_config_factory = $editor_config_factory;
        $this->scoring_config_factory = $scoring_config_factory;
        $this->editor_definition_factory = $editor_definition_factory;
        $this->scoring_definition_factory = $scoring_definition_factory;
    }

    /**
     * @param QuestionPlayConfiguration $config
     * @return array
     */
    public function getFormFields(QuestionPlayConfiguration $config) : array
    {
        return array_merge(
            $this->editor_config_factory->getFormfields($config->getEditorConfiguration()),
            $this->scoring_config_factory->getFormfields($config->getScoringConfiguration()));
    }

    /**
     * @return QuestionPlayConfiguration
     */
    public function readQuestionPlayConfiguration() : QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            $this->editor_config_factory->readObjectFromPost(),
            $this->scoring_config_factory->readObjectFromPost());
    }

    /**
     * @return QuestionPlayConfiguration
     */
    public function getDefaultPlayConfiguration() : QuestionPlayConfiguration
    {
        return QuestionPlayConfiguration::create(
            $this->editor_config_factory->getDefaultValue(),
            $this->scoring_config_factory->getDefaultValue());
    }

    /**
     * @return AsqTableInputFieldDefinition[]
     */
    public function getAnswerOptionDefinitions(?QuestionPlayConfiguration $play) : array
    {
        return array_merge(
            $this->editor_definition_factory->getTableColumns($play),
            $this->scoring_definition_factory->getTableColumns($play));
    }

    /**
     * @return bool
     */
    public function hasAnswerOptions() : bool
    {
        return count($this->getAnswerOptionDefinitions(null)) > 0;
    }

    /**
     * Returns AsqTableInput Options array
     *
     * @return array
     */
    public function getAnswerOptionConfiguration() : array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAnswerOptionValues(?AnswerOptions $options) : array
    {
        if (is_null($options))
        {
            return [];
        }

        return array_map(function($option) {
            /** @var $option AnswerOption */
            return array_merge(
                $this->editor_definition_factory->getValues($option->getDisplayDefinition()),
                $this->scoring_definition_factory->getValues($option->getScoringDefinition()));
        }, $options->getOptions());
    }

    /**
     * @return AnswerOptions
     */
    public function readAnswerOptions(array $values) : AnswerOptions
    {
        $options = [];
        $i = 0;
        foreach ($values as $value) {
            $i += 1;

            $options[] = AnswerOption::create(
                strval($i),
                $this->editor_definition_factory->readObjectFromValues($value),
                $this->scoring_definition_factory->readObjectFromValues($value));
        }

        return AnswerOptions::create($options);
    }

    /**
     * @return array
     */
    public function getScripts() : array
    {
        return[];
    }

    /**
     * @param QuestionDto $question
     * @return QuestionDto
     */
    public function performQuestionPostProcessing(QuestionDto $question) : QuestionDto
    {
        // virtual method
        return $question;
    }
}