<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\ScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class KprimScoringDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class KprimScoringDefinition extends ScoringDefinition {

    const VAR_KPSD_CORRECT = 'kpsd_correct';
    
    /**
     * @var bool
     */
    protected $correct_value;

    public function __construct(bool $correct_value) {
        $this->correct_value = $correct_value;
    }
    
    /**
     * @return boolean
     */
    public function isCorrect_value()
    {
        return $this->correct_value;
    }

    /**
     * @return array
     */
    public static function getFields(): array
    {
        $fields = [];
        $fields[] = new AnswerOptionFormFieldDefinition(
            'Correct Answer',
            AnswerOptionFormFieldDefinition::TYPE_RADIO,
            self::VAR_KPSD_CORRECT,
            ["Right" => true, "Wrong" => false]);
        
        return $fields;
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\DomainModel\Answer\Option\ScoringDefinition::getValues()
     */
    public function getValues(): array
    {
        return [$this->correct_value];
    }

    public static function getValueFromPost(string $index)
    {
        return new KprimScoringDefinition(boolval($_POST[self::VAR_KPSD_CORRECT]));
    }

    public static function deserialize(stdClass $data)
    {
        return new KprimScoringDefinition($data->correct_value);
    }
}