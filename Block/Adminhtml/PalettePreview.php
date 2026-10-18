<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Stoneholl\AdminAccentColor\Model\Config;
use Stoneholl\AdminAccentColor\Model\PaletteDefinition;

class PalettePreview extends Template
{
    /** @var Config */
    private $config;
    /** @var Json */
    private $json;

    /**
     * @param Template\Context $context
     * @param Config $config
     * @param Json $json
     * @param array $data
     */
    public function __construct(Template\Context $context, Config $config, Json $json, array $data = [])
    {
        $this->config = $config;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * Expose only the allowlisted global palette for the personal/global preview toggle.
     *
     * @return string
     */
    public function getGlobalPaletteJson(): string
    {
        $settings = $this->config->getGlobalSettings();
        $values = [];
        foreach (PaletteDefinition::AREAS as $area => $definition) {
            $values[$area . '_enabled'] = $settings->isAreaEnabled($area) ? '1' : '0';
            foreach ($definition['fields'] as $field => $label) {
                $values[$field] = $settings->getColor($field) ?? '';
            }
        }
        return $this->json->serialize($values);
    }

    /**
     * Describe the shared editor's allowlisted target fields and area switches.
     *
     * @return array
     */
    public function getAreas(): array
    {
        return PaletteDefinition::AREAS;
    }

    /**
     * Provide the fixed element/property mapping without adding persisted settings.
     *
     * @return array
     */
    public function getElements(): array
    {
        return PaletteDefinition::ELEMENTS;
    }

    /**
     * Serialize native same-origin URLs and the fixed palette mapping for the local editor.
     *
     * @return string
     */
    public function getEditorConfigJson(): string
    {
        return $this->json->serialize([
            'elements' => PaletteDefinition::ELEMENTS,
            'colors' => PaletteDefinition::COLOR_FIELDS,
            'urls' => [
                'index' => $this->getUrl('adminaccentcolor/preset/index'),
                'save' => $this->getUrl('adminaccentcolor/preset/save'),
                'delete' => $this->getUrl('adminaccentcolor/preset/delete')
            ]
        ]);
    }
}
