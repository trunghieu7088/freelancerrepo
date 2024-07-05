<?php

/**
 * Class AE_container
 * create a elements container , it can contain anything
 * @author Dakachi
 */
class AE_container
{
    public $parent, $field, $sections;
    /**
     * Field Constructor.
     *
     * @param array $params
     * - html tag
     * - id
     * - name
     * - class
     * - title
     * @param array $sections
     * @param $parent
     * @since AEFramework 1.0.0
     */
    function __construct($params = array(), $sections = array(), $parent = array())
    {

        //parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->field = $params;
        $this->sections = $sections;
    }

    /**
     * render container element
     */
    function render()
    {
        $sections   =   $this->sections;

        /*echo '<div class="et-main-content '. $this->field['class'] .'" id="'. $this->field['id'] .'" >' ;*/
        // render menu if have  more then 1 section
        if (is_array($sections)) {
            /**
             * render section menus
             */
            echo '<div class="et-menu-setting"><ul class="inner-menu">';
            $first = false;
            foreach ($sections as $key => $section) {
                if ($key == 0)
                    $first = true;
                $section->render_menu($first);
                $first = false;
            }
            echo '</ul></div>';
            echo '<div class="et-main-content ' . $this->field['class'] . '" id="' . $this->field['id'] . '" >';
            echo '<div class="settings-content">';
            $first = false;
            foreach ($sections as $key => $section) {
                if ($key == 0)
                    $first = true;
                $section->render($first);
                $first = false;
            }

            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="one-column">';
            $sections->render(true);
            echo '</div>';
        }

        /*echo '</div>';*/
    }
}
