<?php
declare(strict_types=1);

namespace Aiops\AmastyExtend\Model;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Amasty\ShopbyPage\Api\Data\PageInterfaceFactory;
use Amasty\ShopbyPage\Model\PageFactory as CustomPageFactory;
use Amasty\ShopbyPage\Model\ResourceModel\Page as PageResource;
use Amasty\ShopbyPage\Api\PageRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\Category;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

class PageManager
{
    const POSITION = 0;

    const STORES = 1;

    const URL = 2;

    const TITLE = 3;

    const DESCRIPTION = 4;

    const META_TITLE = 5;

    const META_KEYWORDS = 6;

    const META_DESCRIPTION = 7;

    const CONDITIONS = 8;

    const CATEGORIES = 9;

    const ATTRIBUTE = 0;

    const OPTION = 1;

    const HAS_ATTRIBUTE_AND_OPTION = 2;

    /**
     * @var StoreInterface[]
     */
    private $allStores;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var PageInterfaceFactory
     */
    private $pageFactory;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var Category[]
     */
    private $allCategories;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var array
     */
    private $attributeOptions;

    /**
     * @var CustomPageFactory
     */
    private $customPageFactory;

    /**
     * @var PageResource
     */
    private $pageResource;

    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param PageInterfaceFactory $pageFactory
     * @param PageRepositoryInterface $pageRepository
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomPageFactory $customPageFactory
     * @param PageResource $pageResource
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        PageInterfaceFactory $pageFactory,
        PageRepositoryInterface $pageRepository,
        CategoryCollectionFactory $categoryCollectionFactory,
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomPageFactory $customPageFactory,
        PageResource $pageResource
    ) {
        $this->storeRepository = $storeRepository;
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customPageFactory = $customPageFactory;
        $this->pageResource = $pageResource;
    }

    /**
     * @param array $pageData
     * @throws LocalizedException
     */
    public function createPage(array $pageData): void
    {
        $pageData = $this->prepareData($pageData);
        $this->validatePosition($pageData[self::POSITION]);
        $this->validateTitle($pageData[self::TITLE]);

        $customPage = $this->customPageFactory->create();
        $this->pageResource->load($customPage, $pageData[self::TITLE], 'title');

        if ($customPage->getId()) {
            $page = $this->pageRepository->get($customPage->getId());
        } else {
            $page = $this->pageFactory->create();
        }

        $page->setPosition($pageData[self::POSITION]);
        $page->setStores($pageData[self::STORES]);
        $page->setUrl($pageData[self::URL]);
        $page->setTitle($pageData[self::TITLE]);
        $page->setDescription($pageData[self::DESCRIPTION]);
        $page->setMetaTitle($pageData[self::META_TITLE]);
        $page->setMetaKeywords($pageData[self::META_KEYWORDS]);
        $page->setMetaDescription($pageData[self::META_DESCRIPTION]);
        $page->setConditions($pageData[self::CONDITIONS]);
        $page->setCategories($pageData[self::CATEGORIES]);
        $this->pageRepository->save($page);
    }

    /**
     * @param string $title
     * @throws LocalizedException
     */
    private function validateTitle(string $title): void
    {
        if (empty($title)) {
            throw new LocalizedException(__('Title should not be empty.'));
        }
    }

    /**
     * @param string $stores
     * @return array
     * @throws LocalizedException
     */
    private function getStores(string $stores): array
    {
        $stores = explode(',', $stores);
        $storeIds = [];

        foreach ($stores as $store) {
            $allStores = $this->getAllStores();

            if (!array_key_exists($store, $allStores)) {
                throw new LocalizedException(__('Store does not exists.'));
            }

            $storeObject = $allStores[$store];
            $storeIds[] = $storeObject->getId();
        }

        return $storeIds;
    }

    /**
     * @return StoreInterface[]
     */
    private function getAllStores(): array
    {
        if ($this->allStores === null) {
            $this->allStores = $this->storeRepository->getList();
        }

        return $this->allStores;
    }

    /**
     * @param string $position
     * @throws LocalizedException
     */
    private function validatePosition(string $position): void
    {
        if (!in_array($position, $this->getAllowedPositions())) {
            throw new LocalizedException(__('This position is not allowed.'));
        }
    }

    /**
     * @return string[]
     */
    private function getAllowedPositions(): array
    {
        return ['replace', 'before', 'after'];
    }

    /**
     * @param array $pageData
     * @return array
     * @throws LocalizedException
     */
    private function prepareData(array $pageData): array
    {
        $pageData[self::POSITION] = $pageData[self::POSITION] ?? '';
        $pageData[self::STORES] = $pageData[self::STORES] ? $this->getStores($pageData[self::STORES]) : [];
        $pageData[self::URL] = $pageData[self::URL] ?? '';
        $pageData[self::TITLE] = $pageData[self::TITLE] ?? '';
        $pageData[self::DESCRIPTION] = $pageData[self::DESCRIPTION] ?? '';
        $pageData[self::META_TITLE] = $pageData[self::META_TITLE] ?? '';
        $pageData[self::META_KEYWORDS] = $pageData[self::META_KEYWORDS] ?? '';
        $pageData[self::META_DESCRIPTION] = $pageData[self::META_DESCRIPTION] ?? '';
        $pageData[self::CONDITIONS] = $this->getConditions($pageData[self::CONDITIONS]) ?? '';
        $pageData[self::CATEGORIES] = $this->getCategories($pageData[self::CATEGORIES]) ?? [];

        return $pageData;
    }

    /**
     * @param string $conditions
     * @return array
     */
    private function getConditions(string $conditions): array
    {
        $attributeOptions = $this->getAttributeOptions();
        $conditions = explode(',', $conditions);
        $conditionValueArray = [];

        foreach ($conditions as $condition) {
            $condition = explode('++', $condition);

            if (count($condition) === self::HAS_ATTRIBUTE_AND_OPTION) {
                $attribute = $condition[self::ATTRIBUTE];
                $value = $condition[self::OPTION];
                $conditionValueArray[] = $this->getConditionValue($attribute, $value, $attributeOptions);
            }
        }

        return $conditionValueArray;
    }

    /**
     * @param string $attribute
     * @param string $value
     * @param array $attributeOptions
     * @return array
     */
    private function getConditionValue(string $attribute, string $value, array $attributeOptions): array
    {
        $result = [];

        foreach ($attributeOptions as $attributeOption) {
            if ($attributeOption['attribute_frontend_label'] === $attribute) {
                $result['filter'] = $attributeOption['attribute_id'];

                if ($attributeOption['frontend_input'] === 'multiselect') {
                    $result['value'] = $this->getOptionValueArray($attributeOption['options'], $value);
                } else {
                    $result['value'] = $this->getOptionValue($attributeOption['options'], $value);
                }

                break;
            }
        }

        return $result;
    }

    /**
     * @param array $options
     * @param string $value
     * @return array
     */
    private function getOptionValueArray(array $options, string $value): array
    {
        $result = [];
        $value = explode('--', $value);
        foreach ($value as $valueItem) {
            foreach ($options as $option) {
                if ($option['option_label'] === $valueItem) {
                    $result[] = $option['option_id'];
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $options
     * @param string $value
     * @return string
     */
    private function getOptionValue(array $options, string $value): string
    {
        $result = '';
        foreach ($options as $option) {
            if ($option['option_label'] === $value) {
                $result = $option['option_id'];
                break;
            }
        }
        return (string) $result;
    }

    /**
     * @return array
     */
    private function getAttributeOptions(): array
    {
        if ($this->attributeOptions === null) {
            $attributeList = $this->attributeRepository->getList(
                Product::ENTITY,
                $this->searchCriteriaBuilder->create()
            );

            $i = 0;

            foreach ($attributeList->getItems() as $attribute) {
                $attribute->setStoreId(0);
                $this->attributeOptions[$i]['attribute_id'] = $attribute->getAttributeId();
                $this->attributeOptions[$i]['frontend_input'] = $attribute->getFrontendInput();
                $this->attributeOptions[$i]['attribute_frontend_label'] = $attribute->getFrontendLabel();
                $attributeOptions = $attribute->getOptions();
                $j = 0;

                foreach ($attributeOptions as $attributeOption) {
                    $this->attributeOptions[$i]['options'][$j]['option_id'] = $attributeOption->getValue();
                    $this->attributeOptions[$i]['options'][$j]['option_label'] = $attributeOption->getLabel();
                    $j++;
                }

                $i++;
            }
        }

        return $this->attributeOptions;
    }

    /**
     * @return array
     */
    private function getAllCategories(): array
    {
        if ($this->allCategories === null) {
            $categoryCollection = $this->categoryCollectionFactory->create();
            $this->allCategories = $categoryCollection->getItems();
        }

        return $this->allCategories;
    }

    /**
     * @param string $categories
     * @return array
     * @throws LocalizedException
     */
    private function getCategories(string $categories): array
    {
        $categoryIds = explode(',', $categories);
        $allCategories = $this->getAllCategories();
        $result = [];

        foreach ($categoryIds as $categoryId) {
            if (!isset($allCategories[$categoryId])) {
                throw new LocalizedException(__('Category does not exists.'));
            }

            $result[] = $categoryId;
        }

        return $result;
    }
}
