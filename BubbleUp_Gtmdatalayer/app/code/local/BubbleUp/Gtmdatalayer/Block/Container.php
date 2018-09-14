<?php 
class BubbleUp_Gtmdatalayer_Block_Container extends Mage_Core_Block_Template
{
    public $defaultContainerTemplate = 'bubbleup_gtmdatalayer/container.phtml';
    public $config;
    protected $beforeContainersHtml = "\n<!-- BEGIN GTM Container(s) from BubbleUp_Gtmdatalayer module -->\n";
    protected $afterContainersHtml = "\n<!-- END GTM Container(s) from BubbleUp_Gtmdatalayer module -->\n";

    public function _construct()
    {
        $this->config = Mage::helper('gtmdatalayer/config');
    }

    public function getContainerTemplate()
    {
        $templateDefinedInLayout = $this->getData('tag_template');

        if (empty($templateDefinedInLayout)) {
            return $this->defaultContainerTemplate;
        }

        return $templateDefinedInLayout;
    }



    public function renderGtmContainerBlock($gtmContainerId)
    {
        $block = $this->getLayout()->createBlock('core/template');

        $block->setTemplate($this->getContainerTemplate());

        $block->addData([
            'container_id'          => $gtmContainerId,
            'consent_variable_name' => $this->config->getConsentVariableName(),
            'consent_required'      => $this->config->getRequireConsent(),
        ]);

        return $block->toHtml();
    }


    public function _toHtml()
    {
        if (!$this->config->getEnabled()) {
            return "<!-- GTM is disabled in this store -->";
        }

        $containerIds = $this->config->getContainerIdsFromConfigAsArray();

        $containers = array_map(function ($containerId) {
            return $this->renderGtmContainerBlock($containerId);
        }, $containerIds);

        return $this->beforeContainersHtml . implode("\n", $containers) . $this->afterContainersHtml;
    }
}
