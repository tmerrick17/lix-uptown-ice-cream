<div id="gift-card" class="gift-card container-fluid">
  <div class="container">

    <div class="gift-card__title">
      <?php
        get_template_part(
          'partials/sections/partial', 'title',
          array(
            'class' => 'title--white',
            'data' => array(
              'title' => 'Gift Cards',
            )
          )
        )
      ?>
    </div>

    <div class="gift-card__cards">
      <img class="gift-card__cards__card-1" src="<?php echo get_template_directory_uri(); ?>/public/images/gift-card/gift_card.svg" alt="gift card">
      <img class="gift-card__cards__card-2" src="<?php echo get_template_directory_uri(); ?>/public/images/gift-card/gift_card.svg" alt="gift card">
    </div>

    <?php
        get_template_part(
          'partials/partial', 'button',
          array(
            'data' => array(
              'button-title' => 'Give a Gift',
            )
          )
        );
      ?>

  </div>
</div>