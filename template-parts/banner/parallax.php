<?php
global $post, $homey_prefix;
$page_id = $post->ID;
$image_id = get_post_meta($post->ID, $homey_prefix.'header_image', true);
$img_url = wp_get_attachment_image_src( $image_id, 'full' );
$image = '';
if( $img_url ) {
    $image = esc_url($img_url[0]);
}
?>
<section class=" top-banner-wrap <?php homey_banner_fullscreen(); ?>">

	<div class="banner-inner byl_banner " data-parallax-bg-image="<?php echo $image; ?>"><img src="<?php echo $image; ?>" alt=""></div><!-- banner-inner parallax -->

	<div class="banner-caption <?php homey_banner_search_class(); ?>">

		<div class="frog_container"><img src="/wp-content/uploads/2022/11/Frog.png" alt=""></div>

		<?php
		homey_banner_search_div_start();

		get_template_part('template-parts/banner/caption');

    	if(homey_banner_search()) {
    		get_template_part ('template-parts/search/main-search-hourly');
    	}

    	homey_banner_search_div_end();
    	?>
		
    	<div class="bannerLinks_container">
                    <?php
                    // Get all terms from the "listing_type" taxonomy
                    $terms = get_terms(array(
                    'taxonomy' => 'listing_type',
                    'hide_empty' => false,
                    ));

                    // Loop through each term and display it
                    foreach ($terms as $term) {
                        // Get the term's URL
                        $term_url = get_term_link($term);

                        // Get the image associated with the term (assuming it's stored in term meta)
                        $attach_id = get_term_meta($term->term_id, 'homey_taxonomy_img', true);
						$attachment = wp_get_attachment_image_src( $attach_id, 'full' );

                        if (!empty($attachment)) {
						?>
						<div class="bannerLinks">
                        <div class="bannerLinks_inner">
						<?php
                        echo '<img src="' . $attachment['0'] . '" alt="">';
                        //echo '<p>' . $term->name . '</p>';
                        echo '<a href="' . $term_url . '"></a>';
						?>
						</div>
                        </div>
						<?php
                    }
					}
                    ?>
        </div>

		<div class="all-cats">
			<div>
				<label for="all-cats" class="all-cats-label">

					<select name="all-cats" class="btn bs-placeholder btn-default" id="all-cats"><?php
						$terms = get_terms([
								'taxonomy' => 'listing_type',
								'hide_empty' => false,
						]);?>
						<option value="all-cats" selected>Search by all categories</option>
						<?php
						foreach ($terms as $term) : ?>
							<option value="<?php echo $term->slug; ?>"><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<span class="bs-caret"><span class="caret"></span></span>
				</label>
				<a href="#" disabled class="btn btn-primary cat-target">SEARCH</a>
			</div>

		</div>

	</div>

	</div><!-- banner-caption -->



</section><!-- header-parallax -->
<?php
// Retrieve listing data
$listings = get_posts(array(
    'post_type' => 'listing',
    'posts_per_page' => -1,
));

// Initialize an array to store listing information
$listing_data = array();

// Loop through each listing to retrieve address, latitude, and longitude
foreach ($listings as $listing) {
    $listing_id = $listing->ID;
    $address = get_post_meta($listing_id, 'homey_listing_address', true);
    $latitude = get_post_meta($listing_id, 'homey_geolocation_lat', true);
    $longitude = get_post_meta($listing_id, 'homey_geolocation_long', true);
    $rating = homey_option('rating');
    $total_rating = get_post_meta($listing_id, 'listing_total_rating', true);
    $listing_price = get_post_meta($listing_id, 'homey_hour_price', true);
    $listing_images = rwmb_meta('homey_listing_images', ['size' => 'full'], $listing_id);

    // Store listing data in an array
    $listing_data[] = array(
        'title' => get_the_title($listing->ID),
        'address' => $address,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'rating' => $rating,
        'total_rating' => $total_rating,
        'listing_price' => $listing_price,
        'listing_id' => $listing_id,
        'listing_images' => $listing_images,

    );
}
?>

<!-- HTML markup to display the map -->
<div class="listmap-hide" id="listings-map"></div>

<button class="btn btn-primary" id="show-map-button" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 999;">
Show Map <i class="fa-solid fa-map" style="margin-left: 5px;"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var customIcon = L.icon({
        iconUrl: 'https://backyardlease.flywheelsites.com/wp-content/uploads/2024/03/icon_Frog-leaning-on-a-_-e1711565393183.png',
        iconSize: [32, 37], // Adjust the size as needed
        iconAnchor: [16, 32], // Adjust the anchor point as needed
    });

    var mapContainer = document.getElementById('listings-map');
	var showMapButton = document.getElementById('show-map-button');

    // Clear previous map instance, if exists
    if (mapContainer._leaflet_id) {
        mapContainer._leaflet_id = null;
        mapContainer.innerHTML = '';
    }

    // Initialize the map
    var map = L.map('listings-map').setView([<?php echo $listing_data[0]['latitude']; ?>, <?php echo $listing_data[0]['longitude']; ?>], 4.2);

    // Add a base map layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Initialize marker cluster group
    var markers = L.markerClusterGroup();

    // Loop through listing data and add markers to the map
    <?php foreach ($listing_data as $listing): ?>
        var marker = L.marker([<?php echo $listing['latitude']; ?>, <?php echo $listing['longitude']; ?>], {icon: customIcon}).addTo(map);

        // Create the image slider HTML
        var imagesHtml = '';
        <?php foreach ($listing['listing_images'] as $image): ?>
            imagesHtml += '<div><img src="<?php echo $image['full_url']; ?>" alt="<?php echo esc_attr($listing['title']); ?>"></div>';
        <?php endforeach; ?>

        // Combine HTML for popup content
        var popupContent = `
            <div class="media-body item-body clearfix">
			    <div class="image-slider">
                    ${imagesHtml}
                </div>
				<div class="bottom-con">
                <div class="item_rate_price_container">
                <p class="item_star_review">
                    <?php if ($listing['rating'] && ($listing['total_rating'] != '' && $listing['total_rating'] != 0)) { ?>
                        <img src="/wp-content/uploads/2022/11/star.svg" alt="star"><span><span><?php echo $listing['total_rating']; ?></span></span>
                    <?php } else { ?>
                        <span>No Review Yet</span>
                    <?php } ?>
                </p>
                <?php if (!empty($listing['listing_price'])) { ?>
                    <p class="item_price_p">
                        $<?php echo $listing['listing_price']; ?>/HR
                    </p>
                <?php } ?>
                </div>
                <div class="item-title-head table-block listing-map" style="margin-bottom: 0px !important;">
                    <div class="title-head-left">
                        <h2 class="title"><a href="<?php echo get_permalink($listing['listing_id']); ?>" target="_blank"><?php echo $listing['title']; ?></a></h2>
                    </div>
                </div>
				</div>
            </div>
        `;

        // Bind popup with listing information
        marker.bindPopup(popupContent, {autoPan: true});

        markers.addLayer(marker);
    <?php endforeach; ?>

    // Add markers to the map
    map.addLayer(markers);

    // Initialize Slick Slider within Leaflet popup
    map.on('popupopen', function (e) {
        var content = e.popup._contentNode;
        var slider = jQuery(content).find('.image-slider');

        // Initialize Slick Slider if slider element is found in popup content
        if (slider.length > 0) {
            slider.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                fade: true,
                adaptiveHeight: true,
                autoplay: false,
                autoplaySpeed: 2000,
            });
        }
    });
	showMapButton.addEventListener('click', function() {              
                if (!mapContainer.classList.contains('listmap-visible')) {
                    mapContainer.classList.add('listmap-visible');
					mapContainer.classList.remove('listmap-hide');
					//document.body.classList.add('listmap-open');
					map.invalidateSize();
					showMapButton.innerHTML = 'Hide Map <i class="fa-solid fa-map" style="margin-left: 5px;"></i>';
                    mapContainer.scrollIntoView({ behavior: 'smooth' });
                } else {
                    mapContainer.classList.remove('listmap-visible');
					//document.body.classList.remove('listmap-open');
					mapContainer.classList.add('listmap-hide');
					showMapButton.innerHTML = 'Show Map <i class="fa-solid fa-map" style="margin-left: 5px;"></i>';
                }
            });
			
			jQuery('.bannerLinks_container').slick({
            slidesToShow: 7,
            slidesToScroll: 7,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: true,
            prevArrow: '<button type="button" class="slick-prev">Previous</button>',
            nextArrow: '<button type="button" class="slick-next">Next</button>',
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 5,
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 3,
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 2,
                    }
                }
            ]
        });
    });
</script>