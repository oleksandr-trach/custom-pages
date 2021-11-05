<?php
declare(strict_types=1);

namespace Aiops\AmastyExtend\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\File\Csv;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultInterface;
use Aiops\AmastyExtend\Model\PageManager;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Psr\Log\LoggerInterface;
use Exception;

class ImportProcess extends Action implements HttpPostActionInterface
{
    const FIRST_LINE = 0;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @var PageManager
     */
    private $pageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @param Context $context
     * @param Csv $csv
     * @param PageManager $pageManager
     * @param JsonSerializer $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Csv $csv,
        PageManager $pageManager,
        JsonSerializer $jsonSerializer,
        LoggerInterface $logger
    ) {
        $this->csv = $csv;
        $this->pageManager = $pageManager;
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        /** @var HttpRequest $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        try {
            $file = $this->getImportFileData($data);
            $result = $this->processImportFile($file);
            $this->messageManager->addSuccessMessage(
                __('%1 pages where imported. %2 errors. See logs for errors.', $result['imported'], $result['errors'])
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while importing file.'));
        }

        return $resultRedirect->setPath('*/*/import');
    }

    /**
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    private function getImportFileData(array $data): array
    {
        if (!isset($data['import_file'])) {
            throw new LocalizedException(__('No import file has been provided.'));
        }

        return array_pop($data['import_file']);
    }

    /**
     * @param array $file
     * @return array
     * @throws Exception
     */
    private function processImportFile(array $file): array
    {
        $csvData = $this->csv->getData($this->getFilePath($file['path'], $file['file']));
        unset($csvData[self::FIRST_LINE]);
        $imported = 0;
        $errors = 0;

        foreach ($csvData as $pageData) {
            try {
                $this->pageManager->createPage($pageData);
                $imported++;
            } catch (Exception $e) {
                $this->logger->error(
                    __('Error: %1. Page data was: %2', $e->getMessage(), $this->jsonSerializer->serialize($pageData))
                );
                $errors++;
            }
        }

        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }

    /**
     * @param string $path
     * @param string $file
     * @return string
     */
    private function getFilePath(string $path, string $file): string
    {
        return $path . '/' . $file;
    }
}
