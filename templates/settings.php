<!--MY SETTINGS PAGE -->
<div class="wrap">
    <h2>Comment Filter</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('commentfilter-group'); ?>
        <?php @do_settings_fields('commentfilter-group'); ?>

        <?php do_settings_sections('commentfilter'); ?>

        <?php @submit_button(); ?>
    </form>
</div>