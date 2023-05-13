<div class="clipboard">
  <div class="clipboard__img">
    <img
      class="clipboard__img__src <?php if ( $args['img_class'] ) { echo esc_html( $args['img_class'] ); } ?> img-fluid"
      src="<?php echo get_template_directory_uri(); ?>/public/images/flaves/clipboard.svg" alt=""
    >
    <span class="clipboard__flavor">
      <?php if ( $args['data'] ) { echo $args['data']['acf-item']; } ?>
    </span>
  </div>

</div>