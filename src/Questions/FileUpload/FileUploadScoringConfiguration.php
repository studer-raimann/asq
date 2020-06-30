<?php
declare(strict_types=1);

namespace srag\asq\Questions\FileUpload;

use srag\asq\Domain\Model\AbstractConfiguration;

/**
 * Class FileUploadScoringConfiguration
 *
 * @license Extended GPL, see docs/LICENSE
 * @copyright 1998-2020 ILIAS open source
 *
 * @package srag/asq
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class FileUploadScoringConfiguration extends AbstractConfiguration
{
    /**
     * @var ?float
     */
    protected $points;
    
    /**
     * @var ?bool
     */
    protected $completed_by_submition;
    
    /**
     * @param int $points
     * @param bool $completed_by_submition
     * @return FileUploadScoringConfiguration
     */
    public static function create(?float $points = null, ?bool $completed_by_submition = null) : FileUploadScoringConfiguration
    {
        $object = new FileUploadScoringConfiguration();
        $object->points = $points;
        $object->completed_by_submition = $completed_by_submition;
        return $object;
    }
    
    /**
     * @return int
     */
    public function getPoints() : ?float
    {
        return $this->points;
    }
    
    /**
     * @return boolean
     */
    public function isCompletedBySubmition() : ?bool
    {
        return $this->completed_by_submition;
    }
}
