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
 * This interface is common for literals according to RDF 1.1
 * {@url http://www.w3.org/TR/rdf11-concepts/#section-Graph-Literal}.
 *
 * @api
 *
 * @since 0.1
 */
interface Literal extends Node
{
    /**
     * Get the value of the Literal in its string representations.
     *
     * @return string the value of the Literal
     *
     * @api
     *
     * @since 0.1
     */
    public function getValue();

    /**
     * Get the datatype URI of the Literal (this is always set according to the
     * standard).
     *
     * @return Node the datatype of the Literal
     *
     * @api
     *
     * @since 0.1
     */
    public function getDatatype();

    /**
     * Get the language tag of this Literal or null of the Literal has no
     * language tag.
     *
     * @return string|null language tag or null, if none is given
     *
     * @api
     *
     * @since 0.1
     */
    public function getLanguage();
}
