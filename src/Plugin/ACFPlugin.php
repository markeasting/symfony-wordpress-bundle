<?php

namespace Metabolism\WordpressBundle\Plugin;

use Metabolism\WordpressBundle\Entity\Block;

/**
 * Class Metabolism\WordpressBundle Framework
 */
class ACFPlugin
{

    /**
     * ACFPlugin constructor.
     */
    public function __construct()
    {
        $acf_settings = [
            'autoload' => true,
            'use_entity' => true,   // Optimized Post entity return format
        ];

        foreach ($acf_settings as $name => $value) {
            if (acf_get_setting($name) !== $value)
                acf_update_setting($name, $value);
        }

        add_filter('acf/validate_field', [$this, 'validateField']);
        add_filter('block_render_callback', [$this, 'renderBlock']);

        add_filter('acf/pre_load_value', [$this, 'preLoadValue'], 10, 3);
    }

    /**
     * Add optimized 'entity' return format + use as default
     * 
     * @param $field
     * @return array
     */
    public function validateField($field)
    {

        if ($field['name'] === 'return_format') {
            if (isset($field['choices']['object']))
                $field['choices']['link'] = __('Link');

            $field['choices']['entity'] = __('Entity');
            $field['default_value'] = 'entity';
        }

        return $field;
    }

    /**
     * Render block
     * 
     * @param $acf_block
     * @return void
     */
    public static function renderBlock($acf_block)
    {

        $block = [
            'blockName' => $acf_block['name'],
            'attrs' => $acf_block,
        ];

        if ($image = $acf_block['data']['_preview_image'] ?? false) {

            echo '<img src="' . get_home_url() . '/' . $image . '" style="width:100%;height:auto" class="preview_image"/>';
            return;
        }

        $block = new Block($block);

        echo $block->render();
    }

    /**
     * Disable database query for non editable field
     * 
     * @param $unused
     * @param $post_id
     * @param $field
     * @return string|null
     */
    public function preLoadValue($unused, $post_id, $field)
    {

        if ($field['type'] == 'message' || $field['type'] == 'tab')
            return '';

        return null;
    }
}
