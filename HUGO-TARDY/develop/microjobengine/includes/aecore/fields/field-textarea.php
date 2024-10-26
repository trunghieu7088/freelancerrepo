<?php
class AE_textarea {
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
    function __construct( $field = array(), $value ='', $parent = array() ) {

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
    function render() {

        $value = isset($this->value) ? stripslashes( $this->value ): '';
        $readonly       = isset($this->field['readonly']) ? ' readonly="readonly"' : '';
        $placeholder    = (isset($this->field['placeholder']) && !is_array($this->field['placeholder'])) ? ' placeholder="' . esc_attr($this->field['placeholder']) . '" ' : '';
        if( isset( $this->field['label']) && $this->field['label'] !== '') {
            echo '<label for="'. $this->field['id'] .'">'. $this->field['label'] .'</label>';
        }
        echo '<textarea id="' . $this->field['id'] . '" name="' . $this->field['name'] . $placeholder .
              '" class="regular-text ' . $this->field['class'] . '"'.$readonly.' >'.$value. '</textarea>';
    }//render

}
