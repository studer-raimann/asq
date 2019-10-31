<?php

namespace ILIAS\AssessmentQuestion\DomainModel;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\AvailableScorings;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\AvailableEditors;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Presenter\AvailablePresenters;

/**
 * Class QuestionPlayConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionPlayConfiguration extends AbstractValueObject {
	/**
	 * @var AbstractConfiguration
	 */
	protected $presenter_configuration;

	/**
	 * @var AbstractConfiguration
	 */
	protected $editor_configuration;

	/**
	 * @var AbstractConfiguration
	 */
	protected $scoring_configuration;

    /**
     * @param AbstractConfiguration $editor_configuration
     * @param AbstractConfiguration $scoring_configuration
     * @param AbstractConfiguration $presenter_configuration
     * @return QuestionPlayConfiguration
     */
	public static function create(
	    AbstractConfiguration $editor_configuration = null,
		AbstractConfiguration $scoring_configuration = null,
		AbstractConfiguration $presenter_configuration = null
	) : QuestionPlayConfiguration {
		$object = new QuestionPlayConfiguration();
		$object->editor_configuration = $editor_configuration;
		$object->presenter_configuration = $presenter_configuration;
		$object->scoring_configuration = $scoring_configuration;
		return $object;
	}

	public static function getEditorClass(?QuestionPlayConfiguration $conf): string {
		if ($conf->editor_configuration !== null) {
			return $conf->editor_configuration->configurationFor();
		} else {
			return AvailableEditors::getDefaultEditor();
		}
	}

	public static function getPresenterClass(?QuestionPlayConfiguration $conf): string {
		if ($conf->presenter_configuration !== null) {
			return $conf->presenter_configuration->configurationFor();
		} else {
			return AvailablePresenters::getDefaultPresenter();
		}
	}

	public static function getScoringClass(?QuestionPlayConfiguration $conf): string {
		if ($conf->scoring_configuration !== null) {
			return $conf->scoring_configuration->configurationFor();
		} else {
			return AvailableScorings::getDefaultScoring();
		}
	}

	/**
	 * @return AbstractValueObject
	 */
	public function getEditorConfiguration(): ?AbstractConfiguration {
		return $this->editor_configuration;
	}

	/**
	 * @return AbstractValueObject
	 */
	public function getPresenterConfiguration(): ?AbstractConfiguration {
		return $this->presenter_configuration;
	}

	/**
	 * @return AbstractValueObject
	 */
	public function getScoringConfiguration(): ?AbstractConfiguration {
		return $this->scoring_configuration;
	}

    /**
     * @param AbstractValueObject $other
     *
     * @return bool
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var QuestionPlayConfiguration $other */
        return get_class($this) === get_class($other) &&
               AbstractValueObject::isNullableEqual(
        	        $this->getEditorConfiguration(),
	                $other->getEditorConfiguration()) &&
               AbstractValueObject::isNullableEqual(
               	    $this->getPresenterConfiguration(),
                    $other->getPresenterConfiguration()) &&
               AbstractValueObject::isNullableEqual(
               	    $this->getScoringConfiguration(),
                    $other->getScoringConfiguration());
    }
    
    public function hasAnswerOptions(): bool {
        if (is_null($this->getScoringConfiguration()) || is_null($this->getEditorConfiguration())) {
            return false;    
        }
        
        $sd_class = QuestionPlayConfiguration::getScoringClass($this)::getScoringDefinitionClass();
        $dd_class = QuestionPlayConfiguration::getEditorClass($this)::getDisplayDefinitionClass();
        
        
        return (count($dd_class::getFields($this)) + count($sd_class::getFields($this))) > 0;
    }
    
    /**
     * @param Question $question
     */
    public static function isComplete(Question $question) : bool{
        return QuestionPlayConfiguration::getScoringClass($question->getPlayConfiguration())::isComplete($question) &&
               QuestionPlayConfiguration::getEditorClass($question->getPlayConfiguration())::isComplete($question);
    }
}