<div id="pix" class="pix container-fluid">
  <div class="container">

    <div class="row">
      <div class="col">
        <div class="pix__title">
          <?php
            get_template_part('partials/sections/partial', 'title',
            array(
                'class' => 'title--white',
                'data' => array(
                  'title' => 'Lix Pix',
                )
              )
            );
          ?>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <?php echo do_shortcode('[instagram-feed feed=1] '); ?>
      </div>
    </div>

  </div>
</div>