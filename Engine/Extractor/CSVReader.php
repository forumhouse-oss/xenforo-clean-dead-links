<?php

/**
 * Class to read dead links data from special CSV file format
 */
class FH_LinkCleaner_Engine_Extractor_CSVReader extends FH_LinkCleaner_Engine_Extractor_Abstract
{
    /** Field, containing url, where dead link was found */
    const FIELD_URL = 1;

    /** Field, containing dead link contents itself */
    const FIELD_LINK_CONTENTS = 2;

    /**
     * @return FH_LinkCleaner_Engine_Extractor_FileEntry[]
     */
    public function read()
    {
        $links = array();

        $lineNumber = 0;
        while (false !== ($dataRow = fgetcsv($this->fileHandle, null, ';', '"'))) {
            $lineNumber++;

            if (count($dataRow) != 5) {
                $this->logger->addError("Invalid column count at line $lineNumber. Skipped.\r\n".implode($dataRow));
                continue;
            }

            $links[] = new FH_LinkCleaner_Engine_Extractor_FileEntry(
                $dataRow[self::FIELD_URL],
                $dataRow[self::FIELD_LINK_CONTENTS]
            );
        }

        return $links;
    }
}
