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

namespace Saft\Sparql\Query;

interface Query
{
    /**
     * @return string
     */
    public function getQuery();

    /**
     * @return array
     */
    public function getQueryParts();

    /**
     * Is instance of AskQuery?
     *
     * @return bool
     */
    public function isAskQuery();

    /**
     * Is instance of ConstructQuery?
     *
     * @return bool
     */
    public function isConstructQuery();

    /**
     * Is instance of DescribeQuery?
     *
     * @return bool
     */
    public function isDescribeQuery();

    /**
     * Is instance of GraphQuery?
     *
     * @return bool
     */
    public function isGraphQuery();

    /**
     * Is instance of SelectQuery?
     *
     * @return bool
     */
    public function isSelectQuery();

    /**
     * Is instance of UpdateQuery?
     *
     * @return bool
     */
    public function isUpdateQuery();
}
