<?php
class AE_text
{
    public $parent, $field, $value;
    /**
     * Field Constructor.
     *
     * @param array $field
     * - id
     * - name
     * - placeholder
     * - readonly
     * - class
     * - title
     * @param $value
     * @param $parent
     * @since AEFramework 1.0.0
     */
    function __construct($field = array(), $value = '', $parent = array())
    {

        //parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * Field Render Function.
     *
     * Takes the vars and outputs the HTML for the field in the settings
     *
     * @since AEFramework 1.0.0
     */
    function render()
    {

        $readonly = isset($this->field['readonly']) ? ' readonly="readonly"' : '';
        $placeholder = (isset($this->field['placeholder']) && !is_array($this->field['placeholder'])) ? ' placeholder="' . esc_attr($this->field['placeholder']) . '" ' : '';

        $default = isset($this->field['default']) ? $this->field['default'] : '';
        if (is_array($this->value)) {
            $value = '';
        } else {
            $value = ($this->value) ? (esc_attr($this->value)) : $default;
        }

        if (isset($this->field['label']) && $this->field['label'] !== '') {
            echo '<label for="' . $this->field['id'] . '">' . $this->field['label'] . '</label>';
        }
        echo '<input type="text" id="' . $this->field['id'] . '" name="' . $this->field['name'] . '" ' . $placeholder . '" value="' . $value . '" class="regular-text ' . $this->field['class'] . '"' . $readonly . ' /><br />';

        if (isset($this->field['reset']) && $this->field['reset']) {
            echo '<div style="margin-top: 10px;" class="mail-control-btn"><a href="#" class="reset-text-default" data-name="' . $this->field['name'] . '" data-default="' . $default . '">' . __('Reset to default', 'enginethemes') . '</a></div>';
        }
    } //render

}
