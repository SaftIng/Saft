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

namespace Saft\Addition\ARC2\Store;

/**
 * This class aims to provide a direct way to read a file and import its content
 * into an ARC2 instance.
 */
class NativeARC2Importer
{
    /**
     * @var \ARC2_Store
     */
    protected $store;

    /**
     * @param \ARC2_Store $store
     */
    public function __construct(\ARC2_Store $store)
    {
        $this->store = $store;
    }

    /**
     * @param string $fileName
     * @param string $graphUri
     */
    public function importN3FileIntoGraph(string $fileName, string $graphUri)
    {
        $file = new \SplFileObject($fileName);

        $batch = 100;
        $counter = 0;
        $batch = '';

        // Loop until we reach the end of the file.
        while (!$file->eof()) {
            $batch .= $file->fgets();

            if ($counter > $batch) {
                $this->store->query('INSERT INTO <'. $graphUri .'> {'. $batch .'}');

                $batch = '';
                $counter = 0;
            }

            ++$counter;
        }

        // if there is something not inserted yet
        if (0 < $counter) {
            $this->store->query('INSERT INTO <'. $graphUri .'> {'. $batch .'}');
        }

        // Unset the file to call __destruct(), closing the file handle.
        $file = null;
    }
}
