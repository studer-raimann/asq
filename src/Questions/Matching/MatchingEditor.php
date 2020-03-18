<?php
declare(strict_types=1);

namespace srag\asq\Questions\Matching;

use ilNumberInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use ilSelectInputGUI;
use ilTemplate;
use srag\CQRS\Aggregate\AbstractValueObject;
use srag\asq\Domain\Model\AbstractConfiguration;
use srag\asq\Domain\Model\Question;
use srag\asq\UserInterface\Web\PathHelper;
use srag\asq\UserInterface\Web\Component\Editor\AbstractEditor;
use srag\asq\UserInterface\Web\Component\Editor\EmptyDisplayDefinition;
use srag\asq\UserInterface\Web\Fields\AsqTableInput;
use srag\asq\UserInterface\Web\Fields\AsqTableInputFieldDefinition;

/**
 * Class MatchingEditor
 *
 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class MatchingEditor extends AbstractEditor
{

    const VAR_SHUFFLE = 'me_shuffle';

    const VAR_THUMBNAIL = 'me_thumbnail';

    const VAR_MATCHING_MODE = 'me_matching';

    const VAR_DEFINITIONS = 'me_definitions';
    
    const VAR_DEFINITION_TEXT = 'me_definition_text';

    const VAR_DEFINITION_IMAGE = 'me_definition_image';

    const VAR_TERMS = 'me_terms';
    
    const VAR_TERM_TEXT = 'me_term_text';

    const VAR_TERM_IMAGE = 'me_term_image';

    const VAR_MATCHES = 'me_matches';
    
    const VAR_MATCH_DEFINITION = 'me_match_definition';

    const VAR_MATCH_TERM = 'me_match_term';

    const VAR_MATCH_POINTS = 'me_match_points';
    
    /**
     * @var AsqTableInput
     */
    private static $definitions;
    /**
     * @var AsqTableInput
     */
    private static $terms;
    /**
     * @var AsqTableInput
     */
    private static $matches;
    
    public function readAnswer(): AbstractValueObject
    {
        $matches = explode(';', $_POST[$this->question->getId()]);
        
        $matches = array_diff($matches, ['']);
        
        return MatchingAnswer::create($matches);
    }

    public function generateHtml(): string
    {
        global $DIC;
        
        /** @var MatchingEditorConfiguration $config */
        $config = $this->question->getPlayConfiguration()->getEditorConfiguration();
        
        $tpl = new ilTemplate(PathHelper::getBasePath(__DIR__) . 'templates/default/tpl.MatchingEditor.html', true, true);
        $tpl->setVariable('QUESTION_ID', $this->question->getId());
        $tpl->setVariable('ANSWER', is_null($this->answer) ? '' :$this->answer->getAnswerString());
        $tpl->setVariable('MATCHING_TYPE', $config->getMatchingMode());
        
        $this->renderDefinitions($config, $tpl);
        
        $this->renderTerms($config, $tpl);
        
        $DIC->ui()->mainTemplate()->addJavaScript(PathHelper::getBasePath(__DIR__) . 'src/Questions/Matching/MatchingEditor.js');
        
        return $tpl->get();
    }
    /**
     * @param config
     * @param tpl
     */
    private function renderTerms($config, $tpl)
    {
        $term_order = $this->getOrder(
            count($config->getTerms()),
            $config->isShuffleTerms());
        
        foreach ($term_order as $id) {
            $term = $config->getTerms()[$id];
            
            if (!empty($term[self::VAR_DEFINITION_IMAGE])) {
                $tpl->setCurrentBlock('term_picture');
                $tpl->setVariable('TERM', $term[self::VAR_TERM_TEXT]);
                $tpl->setVariable('IMAGE', $term[self::VAR_TERM_IMAGE]);
                $tpl->parseCurrentBlock();
            }
            else {
                $tpl->setCurrentBlock('term_text');
                $tpl->setVariable('TERM', $term[self::VAR_TERM_TEXT]);
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('draggable');
            $tpl->setVariable('ID_DRAGGABLE', $id);
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * @param config
     * @param tpl
     */
    private function renderDefinitions($config, $tpl)
    {
        $definition_order = $this->getOrder(
            count($config->getDefinitions()), 
            $config->isShuffleDefinitions());
        
        foreach ($definition_order as $id) {
            $definition = $config->getDefinitions()[$id];
            
            if (!empty($definition[self::VAR_DEFINITION_IMAGE])) {
                $tpl->setCurrentBlock('definition_picture');
                $tpl->setVariable('DEFINITION', $definition[self::VAR_DEFINITION_TEXT]);
                $tpl->setVariable('IMAGE', $definition[self::VAR_DEFINITION_IMAGE]);
                $tpl->parseCurrentBlock();
            }
            else {
                $tpl->setCurrentBlock('definition_text');
                $tpl->setVariable('DEFINITION', $definition[self::VAR_DEFINITION_TEXT]);
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock('droparea');
            $tpl->setVariable('ID_DROPAREA', $id);
            $tpl->parseCurrentBlock();
        }
    }


    private function getOrder(int $count, bool $shuffle) {
        $range = range(0, $count - 1);
        
        if ($shuffle) {
            shuffle($range);
        }
        
        return $range;
    }
    
    /**
     *
     * @param AbstractConfiguration|null $config
     *
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array
    {
        global $DIC;

        $fields = [];
        /** @var MatchingEditorConfiguration $config */

        $shuffle_answers = new ilSelectInputGUI($DIC->language()->txt('asq_label_shuffle_answers'), self::VAR_SHUFFLE);
        $shuffle_answers->setOptions([
            MatchingEditorConfiguration::SHUFFLE_NONE => $DIC->language()
                ->txt('asq_option_shuffle_none'),
            MatchingEditorConfiguration::SHUFFLE_DEFINITIONS => $DIC->language()
                ->txt('asq_option_shuffle_definitions'),
            MatchingEditorConfiguration::SHUFFLE_TERMS => $DIC->language()
                ->txt('asq_option_shuffle_terms'),
            MatchingEditorConfiguration::SHUFFLE_BOTH => $DIC->language()
                ->txt('asq_option_shuffle_both')
        ]);
        $fields[] = $shuffle_answers;

        $thumbnail = new ilNumberInputGUI($DIC->language()->txt('asq_label_thumbnail'), self::VAR_THUMBNAIL);
        $thumbnail->setRequired(true);
        $fields[] = $thumbnail;

        $matching_mode = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_matching_mode'), self::VAR_MATCHING_MODE);
        $matching_mode->addOption(new ilRadioOption($DIC->language()
            ->txt('asq_option_one_to_one'), MatchingEditorConfiguration::MATCHING_ONE_TO_ONE));
        $matching_mode->addOption(new ilRadioOption($DIC->language()
            ->txt('asq_option_many_to_one'), MatchingEditorConfiguration::MATCHING_MANY_TO_ONE));
        $matching_mode->addOption(new ilRadioOption($DIC->language()
            ->txt('asq_option_many_to_many'), MatchingEditorConfiguration::MATCHING_MANY_TO_MANY));
        $fields[] = $matching_mode;

        if (!is_null($config)) {
            $shuffle_answers->setValue($config->getShuffle());
            $thumbnail->setValue($config->getThumbnailSize());
            $matching_mode->setValue($config->getMatchingMode());
        } 
        
        self::createDefinitionsTable($config);
        $fields[] = self::$definitions;
        
        self::createTermsTable($config);
        $fields[] = self::$terms;
        
        self::createMatchTable($config);
        $fields[] = self::$matches;

        return $fields;
    }

    private static function createDefinitionsTable(?MatchingEditorConfiguration $config)
    {
        global $DIC;

        $columns = [];

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_definition_text'), 
            AsqTableInputFieldDefinition::TYPE_TEXT, 
            self::VAR_DEFINITION_TEXT);

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_definition_image'), 
            AsqTableInputFieldDefinition::TYPE_IMAGE, 
            self::VAR_DEFINITION_IMAGE);

        self::$definitions = new AsqTableInput($DIC->language()->txt('asq_label_definitions'), 
            self::VAR_DEFINITIONS,
            !is_null($config) ? $config->getDefinitions() : [], 
            $columns);
    }

    private static function createTermsTable(?MatchingEditorConfiguration $config)
    {
        global $DIC;

        $columns = [];

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_term_text'), 
            AsqTableInputFieldDefinition::TYPE_TEXT, 
            self::VAR_TERM_TEXT);

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_term_image'), 
            AsqTableInputFieldDefinition::TYPE_IMAGE, 
            self::VAR_TERM_IMAGE);

        self::$terms = new AsqTableInput($DIC->language()->txt('asq_label_terms'), 
            self::VAR_TERMS,
            !is_null($config) ? $config->getTerms() : [], 
            $columns);
    }

    private static function createMatchTable(?MatchingEditorConfiguration $config)
    {
        global $DIC;
        
        $columns = [];
        
        $defs = [];
        
        foreach ($config->getDefinitions() as $key=>$value) {
            $defs[$key] = $value[self::VAR_DEFINITION_TEXT];
        }
        
        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_matches_definition'),
            AsqTableInputFieldDefinition::TYPE_DROPDOWN,
            self::VAR_MATCH_DEFINITION, 
            $defs);
        
        $terms = [];
        
        foreach ($config->getTerms() as $key=>$value) {
            $terms[$key] = $value[self::VAR_TERM_TEXT];
        }
        
        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_matches_term'),
            AsqTableInputFieldDefinition::TYPE_DROPDOWN,
            self::VAR_MATCH_TERM,
            $terms);

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_points'),
            AsqTableInputFieldDefinition::TYPE_NUMBER,
            self::VAR_MATCH_POINTS);
        
        self::$matches = new AsqTableInput($DIC->language()->txt('asq_label_matches'),
            self::VAR_MATCHES,
            !is_null($config) ? $config->getMatches() : [],
            $columns);
    }
    
    public static function readConfig()
    {
        $def = !empty(self::$definitions) ? self::$definitions->readValues() : [];
        $term = !empty(self::$terms) ? self::$terms->readValues() : [];
        $match = !empty(self::$matches) ? self::$matches->readValues() : [];
        
        return MatchingEditorConfiguration::create(
            intval($_POST[self::VAR_SHUFFLE]), 
            intval($_POST[self::VAR_THUMBNAIL]), 
            intval($_POST[self::VAR_MATCHING_MODE]),
            $def,
            $term,
            $match);
    }

    /**
     *
     * @return string
     */
    static function getDisplayDefinitionClass(): string
    {
        return EmptyDisplayDefinition::class;
    }
    
    public static function isComplete(Question $question): bool
    {
        /** @var MatchingEditorConfiguration $config */
        $config = $question->getPlayConfiguration()->getEditorConfiguration();
        
        if (count($config->getDefinitions()) < 1 ||
            count($config->getTerms()) < 1 ||
            count($config->getMatches()) < 1) 
        {
            return false;
        }
        
        return true;
    }
}