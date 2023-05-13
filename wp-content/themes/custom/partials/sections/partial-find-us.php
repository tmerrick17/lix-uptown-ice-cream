<div id="find-us" class="find-us container-fluid">
  <div class="container">

    <div class="row justify-content-end">
      <div class="find-us__content col-12 col-lg-9">

        <div class="row justify-content-around px-5">
          <div class="find-us__content__left col-12 col-md-5">

            <div class="find-us__content__left__title">
            <?php
              get_template_part(
                'partials/sections/partial', 'title',
                array(
                  'data' => array(
                    'title' => 'Find Us',
                  )
                )
              )
            ?>
            </div>

            <address class="find-us__content__left__address">
              <a href="https://goo.gl/maps/AyHPCQxSbBsf57pG7" target="_blank">
                607 W Osborn Rd<br>
                Phoenix, AZ 85013 <i class="fa-solid fa-location-dot"></i><br>
              </a>
              <p class="find-us__content__left__address__phone">
                <a href="tel:6029083630">(602) 908-3630
                  <i class="fa-solid fa-phone-volume"></i>
                </a>
              </p>
              <p class="find-us__content__left__address__email">
                <a href="mailto:lixuptown@gmail.com">lixuptown@gmail.com
                  <i class="fa-solid fa-envelope"></i>
                </a>
              </p>
            </address>

            <p>Tuesday - Friday: 4 - 10pm</p>
            <p>Saturday: 12 - 10pm</p>
            <p>Sunday: 12 - 8pm</p>

          </div>

          <img class="find-us__content__right col-12 col-md-7 rounded-lg" src="<?php echo get_template_directory_uri(); ?>/public/images/find-us/cones_frosted_min.jpg" alt="ice cream cones">

        </div>
      </div>
    </div>

  </div>
</div>