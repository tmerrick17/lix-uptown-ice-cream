<?php get_header(); ?>

  <main class="main-home-page container-fluid">

    <?php get_template_part('partials/sections/partial', 'why-us') ?>

    <?php get_template_part('partials/sections/partial', 'background-img',
      array('class' => 'near-top'),
      )
    ?>

    <?php get_template_part('partials/sections/partial', 'new-flaves') ?>

    <?php get_template_part('partials/sections/partial', 'orig-flaves') ?>

    <?php get_template_part('partials/sections/partial', 'background-img',
      array('class' => 'near-middle'),
      )
    ?>

    <?php get_template_part('partials/sections/partial', 'pix') ?>

    <?php get_template_part('partials/sections/partial', 'yelps') ?>

    <?php get_template_part('partials/sections/partial', 'find-us') ?>

    <?php get_template_part('partials/sections/partial', 'background-img',
      array('class' => 'near-bottom'),
      )
    ?>

    <?php get_template_part('partials/sections/partial', 'gift-card') ?>

  </main>

<?php get_footer(); ?>