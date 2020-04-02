<?php

namespace srag\asq\Infrastructure\Persistence\Projection;

use ilDateTime;
use srag\asq\Domain\QuestionDto;

/**
 * Class QuestionAr
 *
 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class QuestionAr extends AbstractProjectionAr
{
    const STORAGE_NAME = "asq_question";
    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected $id;
    /**
     * @con_has_field true
     * @con_fieldtype timestamp
     */
    protected $created;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $creator;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_index      true
     * @con_is_notnull true
     */
    protected $question_id;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_index      true
     * @con_is_notnull true
     */
    protected $revision_name;

    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     * @con_is_notnull true
     */
    protected $data;

    /**
     * 
     * @param QuestionDto $question
     */
    public static function createNew(QuestionDto $question) {        
        global $DIC;
        $object = new QuestionAr();
        
        $created = new ilDateTime(time(), IL_CAL_UNIX);
        $object->created = $created->get(IL_CAL_DATETIME);
        $object->creator = $DIC->user()->getId();
        $object->question_id = $question->getId();
        $object->revision_name = $question->getRevisionId()->getName();
        $object->data = json_encode($question);
        
        return $object;
        
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getCreator(): int
    {
        return $this->creator;
    }
    
    /**
     * @return string
     */
    public function getQuestionId() : string
    {
        return $this->question_id;
    }

    /**
     * @return string
     */
    public function getRevisionName() : string
    {
        return $this->revision_name;
    }
    
    public function getQuestion(): QuestionDto {
        return QuestionDto::deserialize($this->data);
    }
    
    /**
     * @return string
     */
    static function returnDbTableName()
    {
        return self::STORAGE_NAME;
    }
}