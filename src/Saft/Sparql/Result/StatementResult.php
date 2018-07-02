<?php

/*
 * This file is part of Saft.
 *
 * (c) Konrad Abicht <hi@inspirito.de>
 * (c) Natanael Arndt <arndt@informatik.uni-leipzig.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Saft\Sparql\Result;

use Saft\Rdf\Statement;

/**
 * This class represents a result set containing only Statement instances.
 */
interface StatementResult extends SetResult
{
    /**
     * @return Statement|null
     */
    public function current(): ?Statement;

    /**
     * @return bool
     */
    public function valid(): bool;
}
