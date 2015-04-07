<?php
namespace Saft\Backend\LocalStore\Store;

final class SyntaxException extends \Exception
{
    const UNDEFINED = -1;
    
    private $row;
    private $colum;
    
    public function __construct(
        $message,
        $row = self::UNDEFINED,
        $colum = self::UNDEFINED
    ) {
        $this->row = $row;
        $this->colum = $colum;
        parent::__construct($message);
    }

    public function getRow()
    {
        return $this->row;
    }

    public function isRowDefined()
    {
        return $this->row != self::UNDEFINED;
    }
    
    public function isColumDefined()
    {
        return $this->colum != self::UNDEFINED;
    }
    
    public function getColum()
    {
        return $this->colum;
    }

    public function __toString()
    {
        if ($this->isRowDefined() && $this->isColumDefined()) {
            return sprintf(
                '%s (at line %, colum %d)',
                $this->getMessage(),
                $this->getRow(),
                $this->getColum()
            );
        } elseif ($this->isRowDefined()) {
            return sprintf(
                '%s (at row %d)',
                $this->getMessage(),
                $this->getColum()
            );
        } elseif ($this->isColumDefined()) {
            return sprintf(
                '%s (at colum %d)',
                $this->getMessage(),
                $this->getColum()
            );
        } else {
            return $this->getMessage();
        }
    }
}
