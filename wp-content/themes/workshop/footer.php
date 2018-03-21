        <footer class="footer">
            <?php
			wp_nav_menu([
				'theme_location' => 'footer',
				'menu_id'        => 'footer',
            ]);?>
            <div class="copyright" >
                <p>
                    &copy; <a href="<?= get_site_url();?>"><?= get_bloginfo( 'name' ).' - '.date('Y');?></a>
                </p>                    
            </div>

        </footer>
    </body>
    <?php the_workshop_scripts();?>
</html>