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
 * This interface represents a pattern, in some contexts also a variable. Its purpose is to act as some kind of
 * a placeholder, if you dont want to specify a RDF term.
 *
 * It is useful in SPARQL queries, to be used as a variable: SELECT ?s WHERE { ?s ?p ?o }
 */
interface AnyPattern extends Node
{
}
