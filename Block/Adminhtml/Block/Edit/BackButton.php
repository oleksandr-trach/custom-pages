<?php
declare(strict_types=1);

namespace Aiops\AmastyExtend\Block\Adminhtml\Block\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context as WidgetContext;

class BackButton implements ButtonProviderInterface
{
    /**
     * @var WidgetContext
     */
    private $widgetContext;

    /**
     * @param WidgetContext $widgetContext
     */
    public function __construct(
        WidgetContext $widgetContext
    ) {
        $this->widgetContext = $widgetContext;
    }

    public function getButtonData(): array
    {
        $url = $this->widgetContext->getUrlBuilder()->getUrl('*/*/');
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $url),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
