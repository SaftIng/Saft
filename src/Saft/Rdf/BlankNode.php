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

namespace Saft\Rdf;

/**
 * This interface is common for blank nodes according to RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-blank-nodes}.
 *
 * @api
 *
 * @since 0.1
 */
interface BlankNode extends Node
{
    /**
     * Returns the blank ID of this blank node.
     *
     * @return string blank ID
     *
     * @api
     *
     * @since 0.1
     */
    public function getBlankId(): string;
}
