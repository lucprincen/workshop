<?php
namespace Cuisine\Fields;


class TextareaField extends DefaultField{


    /**
     * Method to override to define the input type
     * that handles the value.
     *
     * @return void
     */
    protected function fieldType(){
        $this->type = 'textarea';
    }



    /**
     * Build the html
     *
     * @return String;
     */
    public function build(){

        $html = '<textarea ';

            $html .= 'id="'.$this->id.'" ';

            $html .= 'class="'.$this->getClass().'" ';

            $html .= 'name="'.$this->name.'" ';

            $html .= $this->getPlaceholder();

            $html .= $this->getValidation();

            if( $this->getProperty( 'rows' ) )
                $html .= ' rows="'.$this->getProperty( 'rows' ).'" ';

            if( $this->getProperty( 'cols' ) )
                $html .= ' cols="'.$this->getProperty( 'cols' ).'" ';


        $html .= '>';

            $val = $this->getValue();

            if( $val )
                $html .= $val;

        $html .= '</textarea>';

        return $html;
    }



    /**
     * Get the value of this field:
     * 
     * @return String
     */
    public function getValue(){

        global $post;
        $value = $val = false;

        if( $value && !$val )
            $val = $value;

        if( $this->properties['defaultValue'] && !$val )
            $val = $this->getDefault();


        if( $this->getProperty('stripSlashes') == true )
            $val = stripcslashes( $val );

        return $val;
    }


}