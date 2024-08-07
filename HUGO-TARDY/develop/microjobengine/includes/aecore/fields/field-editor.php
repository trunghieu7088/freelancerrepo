<?php
class AE_editor
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
        $readonly       = isset($this->field['readonly']) ? ' readonly="readonly"' : '';
        $placeholder    = (isset($this->field['placeholder']) && !is_array($this->field['placeholder'])) ? ' placeholder="' . esc_attr($this->field['placeholder']) . '" ' : '';
        $default = isset($this->field['default']) ? $this->field['default'] : '';
        $value = ($this->value) ? $this->value : $default;
        echo '<textarea id="' . $this->field['id'] . '" name="' . $this->field['name'] . '" ' . $placeholder .
            ' class="regular-editor editor ' . $this->field['class'] . '"' . $readonly . ' >' . esc_attr($value) . '</textarea>';
        if (isset($this->field['reset']) && $this->field['reset']) {
            echo '<div style="margin-top: 10px;" class="mail-control-btn"><a href="#" class="reset-default">' . __('Reset to default', 'enginethemes') . '</a></div>';
        }
    } //render

}
