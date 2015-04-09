<?php
namespace Saft\Backend\LocalStore\Store;

class DefaultFilenameFactory implements FilenameFactory
{
    /**
     * {@inheritdoc}
     */
    public function generateFilename($graphUri, $baseDir)
    {
        // TODO check, if file allready exist.
        return basename($graphUri) . '.nt';
    }
}
