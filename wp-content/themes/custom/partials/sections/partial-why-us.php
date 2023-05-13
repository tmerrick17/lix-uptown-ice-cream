<div id="why-us" class="why-us container-fluid">
  <div class="container">

    <div class="row">
      <div class="why-us__video col-12">
        <?php
          echo wp_video_shortcode(array(
            'src' => 'http://localhost:8000/wp-content/uploads/2023/05/lix-video.mp4',
            'poster' => 'http://localhost:8000/wp-content/themes/custom/public/images/backgrounds/wallpaper-wall-shot.jpg',
            'width' => '1200',
            'preload' => 'auto',
          ));
        ?>
      </div>
    </div>

    <div class="why-us__content row justify-content-center">
      <div class="why-us__content__mission col-12 col-lg-6">

        <div class="why-us__content__mission__title">
          <?php
          get_template_part(
            'partials/sections/partial',
            'title',
            array(
              'data' => array(
                'title' => 'Why Us',
              )
            )
          );
          ?>
        </div>

        <div class="why-us__content__mission__copy">
          <p>We start every batch with the intention that it provokes a serendipitous experience in each and every Guest!</p>
          <p>Only positive vibes are allowed in the kitchen when we cook and churn. In fact, we each make it a dance party! 🎉</p>
        </div>

      </div>
    </div>

  </div>
</div>