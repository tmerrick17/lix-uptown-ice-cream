<div id="new-flaves"  class="container">

  <div class="group-flaves__new row">
    <div class="col">

      <div class="group-flaves__new__title">
        <?php
          get_template_part('partials/sections/partial', 'title',
            array(
              'data' => array(
                'title' => 'New Flaves',
              )
            )
          );
        ?>
      </div>

      <div class="group-flaves__new__clipboards">
        <div class="row">

          <!-- New Flavor Clipboard Layout -->
          <?php
            for ($i = 1; $i <= 6; $i++) {
              $new_flavor = get_field('new_flavor_' . $i);
              $img_class = 'clipboard__img--' . $i;
            ?>
              <div class="col-12 col-sm-6 col-lg-4 col-xl-2">
                <?php
                get_template_part('partials/partial', 'clipboard',
                  array(
                    'img_class' => $img_class,
                    'data' => array(
                      'acf-item' => $new_flavor,
                    )
                  )
                )
                ?>
            </div>
          <?php
          }
          ?>

        </div>
      </div>

    </div>
  </div>
</div>