<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage\File;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Helper\ProfileHelper;
use S2\Rose\Storage\ArrayFulltextStorage;
use S2\Rose\Storage\ArrayStorage;

class SingleFileArrayStorage extends ArrayStorage
{
    /**
     * @var ArrayFulltextStorage
     */
    protected $fulltextProxy;

    /**
     * @var string
     */
    protected $filename;

    public function __construct($filename)
    {
        $this->filename      = $filename;
        $this->fulltextProxy = new ArrayFulltextStorage();
    }

    /**
     * @param bool $isDebug
     *
     * @return array
     */
    public function load($isDebug = false)
    {
        $return = [];
        if (count($this->toc)) {
            return $return;
        }

        if (!is_file($this->filename)) {
            return $return;
        }

        if ($isDebug) {
            $start_time = microtime(true);
        }

        $data = file_get_contents($this->filename);

        if ($isDebug) {
            $return[] = ProfileHelper::getProfilePoint('Reading index file', -$start_time + ($start_time = microtime(true)));
        }

        $end     = strpos($data, "\n");
        $my_data = substr($data, 8, $end);
        $data    = substr($data, $end + 1);
        $this->fulltextProxy->setFulltextIndex(unserialize($my_data) ?: []);

        $end                 = strpos($data, "\n");
        $my_data             = substr($data, 8, $end);
        $data                = substr($data, $end + 1);
        $this->excludedWords = unserialize($my_data) ?: [];

        $end                       = strpos($data, "\n");
        $my_data                   = substr($data, 8, $end);
        $data                      = substr($data, $end + 1);
        $this->indexSingleKeywords = unserialize($my_data) ?: [];

        $end                     = strpos($data, "\n");
        $my_data                 = substr($data, 8, $end);
        $data                    = substr($data, $end + 1);
        $this->indexBaseKeywords = unserialize($my_data) ?: [];

        $end                      = strpos($data, "\n");
        $my_data                  = substr($data, 8, $end);
        $data                     = substr($data, $end + 1);
        $this->indexMultiKeywords = unserialize($my_data) ?: [];

        $end       = strpos($data, "\n");
        $my_data   = substr($data, 8, $end);
        // $data      = substr($data, $end + 1);
        $this->toc = unserialize($my_data) ?: [];

        if ($isDebug) {
            $return[] = ProfileHelper::getProfilePoint('Unserializing index', -$start_time + ($start_time = microtime(true)));
        }

        $this->externalIdMap = [];
        foreach ($this->toc as $serializedExtId => $entry) {
            $this->externalIdMap[$entry->getInternalId()] = ExternalId::fromString($serializedExtId);
        }

        return $return;
    }

    public function save()
    {
        @unlink($this->filename);
        file_put_contents($this->filename, '<?php //' . 'a:' . count($this->fulltextProxy->getFulltextIndex()) . ':{');
        $buffer = '';
        $length = 0;
        foreach ($this->fulltextProxy->getFulltextIndex() as $word => $data) {
            $chunk  = serialize($word) . serialize($data);
            $length += strlen($chunk);
            $buffer .= $chunk;
            if ($length > 100000) {
                file_put_contents($this->filename, $buffer, FILE_APPEND);
                $buffer = '';
                $length = 0;
            }
        }
        file_put_contents($this->filename, $buffer . '}' . "\n", FILE_APPEND);
        $this->fulltextProxy->setFulltextIndex([]);

        file_put_contents($this->filename, '      //' . serialize($this->excludedWords) . "\n", FILE_APPEND);
        $this->excludedWords = [];

        file_put_contents($this->filename, '      //' . serialize($this->indexSingleKeywords) . "\n", FILE_APPEND);
        $this->indexSingleKeywords = [];

        file_put_contents($this->filename, '      //' . serialize($this->indexBaseKeywords) . "\n", FILE_APPEND);
        $this->indexBaseKeywords = [];

        file_put_contents($this->filename, '      //' . serialize($this->indexMultiKeywords) . "\n", FILE_APPEND);
        $this->indexMultiKeywords = [];

        file_put_contents($this->filename, '      //' . serialize($this->toc) . "\n", FILE_APPEND);
        $this->toc = [];
    }
}
