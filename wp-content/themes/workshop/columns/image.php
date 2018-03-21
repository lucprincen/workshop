<?php
$url = $column->getField( 'medium' );
if( !$url || $url == '' )
    $url = $column->getField( 'full' );
?>
<div class="column image">
	<img src="<?= esc_url( $url );?>"/>
</div>