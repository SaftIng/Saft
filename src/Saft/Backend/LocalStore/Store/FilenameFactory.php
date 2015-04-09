<?php
namespace Saft\Backend\LocalStore\Store;

interface FilenameFactory
{
    /**
     * Generates the filename for the given graph URI. It will be used to create
     * a ntriples file for the graph in the base directory of the store. It is common
     * to generate filenames with the file extension '.nt', e. g. 'foaf.nt'.
     * @param string $graphUri
     * @param string $baseDir absolute Path
     * @return string generated filename as relative path to the base directory
     */
    public function generateFilename($graphUri, $baseDir);
}
