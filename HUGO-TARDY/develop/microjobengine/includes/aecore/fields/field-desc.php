<?php
class AE_desc
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
     * - text
     * @param $value
     * @param $parent
     * @since AEFramework 1.0.0
     */
    function __construct($field = array(), $value = '', $parent = array())
    {

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
        echo '<div class="field-desc desc ' . $this->field['class'] . '">' . $this->field['desc'] . '</div>';
    } //render

}
