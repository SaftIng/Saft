<?php

namespace Saft\Rdf\Test;

use Saft\Rdf\BlankNodeImpl;

class BlankNodeImplTest extends BlankNodeAbstractTest
{
    /**
     * {@inheritdoc}
     */
    public function newInstance($id)
    {
        return new BlankNodeImpl($id);
    }
}
