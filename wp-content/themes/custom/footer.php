<?php wp_footer(); ?>

<footer class="footer container-fluid">
  <div class="container">

    <div class="row justify-content-between align-items-center">
      <div class="col-2">
        <img class="img-fluid" src="<?php echo get_template_directory_uri(); ?>/public/images/lix_logo-blue.svg" alt="Lix Uptown Ice Cream Logo">
      </div>
      <div class="col-10">
        <p>
          &copy; Copyright <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All Rights Reserved.
        </p>
      </div>
    </div>

  </div>

</footer>

</body>
</html>