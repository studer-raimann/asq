<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Test;

use srag\asq\Domain\Model\QuestionData;
use srag\asq\Domain\Model\Configuration\QuestionPlayConfiguration;
use srag\asq\Infrastructure\Persistence\QuestionType;
use srag\asq\Questions\Numeric\NumericAnswer;
use srag\asq\Questions\Numeric\Editor\Data\NumericEditorConfiguration;
use srag\asq\Questions\Numeric\Form\NumericFormFactory;
use srag\asq\Questions\Numeric\Scoring\Data\NumericScoringConfiguration;
use srag\asq\Questions\Numeric\Editor\NumericEditor;
use srag\asq\Questions\Numeric\Scoring\NumericScoring;
use srag\asq\Questions\Choice\Form\Editor\MultipleChoice\MultipleChoiceFormFactory;
use srag\asq\Questions\Choice\Editor\MultipleChoice\MultipleChoiceEditor;
use srag\asq\Questions\Choice\Scoring\MultipleChoiceScoring;
use srag\asq\Questions\Choice\Editor\MultipleChoice\Data\MultipleChoiceEditorConfiguration;
use srag\asq\Questions\Choice\Scoring\Data\MultipleChoiceScoringConfiguration;
use srag\asq\Domain\Model\Answer\Option\AnswerOptions;
use srag\asq\Domain\Model\Answer\Option\AnswerOption;
use srag\asq\Questions\Generic\Data\ImageAndTextDisplayDefinition;
use srag\asq\Questions\Choice\Scoring\Data\MultipleChoiceScoringDefinition;
use srag\asq\Questions\Choice\MultipleChoiceAnswer;
use srag\asq\Application\Exception\AsqException;

require_once 'QuestionTestCase.php';

/**
 * Class MultipleChoiceTest
 *
 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class MultipleChoiceTest extends QuestionTestCase
{
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Test\QuestionTestCase::getQuestions()
     */
    public function getQuestions() : array
    {
        return [
            'question 1' => $this->createQuestion(
                QuestionData::create('Question 1', '', '', '', 1),
                QuestionPlayConfiguration::create(
                    MultipleChoiceEditorConfiguration::create(false, 1),
                    MultipleChoiceScoringConfiguration::create()
                    ),
                    AnswerOptions::create([
                        AnswerOption::create('1',
                            ImageAndTextDisplayDefinition::create('1', 'blah.jpg'),
                            MultipleChoiceScoringDefinition::create(1, 0)),
                        AnswerOption::create('2',
                            ImageAndTextDisplayDefinition::create('2'),
                            MultipleChoiceScoringDefinition::create(2, 0)),
                        AnswerOption::create('3',
                            ImageAndTextDisplayDefinition::create('3', 'blah.jpg'),
                            MultipleChoiceScoringDefinition::create(3, 0)),
                        AnswerOption::create('4',
                            ImageAndTextDisplayDefinition::create('4'),
                            MultipleChoiceScoringDefinition::create(4, 0))
                    ])
                ),
            'question 2' => $this->createQuestion(
                QuestionData::create('Question 2', '', '', '', 1),
                QuestionPlayConfiguration::create(
                    MultipleChoiceEditorConfiguration::create(true, 2, 100),
                    MultipleChoiceScoringConfiguration::create()
                    ),
                AnswerOptions::create([
                    AnswerOption::create('1',
                        ImageAndTextDisplayDefinition::create('1', 'blah.jpg'),
                        MultipleChoiceScoringDefinition::create(1, 0)),
                    AnswerOption::create('2',
                        ImageAndTextDisplayDefinition::create('2'),
                        MultipleChoiceScoringDefinition::create(0, 0)),
                    AnswerOption::create('3',
                        ImageAndTextDisplayDefinition::create('3', 'blah.jpg'),
                        MultipleChoiceScoringDefinition::create(1, 0)),
                    AnswerOption::create('4',
                        ImageAndTextDisplayDefinition::create('4'),
                        MultipleChoiceScoringDefinition::create(0, 1))
                ])
                ),
            'question 3' => $this->createQuestion(
                QuestionData::create('Question 3', '', '', '', 1),
                QuestionPlayConfiguration::create(
                    MultipleChoiceEditorConfiguration::create(false, 3, 100),
                    MultipleChoiceScoringConfiguration::create()
                    ),
                AnswerOptions::create([
                    AnswerOption::create('1',
                        ImageAndTextDisplayDefinition::create('1', 'blah.jpg'),
                        MultipleChoiceScoringDefinition::create(2, -2)),
                    AnswerOption::create('2',
                        ImageAndTextDisplayDefinition::create('2'),
                        MultipleChoiceScoringDefinition::create(1, 0)),
                    AnswerOption::create('3',
                        ImageAndTextDisplayDefinition::create('3', 'blah.jpg'),
                        MultipleChoiceScoringDefinition::create(1, 0)),
                    AnswerOption::create('4',
                        ImageAndTextDisplayDefinition::create('4'),
                        MultipleChoiceScoringDefinition::create(-1, 1))
                ])
                )
        ];
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Test\QuestionTestCase::getAnswers()
     */
    public function getAnswers() : array
    {
        return [
            'answer 1' => MultipleChoiceAnswer::create([]),
            'answer 2' => MultipleChoiceAnswer::create(['1']),
            'answer 3' => MultipleChoiceAnswer::create(['4']),
            'answer 4' => MultipleChoiceAnswer::create(['1', '2']),
            'answer 5' => MultipleChoiceAnswer::create(['1', '3']),
            'answer 6' => MultipleChoiceAnswer::create(['1', '2', '3'])
        ];
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Test\QuestionTestCase::getExpectedScores()
     */
    public function getExpectedScores() : array
    {
        return [
            'question 1' => [
                'answer 1' => 0,
                'answer 2' => 1,
                'answer 3' => 4,
                'answer 4' => new AsqException('Too many answers "2" given for maximum allowed of: "1"'),
                'answer 5' => new AsqException('Too many answers "2" given for maximum allowed of: "1"'),
                'answer 6' => new AsqException('Too many answers "3" given for maximum allowed of: "1"'),
            ],
            'question 2' => [
                'answer 1' => 1,
                'answer 2' => 2,
                'answer 3' => 0,
                'answer 4' => 2,
                'answer 5' => 3,
                'answer 6' => new AsqException('Too many answers "3" given for maximum allowed of: "2"')
            ],
            'question 3' => [
                'answer 1' => -1,
                'answer 2' => 3,
                'answer 3' => -3,
                'answer 4' => 4,
                'answer 5' => 4,
                'answer 6' => 5
            ]
        ];
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Test\QuestionTestCase::getMaxScores()
     */
    public function getMaxScores() : array
    {
        return [
            'question 1' => 4,
            'question 2' => 3,
            'question 3' => 5
        ];
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Test\QuestionTestCase::getTypeDefinition()
     */
    public function getTypeDefinition() : QuestionType
    {
        return QuestionType::createNew(
            'multiple_choice',
            MultipleChoiceFormFactory::class,
            MultipleChoiceEditor::class,
            MultipleChoiceScoring::class
            );
    }
}
