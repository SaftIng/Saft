<?php
namespace Saft\Backend\LocalStore\Store;

final class SyntaxException extends \Exception
{
    const UNDEFINED = -1;
    
    private $row;
    private $column;
    
    public function __construct(
        $message,
        $row = self::UNDEFINED,
        $column = self::UNDEFINED
    ) {
        $this->row = $row;
        $this->column = $column;
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
    
    public function isColumnDefined()
    {
        return $this->column != self::UNDEFINED;
    }
    
    public function getColumn()
    {
        return $this->column;
    }

    public function __toString()
    {
        if ($this->isRowDefined() && $this->isColumnDefined()) {
            return sprintf(
                '%s (at line %, column %d)',
                $this->getMessage(),
                $this->getRow(),
                $this->getColumn()
            );
        } elseif ($this->isRowDefined()) {
            return sprintf(
                '%s (at row %d)',
                $this->getMessage(),
                $this->getColumn()
            );
        } elseif ($this->isColumnDefined()) {
            return sprintf(
                '%s (at column %d)',
                $this->getMessage(),
                $this->getColumn()
            );
        } else {
            return $this->getMessage();
        }
    }
}
