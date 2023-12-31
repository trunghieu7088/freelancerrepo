<?php
/**
 * Class AE_Group
 * create a group of input element
 * @author Dakachi
*/
class AE_group {
    /**
     * Field Constructor.
     *
     * @param array $params
     * - html tag
     * - id
     * - name
     * - class
     * - title
     * - desc
     * @param $field
     * @param $parent
     * @since AEFramework 1.0.0
    */
    function __construct( $params = array(), $fields = array(), $parent = array() ) {

        //parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->params = $params;

        $temp   =   array ();

        $group_name  =  isset( $this->params['name'] ) ?  $this->params['name'] : '' ;
        // echo $group_name;
        foreach ( $fields as $key => $field) {
            $type   =   'AE_'.$field['type'];
            $name   =   $field['name'];
            if( $group_name == '' )  $value  =   $parent->$name;
            else {
                $value  =   $parent->$group_name;
                if( is_array($value) ) {
                    if ( isset($value[$name]) )  $value = $value[$name];
                    else $value = '';
                }
            }
            if( class_exists($type) )
                $temp[] =   new AE_BackendField ( new $type ( $field , $value , $parent ) );
        }
        $this->fields = $temp;
    }
    /**
     * render group html
     * @author Dakachi
    */
    function render() {
        $fields =   $this->fields;
        $group_name  =  isset( $this->params['name'] ) ?  'data-name="'.$this->params['name'].'"' : '' ;

        $toggle = isset($this->params['toggle']) ? $this->params['toggle'] : false;
        $toggle_class = $toggle ? 'toggle-desc' : '';
        $display = $toggle ? 'style="display:none;"' : '';

        echo '<div class="'. $this->params['class'] .'" >';
        echo '<form '. $group_name .'>';
            echo '<div class="'.$toggle_class.' title group-'. $this->params['id'] .'">'. $this->params['title'] .'</div>';
            /**
             * print group description
            */

            echo '<div class="desc" >';
                if(isset($this->params['desc'])) echo '<span class="group-desc">'.$this->params['desc'] .'</span>';
                if($toggle) echo '<div '.$display.' class="toggle">';
                // render group field
                if(is_array($fields)) {
                    /**
                     * render group menus
                    */
                    foreach ( $fields as $key => $field ) {
                        $field->render();
                    }

                } else {
                    $fields->render ();
                }
                if($toggle) echo '</div>';
            echo '</div>';
        echo '</form>';
        echo '</div>';
    }

}


/**
 * class adapt field to compatible with backend settings
 * @since 1.0
 * @author Dakachi
*/
class AE_BackendField {
    public $field;
    function __construct ($field) {
        $this->field    =   $field;
    }

    function render() {

        $item = $this->field->field;
        $type = isset($item['type']) ? $item['type'] : '';
        $type_css = isset($item['type']) ? "form-item-{$type} wrap-form-type-{$type}" : '';

        echo '<div class="form no-margin no-padding no-background"><div class="form-item '.$type_css.'">';
                    $this->field->render();
        echo '</div></div>';
    }
}