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
    public function importN3FileIntoGraph($fileName, $graphUri)
    {
        $file = new \SplFileObject($fileName);

        $batch = 1000;
        $counter = 0;
        $chunk = '';
        $j = 0;

        // Loop until we reach the end of the file.
        while (!$file->eof()) {
            $chunk .= $file->fgets();

            if ($counter > $batch) {
                $this->store->query('INSERT INTO <'. $graphUri .'> {'. $chunk .'}');

                $chunk = '';
                $counter = 0;
                $j++;
            }

            ++$counter;
        }

        // if there is something not inserted yet
        if (0 < $counter) {
            $this->store->query('INSERT INTO <'. $graphUri .'> {'. $chunk .'}');
        }

        // Unset the file to call __destruct(), closing the file handle.
        $file = null;
    }
}
