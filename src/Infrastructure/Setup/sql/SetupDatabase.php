<?php
declare(strict_types=1);

namespace srag\asq\Infrastructure\Setup\sql;

use srag\asq\AsqGateway;
use srag\asq\Infrastructure\Persistence\QuestionType;
use srag\asq\Infrastructure\Persistence\SimpleStoredAnswer;
use srag\asq\Infrastructure\Persistence\EventStore\QuestionEventStoreAr;
use srag\asq\Infrastructure\Persistence\Projection\QuestionAr;
use srag\asq\Infrastructure\Persistence\Projection\QuestionListItemAr;
use srag\asq\Questions\Choice\Editor\ImageMap\ImageMapEditor;
use srag\asq\Questions\Choice\Editor\MultipleChoice\MultipleChoiceEditor;
use srag\asq\Questions\Choice\Form\Editor\ImageMap\ImageMapFormFactory;
use srag\asq\Questions\Choice\Form\Editor\MultipleChoice\MultipleChoiceFormFactory;
use srag\asq\Questions\Choice\Form\Editor\MultipleChoice\SingleChoiceFormFactory;
use srag\asq\Questions\Choice\Scoring\MultipleChoiceScoring;
use srag\asq\Questions\Cloze\Editor\ClozeEditor;
use srag\asq\Questions\Cloze\Form\ClozeFormFactory;
use srag\asq\Questions\Cloze\Scoring\ClozeScoring;
use srag\asq\Questions\ErrorText\Editor\ErrorTextEditor;
use srag\asq\Questions\ErrorText\Form\ErrorTextFormFactory;
use srag\asq\Questions\ErrorText\Scoring\ErrorTextScoring;
use srag\asq\Questions\Essay\Editor\EssayEditor;
use srag\asq\Questions\Essay\Form\EssayFormFactory;
use srag\asq\Questions\Essay\Scoring\EssayScoring;
use srag\asq\Questions\FileUpload\Editor\FileUploadEditor;
use srag\asq\Questions\FileUpload\Form\FileUploadFormFactory;
use srag\asq\Questions\FileUpload\Scoring\FileUploadScoring;
use srag\asq\Questions\Formula\Editor\FormulaEditor;
use srag\asq\Questions\Formula\Form\FormulaFormFactory;
use srag\asq\Questions\Formula\Scoring\FormulaScoring;
use srag\asq\Questions\Kprim\Editor\KprimChoiceEditor;
use srag\asq\Questions\Kprim\Form\KprimChoiceFormFactory;
use srag\asq\Questions\Kprim\Scoring\KprimChoiceScoring;
use srag\asq\Questions\Matching\Editor\MatchingEditor;
use srag\asq\Questions\Matching\Form\MatchingFormFactory;
use srag\asq\Questions\Matching\Scoring\MatchingScoring;
use srag\asq\Questions\Numeric\Editor\NumericEditor;
use srag\asq\Questions\Numeric\Form\NumericFormFactory;
use srag\asq\Questions\Numeric\Scoring\NumericScoring;
use srag\asq\Questions\Ordering\Editor\OrderingEditor;
use srag\asq\Questions\Ordering\Form\OrderingFormFactory;
use srag\asq\Questions\Ordering\Scoring\OrderingScoring;
use srag\asq\Questions\TextSubset\Editor\TextSubsetEditor;
use srag\asq\Questions\TextSubset\Form\TextSubsetFormFactory;
use srag\asq\Questions\TextSubset\Scoring\TextSubsetScoring;

/**
 * Class SetupDatabase
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class SetupDatabase
{
    private function __construct()
    {
    }


    public static function new() : SetupDatabase
    {
        return new self();
    }


    public function run() : void
    {
        QuestionEventStoreAr::updateDB();
        QuestionListItemAr::updateDB();
        QuestionAr::updateDB();
        SimpleStoredAnswer::updateDB();
        QuestionType::updateDB();
        QuestionType::truncateDB();

        $this->addQuestionTypes();
    }

    private function addQuestionTypes() : void
    {
        AsqGateway::get()->question()->addQuestionType(
            'asq_question_single_answer',
            SingleChoiceFormFactory::class,
            MultipleChoiceEditor::class,
            MultipleChoiceScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_multiple_answer',
            MultipleChoiceFormFactory::class,
            MultipleChoiceEditor::class,
            MultipleChoiceScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_kprim_answer',
            KprimChoiceFormFactory::class,
            KprimChoiceEditor::class,
            KprimChoiceScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_error_text',
            ErrorTextFormFactory::class,
            ErrorTextEditor::class,
            ErrorTextScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_image_map',
            ImageMapFormFactory::class,
            ImageMapEditor::class,
            MultipleChoiceScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_cloze',
            ClozeFormFactory::class,
            ClozeEditor::class,
            ClozeScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_numeric',
            NumericFormFactory::class,
            NumericEditor::class,
            NumericScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_formula',
            FormulaFormFactory::class,
            FormulaEditor::class,
            FormulaScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_text_subset',
            TextSubsetFormFactory::class,
            TextSubsetEditor::class,
            TextSubsetScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_ordering',
            OrderingFormFactory::class,
            OrderingEditor::class,
            OrderingScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_matching',
            MatchingFormFactory::class,
            MatchingEditor::class,
            MatchingScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_essay',
            EssayFormFactory::class,
            EssayEditor::class,
            EssayScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_file_upload',
            FileUploadFormFactory::class,
            FileUploadEditor::class,
            FileUploadScoring::class
        );

        AsqGateway::get()->question()->addQuestionType(
            'asq_question_ordering_text',
            OrderingFormFactory::class,
            OrderingEditor::class,
            OrderingScoring::class
        );
    }

    public function uninstall() : void
    {
        global $DIC;

        $DIC->database()->dropTable(QuestionEventStoreAr::STORAGE_NAME, false);
        $DIC->database()->dropTable(QuestionListItemAr::STORAGE_NAME, false);
        $DIC->database()->dropTable(QuestionAr::STORAGE_NAME, false);
        $DIC->database()->dropTable(SimpleStoredAnswer::STORAGE_NAME, false);
        $DIC->database()->dropTable(QuestionType::STORAGE_NAME, false);
    }
}
