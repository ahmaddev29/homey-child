<?php
global $post, $homey_local;
$post_id = $post->ID;
$have_guided_service = get_field('have_guided_service', $post_id);
$guide_bio = get_field('guide_bio', $post_id);
$what_expect = get_field('what_expect', $post_id);
$price_type = get_field('price_type', $post_id);
$price_rate = get_field('price_rate', $post_id);
$guided_price = get_field('guided_price', $post_id);
$what_permitted = get_field('what_permitted', $post_id);
$what_not_permitted = get_field('what_not_permitted', $post_id);
$who_permitted = get_field('who_permitted', $post_id);
$who_not_permitted = get_field('who_not_permitted', $post_id);
$license_required = get_field('license_required', $post_id);
$guest_provide = get_field('guest_provide', $post_id);
$gears_list = get_field('gears_list', $post_id);
$gears_price = get_field('gears_price', $post_id);
$guest_wear = get_field('guest_wear', $post_id);
$maximum_guests = get_field('maximum_guests', $post_id);
$non_participants = get_field('non_participants', $post_id);
$non_participants_price = get_field('non_participants_price', $post_id);
$equipment_data = get_field('equipments_rows', $post_id);

$display_price_type = ucwords(str_replace('_', ' ', $price_type));
$display_price_rate = ucwords(str_replace('_', ' ', $price_rate));

if ($have_guided_service != 'no_guide_needed') {
?>
  <div id="additional-services" class="additional-services-section">
    <div class="block">
      <div class="block-section">
        <div class="block-body">
          <div class="section-title">
            <h3 class="title">Guided Service</h3>
          </div><!-- block-left -->
          <div class="section-body">
            <div class="row">
              <div class="col-sm-4 col-xs-12">
                <strong>Amenities</strong>
                <p>
                  <?php if (!empty($price_type)) { ?>
                    <span style="display: block;"><i class="fa-solid fa-filter-circle-dollar"></i> Price Type: <?php echo esc_html($display_price_type); ?></span>
                  <?php } ?>
                  <?php if (!empty($price_rate)) { ?>
                    <span style="display: block;"><i class="fa-solid fa-dollar-sign"></i> Price Rate: <?php echo esc_html($display_price_rate); ?></span>
                  <?php } ?>
                  <?php if (!empty($guided_price)) { ?>
                    <span style="display: block;"><i class="fa fa-money"></i> Price: $<?php echo esc_html($guided_price); ?> </span>
                  <?php } ?>
                  <?php if (!empty($maximum_guests)) { ?>
                    <span style="display: block;"><i class="fa-solid fa-user" aria-hidden="true"></i> Guests: <?php echo esc_html($maximum_guests); ?></span>
                  <?php } ?>
                  <?php if (!empty($non_participants)) { ?>
                    <span style="display: block;"><i class="fa-solid fa-user" aria-hidden="true"></i> Non-participants: <?php echo esc_html($non_participants); ?></span>
                  <?php } ?>
                  <?php if (!empty($non_participants_price)) { ?>
                    <span style="display: block;"><i class="fa fa-money"></i> Non-participants Price: $<?php echo esc_html($non_participants_price); ?>
                    </span>
                  <?php } ?>
                </p>
              </div>

              <?php if (!empty($guide_bio)) {
                // Strip HTML tags and then truncate the content
                $clean_bio = strip_tags($guide_bio);
                $truncated_bio = substr($clean_bio, 0, 350);
                $bio_length = strlen($clean_bio);
              ?>
                <div class="col-sm-8 col-xs-12">
                  <strong>Guide Bio</strong>
                  <p id="guide-bio-short">
                    <?php echo esc_html($truncated_bio); ?>
                    <?php if ($bio_length > 350) { ?>
                      ... <a href="#" id="show-more-bio">Show more</a>
                    <?php } ?>
                  </p>
                  <?php if ($bio_length > 350) { ?>
                    <p id="guide-bio-full" style="display: none;">
                      <?php echo esc_html($clean_bio); ?>
                      <a href="#" id="show-less-bio">Show less</a>
                    </p>
                  <?php } ?>
                </div>
              <?php } ?>


            </div>

            <div class="row">
              <?php if (!empty($what_expect)) {
                $words = explode(' ', $what_expect);
                $first_four_words = array_slice($words, 0, 5);
                $shortened_content = implode(' ', $first_four_words) . '....';
              ?>
                <div class="col-sm-4 col-xs-12">
                  <strong>What to expect</strong>
                  <p><?php echo esc_html($shortened_content); ?></p>
                </div>
              <?php } ?>


              <?php if (!empty($what_permitted)) { ?>
                <div class="col-sm-4 col-xs-12">

                  <strong>What is permitted</strong>
                  <p><?php echo esc_html($what_permitted); ?></p>

                </div>
              <?php } ?>

              <?php if (!empty($what_not_permitted)) { ?>
                <div class="col-sm-4 col-xs-12">

                  <strong>What is not permitted</strong>
                  <p><?php echo esc_html($what_not_permitted); ?></p>

                </div>
              <?php } ?>

              <?php if (!empty($who_permitted)) { ?>
                <div class="col-sm-4 col-xs-12">

                  <strong>Who is permitted</strong>
                  <p><?php echo esc_html($who_permitted); ?></p>

                </div>
              <?php } ?>

              <?php if (!empty($who_not_permitted)) { ?>
                <div class="col-sm-4 col-xs-12">
                  <strong>Who is not permitted</strong>
                  <p><?php echo esc_html($who_not_permitted); ?></p>
                </div>
              <?php } ?>

              <?php if (!empty($license_required)) { ?>
                <div class="col-sm-4 col-xs-12">

                  <strong>Required License</strong>
                  <p><?php echo esc_html($license_required); ?></p>

                </div>
              <?php } ?>

              <?php if (!empty($guest_provide)) { ?>
                <div class="col-sm-4 col-xs-12">

                  <strong>Required Documents</strong>
                  <p><?php echo esc_html($guest_provide); ?></p>

                </div>
              <?php } ?>

              <?php if (!empty($guest_wear)) { ?>
                <div class="col-sm-4 col-xs-12">
                  <strong>Guest(s) should wear</strong>
                  <p><?php echo esc_html($guest_wear); ?></p>

                </div>
              <?php } ?>
              <?php if (!empty($equipment_data)) { ?>
                <div class="col-sm-4 col-xs-12">
                  <strong>Equipment rentals</strong>
                  <p>
                    <?php foreach ($equipment_data as $row) { ?>
                      <span style="display: block;">
                        <?php echo esc_html($row['equipment_name']); ?>:
                        $<?php echo esc_html($row['equipment_price']); ?>
                        <?php echo esc_html($row['equipment_type'] === 'total_fee' ? 'Total' : 'Per Guest'); ?>
                      </span>
                    <?php } ?>
                  </p>
                </div>
              <?php } ?>


            </div>
          </div><!-- block-right -->
        </div><!-- block-body -->
      </div><!-- block-section -->
    </div><!-- block -->
  </div><!-- accomodation-section -->
<?php } ?>