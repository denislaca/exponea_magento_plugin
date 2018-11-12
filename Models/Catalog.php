<?php
namespace Exponea\Exponea\Models; 

class Catalog {
    public function updateCatalog() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        /** Apply filters here */
        $collection = $productCollection->addAttributeToSelect('*')->load();

        $id = 1;
        $data_array = [];

        $csvArray = ["product_id", "sku", "title", "price", "url", "image", "local_currency", "local_currency_symbol", "description", "categories_path", "category_level_1", "category_level_2", "category_level_3"];
        $csvLength = sizeof($csvArray);
        $csv = implode(',', $csvArray)." \n";
        //$csv = "product_id,sku,title,price,url,image,local_currency,description,categories_path,category_level_1,category_level_2,category_level_3 \n";//Column headers

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $currency = $objectManager->get('\Magento\Directory\Model\Currency');
        $store = $storeManager->getStore();
        $currencyCode = $store->getCurrentCurrencyCode();
        
        $currentcurrency = trim($currencyCode);
        $currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currentcurrency);
        $currencySymbol = $currency->getCurrencySymbol(); 
        
        foreach ($collection as $product){
            $categoryTree = "";
            $categoryID = "";
            $catalogItem = [];

            $catalogItem[] = $product->getId(); //id
            $catalogItem[] = $product->getSku(); //sku

            $name = $product->getName();
            $removeIndexSize = array(strpos($name, '-XS-'),strpos($name, '-S-'),strpos($name, '-M-'),strpos($name, '-L-'),strpos($name, '-XL-'),strpos($name, '-2'),strpos($name, '-3'));
            $removeIndexSize = array_filter($removeIndexSize);
            if (sizeof($removeIndexSize)) {
                $removeIndexSize = min($removeIndexSize);
                $name = substr($name, 0, $removeIndexSize);
            }
            $catalogItem[] = $name; //title

            $catalogItem[] = $product->getPrice(); //price

            $url = $product->getUrl_key(); //url
            $removeIndexSize = array(strpos($url, '-xs-'),strpos($url, '-s-'),strpos($url, '-m-'),strpos($url, '-l-'),strpos($url, '-xl-'));

            if (! array_sum($removeIndexSize)) {
                $indexDash = strpos($url, '-');
                if (is_numeric($url[$indexDash + 1])) {
                    echo $url."---".$url[$indexDash + 1];
                }
            }

            $removeIndexSize = array_filter($removeIndexSize);
            if (sizeof($removeIndexSize)) {
                $removeIndexSize = min($removeIndexSize);
                $url = substr($url, 0, $removeIndexSize);
            }
            $catalogItem[] = $url.".html"; //title

            $catalogItem[] = $product->getImage(); //image
            $catalogItem[] = $currencyCode; //local_currency
            $catalogItem[] = $currencySymbol; //local_currency_symbol

            $description = ($product->getDescription());
            $description = str_replace('"', '',$description);
            $description = '"'.$description.'"';
            $catalogItem[] = $description;
            

            //categories
            $categories = $product->getCategoryCollection()->addAttributeToSelect('name');
            $categoryTree = [];

            foreach ($categories as $category) {
                $categoryID = $category->getId();
                $parentCategories = $category->getParentCategories();

                foreach ($parentCategories as $parentCategory) {
                    $categoryTree[] = $parentCategory->getName();
                }
            }

            $categoryTree = array_unique($categoryTree);

            $catalogItem[] = implode(' > ', $categoryTree);
            $categoryList = $categoryTree;
            $categoryTreeLength = sizeof($categoryTree);   
            
            $categoryList = array_values($categoryList); //reindex array

            for ($i=0; $i < 3; $i++) {
                if (isset($categoryList[$i])) {
                    $catalogItem[] = $categoryList[$i];
                }
            }


            for ($i=0; $i < $csvLength; $i++) {
                if (! isset($categoryList[$i])) {
                    $catalogItem[] = "";
                }
            }

            $data_array[] = $catalogItem;
            
        }  

        foreach ($data_array as $record){
            for ($i = 0; $i < $csvLength; $i++) {
                if ($i == $csvLength-1) {
                    $csv.= $record[$i];
                }
                else {
                    $csv.= $record[$i].',';
                }
            }
            $csv.= "\n";
        }
        

        $csv_handler = fopen ('catalog/catalog.csv','w');
        fwrite ($csv_handler,$csv);
        fclose ($csv_handler);
        
        exit;
    }
}
