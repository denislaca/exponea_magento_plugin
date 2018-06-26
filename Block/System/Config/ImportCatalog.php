<?php
namespace Exponea\Exponea\Block\System\Config;

class ImportCatalog extends \Magento\Config\Block\System\Config\Form\Field {

    protected $_template = 'Exponea_Exponea::system/config/import-catalog.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);        
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        return $this->_toHtml();
    }

    public function getAjaxUrl() {
        return $this->getUrl('exponea_exponea/system_config/importcatalog');
    }

    public function getButtonHtml() {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'id' => 'import_catalog',
            'label' => __('Import catalog'),
        ]);

        return $button->toHtml();
    }
}

