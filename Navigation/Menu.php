<?php
/**
 * @description simple menu constructing
 * @author Se#
 * @version 0.0.1
 */
class Evil_Navigation_Menu extends Evil_Navigation
{
    /**
     * @description create an HTML view of a menu
     * @param string $name
     * @param string $result
     * @return string
     * @author Se#
     * @version 0.0.1
     */
    public function deploy($name = 'menu', $result = '')
    {
        if(!empty($this->_config))
        {
            $result .= '<div class="' . $name . '">';
            foreach($this->_config as $href => $title)
                $result .= '<li class="item"><a href="' . $href . '">' . $title . '</a></li>';

            $result .= '</div>';
        }

        return $result;
    }
}