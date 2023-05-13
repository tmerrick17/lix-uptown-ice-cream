<nav class="mobile-menu">
  <?php
    wp_nav_menu(
      array(
        'theme_location' => 'mobile-menu',
        'menu_class' => 'mobile-menu__items'
      )
    );
  ?>
</nav>