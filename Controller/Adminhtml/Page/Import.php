<?php
declare(strict_types=1);

namespace Aiops\AmastyExtend\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;

class Import extends Action
{
    const ADMIN_RESOURCE = 'Amasty_ShopbyPage::page';

    /**
     * @var ResultPageFactory
     */
    private $resultPageFactory;

    public function __construct(
        Context $context,
        ResultPageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Import Pages'));
        return $resultPage;
    }
}
