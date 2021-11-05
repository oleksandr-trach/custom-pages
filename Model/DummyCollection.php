<?php
declare(strict_types=1);

namespace Aiops\AmastyExtend\Model;

use Magento\Framework\Data\Collection;

class DummyCollection extends Collection
{
    /**
     * @param array|string $field
     * @param array|int|string $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition): DummyCollection
    {
        return $this;
    }
}
