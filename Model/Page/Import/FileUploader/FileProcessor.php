<?php
declare(strict_types=1);

namespace Aiops\AmastyExtend\Model\Page\Import\FileUploader;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Exception;

class FileProcessor
{
    const FILE_DIR = 'custom_page_import';

    /**
     * @var WriteInterface
     */
    private $varDirectory;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory
    ) {
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * @param string $fileId
     * @return array
     */
    public function saveToTmp(string $fileId): array
    {
        try {
            $result = $this->save($fileId, $this->varDirectory->getAbsolutePath('tmp/' . self::FILE_DIR));
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $result;
    }

    /**
     * @param string $fileId
     * @param string $destination
     * @return array
     * @throws Exception
     */
    private function save(string $fileId, string $destination): array
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $uploader->setAllowedExtensions(['csv']);
        return $uploader->save($destination);
    }
}
