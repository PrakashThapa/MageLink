<?php
namespace MageDeveloper\Magelink\ViewHelpers\Magento\Product;

	/***************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2013
	 *  All rights reserved
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 3 of the License, or
	 *  (at your option) any later version.
	 *
	 *  The GNU General Public License can be found at
	 *  http://www.gnu.org/copyleft/gpl.html.
	 *
	 *  This script is distributed in the hope that it will be useful,
	 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *  GNU General Public License for more details.
	 *
	 *  This copyright notice MUST APPEAR in all copies of the script!
	 ***************************************************************/

/**
 *
 *
 * @package magelink
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class CollectionViewHelper extends \MageDeveloper\Magelink\ViewHelpers\Magento\AbstractMagentoViewHelper
{
	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments()
	{
		parent::initializeArguments();
		$this->registerArgument("addAttributeToFilter", "array", "Method Params for 'addAttributeToFilter'", false, array());
		$this->registerArgument("addFieldToFilter", "array", "Method Params for 'addFieldToFilter'", false, array());

		$this->registerArgument("getFirstItem", "bool", "Gets the first item of the collection'", false, false);
		$this->registerArgument("getLastItem", "bool", "Gets the last item of the collection'", false, false);

		$this->registerArgument("cache", "bool", "Caches Product Data", false, true);
		$this->registerArgument("as", "string", "Collection Alias for Template", false, "collection");
	}
	
	/**
	 * Gets a product
	 * 
	 * @return \string Attribute Value
	 */
	public function render()
	{
		$cacheIdentifier = "category_collection_" .
			md5(implode("-", $this->arguments["addAttributeToFilter"])) .
			md5(implode("-", $this->arguments["addFieldToFilter"]))
		;
		$productArr = array();

		// Check if product is in cache, and caching can be used
		if ((FALSE === ($productArr = $this->cacheService->get($cacheIdentifier))) || !$this->arguments["cache"])
		{
			if ($this->magentoCore->init())
			{
				$products 		= \Mage::getModel("catalog/product")
					->getCollection()
					->addAttributeToSelect("entity_id");


				// addAttributeToFilter
				if (is_array($this->arguments["addAttributeToFilter"]) && !empty($this->arguments["addAttributeToFilter"]))
				{
					foreach ($this->arguments["addAttributeToFilter"] as $_name=>$_value)
					{
						$products->addAttributeToFilter($_name, $_value);
					}
				}
				
				// addFieldToFilter
				if (is_array($this->arguments["addFieldToFilter"]) && !empty($this->arguments["addFieldToFilter"]))
				{
					foreach ($this->arguments["addFieldToFilter"] as $_name=>$_value)
					{
						$products->addFieldToFilter($_name, $_value);
					}
				}
				
				$ids 			= $products->getAllIds();
				$store			= $this->settingsService->getStoreViewCode();

				foreach($ids as $_id)
				{
					$data = array();
					$product = \Mage::getModel("magelink/product_api")->fetch($_id, $store);
					$data = json_decode($product, true);

					$productArr[] = $data;
				}

				$lifetime = $this->settingsService->getCacheLifetime();
				$this->cacheService->set( $cacheIdentifier, $productArr, array(), $lifetime );
			}
			
		}
		
		if ($this->arguments["getFirstItem"])
		{
			$productArr = reset($productArr);
		}
		else if ($this->arguments["getLastItem"])
		{
			$productArr = end($productArr);
		}
		
		
		
		
		$output = '';
		$this->templateVariableContainer->add($this->arguments["as"], $productArr);
		return $output;
	}
	
	
	
	
}