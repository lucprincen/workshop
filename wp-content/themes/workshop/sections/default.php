<?php
	
if( $section->hide_title === 'false' )
    $section->theTitle();
    
echo '<section class="'.esc_attr( $section->view ).'">';
                
    the_columns( $section );

echo '</section>';
