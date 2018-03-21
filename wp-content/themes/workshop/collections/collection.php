<?php

	$query = $column->getQuery();
	$maxRow = $column->getField( 'posts_per_row' );
	$maxPosts = $column->getField( 'posts_per_page' );
	$view = $column->getField( 'view', 'blocks' );
	$grid = $column->getField( 'grid', 'grid' );

	if( $query->have_posts() ){

		$i = 0;
		$inRow = 0;
	
		$column->theTitle();

		while( $query->have_posts() ){
			$query->the_post();
	
			if( $inRow == 0 )
				echo '<div class="block-row">';
	
					get_block_template( $column );
	
			$i++; $inRow++;
			if( $inRow == $maxRow || $i == $maxPosts || $i == $query->found_posts || $i == $query->post_count ){

				echo '</div>';
				$inRow = 0;
			
			}
	
		}

	}



