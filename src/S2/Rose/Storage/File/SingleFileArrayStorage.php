<?php
/**
 * @copyright 2016-2025 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage\File;

use S2\Rose\Entity\ExternalId;
use S2\Rose\Entity\Metadata\Img;
use S2\Rose\Entity\Metadata\ImgCollection;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Entity\TocEntry;
use S2\Rose\Helper\ProfileHelper;
use S2\Rose\Storage\ArrayFulltextStorage;
use S2\Rose\Storage\ArrayStorage;
use S2\Rose\Storage\FulltextProxyInterface;

class SingleFileArrayStorage extends ArrayStorage
{
    protected FulltextProxyInterface $fulltextProxy;
    protected string $filename;

    public function __construct($filename)
    {
        $this->filename      = $filename;
        $this->fulltextProxy = new ArrayFulltextStorage();
    }

    public function load(bool $isDebug = false): array
    {
        $return = [];
        if (\count($this->toc)) {
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

        $myData = $this->extractSerializedSection($data);
        $unserializeOptions = ['allowed_classes' => [
            \DateTime::class,
            TocEntry::class,
            Img::class,
            ImgCollection::class,
            SnippetSource::class,
        ]];
        $this->fulltextProxy->setFulltextIndex(unserialize($myData, $unserializeOptions) ?: []);

        $myData              = $this->extractSerializedSection($data);
        $this->excludedWords = unserialize($myData, $unserializeOptions) ?: [];

        $myData         = $this->extractSerializedSection($data);
        $this->metadata = unserialize($myData, $unserializeOptions) ?: [];

        $myData = $this->extractSerializedSection($data);
        $this->toc = unserialize($myData, $unserializeOptions) ?: [];


        if ($isDebug) {
            $return[] = ProfileHelper::getProfilePoint('Unserializing index', -$start_time + ($start_time = microtime(true)));
        }

        $this->externalIdMap = [];
        foreach ($this->toc as $serializedExtId => $entry) {
            $this->externalIdMap[$entry->getInternalId()] = ExternalId::fromString($serializedExtId);
        }

        return $return;
    }

    public function save(): void
    {
        @unlink($this->filename);
        file_put_contents($this->filename, '<?php die; //' . 'a:' . \count($this->fulltextProxy->getFulltextIndex()) . ':{');
        $buffer = '';
        $length = 0;
        foreach ($this->fulltextProxy->getFulltextIndex() as $word => $data) {
            $chunk  = serialize($word) . serialize($data);
            $length += \strlen($chunk);
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

        file_put_contents($this->filename, '      //' . serialize($this->metadata) . "\n", FILE_APPEND);
        $this->metadata = [];

        file_put_contents($this->filename, '      //' . serialize($this->toc) . "\n", FILE_APPEND);
        $this->toc = [];
    }

    private function extractSerializedSection(string &$data): string
    {
        $endPos = strpos($data, "\n");
        if ($endPos === false) {
            $line = $data;
            $data = '';
        } else {
            $line = substr($data, 0, $endPos);
            $data = substr($data, $endPos + 1);
        }

        $commentPos = strpos($line, '//');
        if ($commentPos === false) {
            throw new \RuntimeException('Broken SingleFileArrayStorage format: "//" marker not found.');
        }

        return substr($line, $commentPos + 2);
    }
}
