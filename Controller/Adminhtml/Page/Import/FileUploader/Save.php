<?php
declare(strict_types=1);

namespace Aiops\AmastyExtend\Controller\Adminhtml\Page\Import\FileUploader;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Aiops\AmastyExtend\Model\Page\Import\FileUploader\FileProcessor;
use Magento\Framework\Controller\ResultInterface;

class Save extends Action
{
    const IMPORT_FILE = 'import_file';

    const ADMIN_RESOURCE = 'Amasty_ShopbyPage::page';

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @param Context $context
     * @param FileProcessor $fileProcessor
     */
    public function __construct(
        Context $context,
        FileProcessor $fileProcessor
    ) {
        $this->fileProcessor = $fileProcessor;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->fileProcessor->saveToTmp(self::IMPORT_FILE);
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
