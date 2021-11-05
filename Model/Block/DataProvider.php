<?php
declare(strict_types=1);

namespace Aiops\AmastyExtend\Model\Block;

use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Aiops\AmastyExtend\Model\DummyCollectionFactory;

class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DummyCollectionFactory $dummyCollectionFactory
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DummyCollectionFactory $dummyCollectionFactory,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $dummyCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}
