<?php

namespace ChefSections\Wrappers;

class SectionsBuilder extends Wrapper {

    /**
     * Return the igniter service key responsible for the Field class.
     * The key must be the same as the one used in the assigned
     * igniter service.
     *
     * @return string
     */
    protected static function getFacadeAccessor(){
        return 'sectionsbuilder';
    }

}
