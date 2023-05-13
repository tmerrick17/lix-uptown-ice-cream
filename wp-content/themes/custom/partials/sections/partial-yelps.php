<div id="yelps" class="yelps container">

  <div class="row">
    <div class="col">
      <div class="yelps__title">
        <?php
          get_template_part('partials/sections/partial', 'title',
            array(
              'data' => array(
                'title' => 'Lix Luv',
              )
            )
          );
        ?>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <?php echo do_shortcode('[reviews-feed feed=1] '); ?>
    </div>
  </div>

</div>