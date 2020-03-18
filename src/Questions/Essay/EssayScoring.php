<?php
declare(strict_types=1);

namespace srag\asq\Questions\Essay;

use Exception;
use ilNumberInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use srag\asq\Domain\QuestionDto;
use srag\asq\Domain\Model\AbstractConfiguration;
use srag\asq\Domain\Model\Question;
use srag\asq\Domain\Model\Answer\Answer;
use srag\asq\Domain\Model\Answer\Option\AnswerOptions;
use srag\asq\Domain\Model\Scoring\AbstractScoring;
use srag\asq\Domain\Model\Scoring\TextScoring;
use srag\asq\UserInterface\Web\Fields\AsqTableInput;
use srag\asq\UserInterface\Web\Fields\AsqTableInputFieldDefinition;

/**
 * Class EssayScoring
 *
 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class EssayScoring extends AbstractScoring { 
    const VAR_TEXT_MATCHING = 'es_text_matching';
    const VAR_SCORING_MODE = 'es_scoring_mode';
    const VAR_POINTS = 'es_points';
    
    const VAR_ANSWERS_ANY = 'es_answers_any';
    const VAR_ANSWERS_ALL = 'es_answers_all';
    const VAR_ANSWERS_ONE = 'es_answers_one';
    const VAR_ANSWERS_COUNT = 'es_answers_count';
    
    const SCORING_MANUAL = 1;
    const SCORING_AUTOMATIC_ANY = 2;
    const SCORING_AUTOMATIC_ALL = 3;
    const SCORING_AUTOMATIC_ONE = 4;
    
    /**
     * @var EssayScoringConfiguration
     */
    protected $configuration;
    
    /**
     * @var string[]
     */
    private $words;
    
    /**
     * @var EssayScoringProcessedAnswerOption[]
     */
    private $answer_options;
    
    /**
     * @param QuestionDto $question
     */
    public function __construct($question) {
        parent::__construct($question);
        
        $this->configuration = $question->getPlayConfiguration()->getScoringConfiguration();
    }
    
    public function score(Answer $answer): float {
        if ($this->configuration->getScoringMode() === self::SCORING_MANUAL) {
            // TODO handle manual scoring
            throw new Exception("Dont run score on manual scoring");
        }
        else {
            $reached_points = $this->generateScore($answer->getText());

            return $reached_points;
        }
    }
    
    private function generateScore(string $text) : float {
        
        $text = strip_tags($text);
        
        if ($this->configuration->getMatchingMode() === TextScoring::TM_CASE_INSENSITIVE) {
            $text = strtoupper($text);
        }
        
        //ignore punctuation
        $this->words = explode(' ', preg_replace("#[[:punct:]]#", "", $text));
        
        $this->answer_options = array_map(function($answer_option) {
            return new EssayScoringProcessedAnswerOption($answer_option->getScoringDefinition(), $this->configuration->getMatchingMode() === TextScoring::TM_CASE_INSENSITIVE);
        }, $this->question->getAnswerOptions()->getOptions());
        
        $points = 0;
        
        foreach ($this->answer_options as $answer_option) {
            $found = $this->textContainsOption($answer_option);
            
            // one match found
            if ($found && $this->configuration->getScoringMode() === self::SCORING_AUTOMATIC_ONE) {
                return $this->configuration->getPoints();
            }
            
            // one error found
            if (!$found && $this->configuration->getScoringMode() === self::SCORING_AUTOMATIC_ALL) {
                return 0;
            }
            
            // match found
            if ($found && $this->configuration->getScoringMode() === self::SCORING_AUTOMATIC_ANY) {
                $points += $answer_option->getPoints();
            }
            
        }
        
        switch ($this->configuration->getScoringMode()) {
            case self::SCORING_AUTOMATIC_ALL:
                // all matches found
                return $this->configuration->getPoints();
            case self::SCORING_AUTOMATIC_ANY:
                return $points;
            case self::SCORING_AUTOMATIC_ONE:
                // no match found
                return 0;
        }
    }

    private function textContainsOption(EssayScoringProcessedAnswerOption $answer_option) : bool {
        $answer_words = $answer_option->getWords();
        
        switch($this->configuration->getMatchingMode()) {
            case TextScoring::TM_LEVENSHTEIN_1:
                $max_distance = 1;
                break;
            case TextScoring::TM_LEVENSHTEIN_2:
                $max_distance = 2;
                break;
            case TextScoring::TM_LEVENSHTEIN_3:
                $max_distance = 3;
                break;
            case TextScoring::TM_LEVENSHTEIN_4:
                $max_distance = 4;
                break;
            case TextScoring::TM_LEVENSHTEIN_5:
                $max_distance = 5;
                break;
            default:
                $max_distance = 0;
                break;
        }
        
        for ($i = 0; $i < (count($this->words) - (count($answer_words) - 1)); $i++) {
            $distance = 0;
            
            for ($j = 0; $j < count($answer_words); $j++) {
                $distance += levenshtein($this->words[$i + $j], $answer_words[$j]);
                
                if ($distance > $max_distance) {
                    break;
                }
            }
            
            if ($distance <= $max_distance) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function calculateMaxScore() {
        if ($this->configuration->getScoringMode() === self::SCORING_AUTOMATIC_ANY) {
            $this->max_score = 
                array_sum(
                    array_map(function($answer_option) {
                        return $answer_option->getScoringDefinition()->getPoints();
                    }, 
                    $this->question->getAnswerOptions()->getOptions()
                ));
        }
        else {
            $this->max_score = $this->configuration->getPoints();
        }
    }
    
    public function getBestAnswer(): Answer
    {
        $text = implode(' ',array_map(function($answer_option) {
            return $answer_option->getScoringDefinition()->getText();
        }, $this->question->getAnswerOptions()->getOptions()));
        
        return EssayAnswer::create($text);
    }
    
    /**
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config, AnswerOptions $options = null): ?array {
        global $DIC;
        
        /** @var $config EssayScoringConfiguration */
        
        $fields = [];
        
        $text_matching = TextScoring::getScoringTypeSelectionField(self::VAR_TEXT_MATCHING);
        $fields[self::VAR_TEXT_MATCHING] = $text_matching;
        
        $scoring_mode = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_text_matching'), self::VAR_SCORING_MODE);
        $scoring_mode->setRequired(true);
        
        $manual = new ilRadioOption($DIC->language()->txt('asq_label_manual_scoring'), self::SCORING_MANUAL);
        $manual->setInfo($DIC->language()->txt('asq_info_manual_scoring'));   
        $scoring_mode->addOption($manual);
        
        $any = new ilRadioOption($DIC->language()->txt('asq_label_automatic_any'), self::SCORING_AUTOMATIC_ANY);
        $any->setInfo($DIC->language()->txt('asq_info_automatic_any'));
        $any_options = new AsqTableInput($DIC->language()->txt('asq_label_answers'), 
             self::VAR_ANSWERS_ANY, 
             self::readAnswerOptionValues($options), 
             [
                 new AsqTableInputFieldDefinition($DIC->language()->txt('asq_label_answer_text'), 
                                                  AsqTableInputFieldDefinition::TYPE_TEXT, 
                                                  EssayScoringDefinition::VAR_TEXT),
                 new AsqTableInputFieldDefinition($DIC->language()->txt('asq_label_points'),
                                                  AsqTableInputFieldDefinition::TYPE_NUMBER,
                                                  EssayScoringDefinition::VAR_POINTS)
                                         ]);
        $any->addSubItem($any_options);
        $scoring_mode->addOption($any);
        
        $all = new ilRadioOption($DIC->language()->txt('asq_label_automatic_all'), self::SCORING_AUTOMATIC_ALL);
        $all->setInfo($DIC->language()->txt('asq_info_automatic_all'));
        $all_options = new AsqTableInput($DIC->language()->txt('asq_label_answers'),
            self::VAR_ANSWERS_ALL,
            self::readAnswerOptionValues($options),
            [
                new AsqTableInputFieldDefinition($DIC->language()->txt('asq_label_answer_text'),
                    AsqTableInputFieldDefinition::TYPE_TEXT,
                    EssayScoringDefinition::VAR_TEXT)
            ]);
        
        $all_points = new ilNumberInputGUI($DIC->language()->txt('asq_label_points'), self::VAR_ANSWERS_ALL . self::VAR_POINTS);
        $all_points->setSize(2);
        $all_points->setRequired(true);
        
        $all->addSubItem($all_options);
        $all->addSubItem($all_points);
        $scoring_mode->addOption($all);
        
        $one = new ilRadioOption($DIC->language()->txt('asq_label_automatic_one'), self::SCORING_AUTOMATIC_ONE);
        $one->setInfo($DIC->language()->txt('asq_info_automatic_one'));
        
        $one_options = new AsqTableInput($DIC->language()->txt('asq_label_answers'),
            self::VAR_ANSWERS_ONE,
            self::readAnswerOptionValues($options),
            [
                new AsqTableInputFieldDefinition($DIC->language()->txt('asq_label_answer_text'),
                    AsqTableInputFieldDefinition::TYPE_TEXT,
                    EssayScoringDefinition::VAR_TEXT)
            ]);
        
        $one_points = new ilNumberInputGUI($DIC->language()->txt('asq_label_points'), self::VAR_ANSWERS_ONE . self::VAR_POINTS);
        $one_points->setSize(2);
        $one_points->setRequired(true);
        
        $one->addSubItem($one_options);
        $one->addSubItem($one_points);
        $scoring_mode->addOption($one);
        
        $fields[self::VAR_SCORING_MODE] = $scoring_mode;
        
        if ($config !== null) {
            $text_matching->setValue($config->getMatchingMode());
            $scoring_mode->setValue($config->getScoringMode());
            $all_points->setValue($config->getPoints());
            $one_points->setValue($config->getPoints());
        }
        
        return $fields;
    }
    
    private static function readAnswerOptionValues(?Answeroptions $options) : array {
        if (is_null($options) || count($options->getOptions()) === 0) {
            return [];
        }
        
        $values = [];
        
        foreach($options->getOptions() as $option) {
            /** @var EssayScoringDefinition $definition */
            $definition = $option->getScoringDefinition();
            
            $new_item = [];
            $new_item[EssayScoringDefinition::VAR_TEXT] = $definition->getText();
            $new_item[EssayScoringDefinition::VAR_POINTS] = $definition->getPoints();
            $values[] = $new_item;     
        }
        
        return $values;
    }
    
    public static function readConfig()
    {
        $scoring_mode = intval($_POST[self::VAR_SCORING_MODE]);
        $points = 0.0;
        
        if ($scoring_mode === self::SCORING_AUTOMATIC_ALL) {
            $points = floatval($_POST[self::VAR_ANSWERS_ALL . self::VAR_POINTS]);
        }
        else if ($scoring_mode === self::SCORING_AUTOMATIC_ONE) {
            $points = floatval($_POST[self::VAR_ANSWERS_ONE . self::VAR_POINTS]);
        }
        
        return EssayScoringConfiguration::create(intval($_POST[self::VAR_TEXT_MATCHING]), 
                                                 $scoring_mode,
                                                 $points);
    }
    
    public static function isComplete(Question $question): bool
    {
        /** @var EssayScoringConfiguration $config */
        $config = $question->getPlayConfiguration()->getScoringConfiguration();
        
        if (empty($config->getScoringMode())) {
            return false;
        }
        
        if ($config->getScoringMode() !== self::SCORING_MANUAL) {
            foreach ($question->getAnswerOptions()->getOptions() as $option) {
                /** @var EssayScoringDefinition $option_config */
                $option_config = $option->getScoringDefinition();
                
                if (empty($option_config->getText()) ||
                    ($config->getScoringMode() === self::SCORING_AUTOMATIC_ANY && empty($option_config->getPoints())))
                {
                    return false;
                }
            }            
        }

        
        return true;
    }
}