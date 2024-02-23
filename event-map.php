<?php
/**
* Plugin Name: WP Event Map
* Description: This plugin help to create events and show those events on a single google map. All the events with their info and that map can be displayed with the use of a simple shortcode: 'event-map-sc'.  
* Plugin URI: 
* Author: Saurab Adhikari     
* Author URI: http://saurabadhikari.com.np 
* Version:     1.0
* Text Domain: wp-event-map
* Domain Path: /languages
*
* @package  WP Event Map
*
*/
/*
WP Event Map is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
                               
WP Event Map is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with WP Event Map. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/
/*
* Exit if accessed directly.
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'plugins_loaded', 'wpem_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function wpem_load_textdomain() {
  load_plugin_textdomain( 'wp-event-map', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' ); 
}

//Include metaboxes
include('event-meta.php');

// Plugin base url.
define( "EM_BASE_URL", plugin_dir_url( __FILE__ ) );
// Plugin base path.
define( "EM_BASE_PATH", dirname( __FILE__ ) );

/**
* Enqueue styles/scripts in frontend.
*
* @since 1.0.0
*/
function wpem_load_plugin_css() {

    wp_enqueue_style( "em-style", EM_BASE_URL . "/assets/css/em-style.css" );
    wp_enqueue_script("jquery-initialize", EM_BASE_URL . "/assets/js/initialize.js",array('jquery'));    
    wp_enqueue_script( "gmaps", EM_BASE_URL . "/assets/js/gmaps.js",array('jquery') );
    wp_enqueue_script( "media-upload", EM_BASE_URL . "/assets/js/media-upload.js",array('jquery') );
    wp_enqueue_script( "jquery-migrate", EM_BASE_URL . "/assets/js/jquery-migrate.js",array('jquery'));
}
add_action( "wp_enqueue_scripts", "wpem_load_plugin_css" );

/**
* Enqueue styles/scripts in backend.
*
* @since 1.0.0
*/
function wpem_load_admin_css() {
    wp_enqueue_style( "custom-css", EM_BASE_URL . "/assets/css/custom.css" );
    wp_enqueue_script( "jquery-ui-datepicker" );
    wp_enqueue_script( "date-picker", EM_BASE_URL . "/assets/js/date-picker.js",array('jquery') );
    wp_enqueue_style( "jquery-ui-datepicker", EM_BASE_URL . "/assets/css/jquery-ui.css" );  
    wp_enqueue_script( "date-picker-full", EM_BASE_URL . "/assets/js/jquery.datetimepicker.full.js",array('jquery') );
    wp_enqueue_style( "dtpicker-css", EM_BASE_URL . "/assets/css/jquery.datetimepicker.css" );
    wp_enqueue_script( "custom-datetime", EM_BASE_URL . "/assets/js/custom-dt.js",array('jquery') );
}
add_action( "admin_enqueue_scripts", "wpem_load_admin_css" );

/**
* Function retrieves values from database and shows in map and table.
*
* @since 1.0.0
*/
function wpem_custom_event_map(){?>
<div class="entry-content">
    <div class="google-map-wrap" itemscope itemprop="hasMap" itemtype="http://schema.org/Map">
        <div id="google-map" class="google-map">
        </div>
    </div>
    <script>
        jQuery( document ).ready( function($) {
            <?php
            $args=array("post_type" => "event");
            $query1 = new WP_Query( $args );

            // The Loop
            while ( $query1->have_posts() ) {
                $query1->the_post();                
                $feat_image_url = wp_get_attachment_url( get_post_thumbnail_id() );
                $image_attributes = wp_get_attachment_image_src(get_post_meta(get_the_ID(), "_image_id", true ));
                /* Marker #1 */
                $locations[] = array(
                    "google_map" => array(
                        "lat" => get_post_meta( get_the_ID(), "event_label1", true ),
                        "lng" => get_post_meta( get_the_ID(), "event_label2", true ),
                        ),

                    "location_address" => get_post_meta( get_the_ID(), "event_label3", true ),
                    "location_name"    => get_post_meta( get_the_ID(), "event_label4", true ),
                    "event_name"       => get_the_title(),
                    "image"            => $image_attributes[0],
                    "sdate"             => get_post_meta( get_the_ID(), "the_date", true ),
                    "stime"             => get_post_meta( get_the_ID(), "datetimepicker1", true ),
                    "edate"             => get_post_meta( get_the_ID(), "the_date1", true ),
                    "etime"             => get_post_meta( get_the_ID(), "datetimepicker2", true ),
                                                                                                                                                                                                                                                                                                                         

                    );
            }
            /* Set Default Map Area Using First Location */
            $map_area_lat = isset( $locations[0]["google_map"]["lat"] ) ? $locations[0]["google_map"]["lat"]: '';
            $map_area_lng = isset( $locations[0]["google_map"]["lng"] ) ? $locations[0]["google_map"]["lng"]: '';
            ?>
            /* Do not drag on mobile. */
            var is_touch_device = "ontouchstart" in document.documentElement;

            var map = new GMaps({
                el: "#google-map",
                lat: "<?php echo esc_attr($map_area_lat); ?>",
                lng: "<?php echo esc_attr($map_area_lng); ?>",
                scrollwheel: false,
                draggable: ! is_touch_device
            });

            /* Map Bound */
            var bounds = [];

            <?php /* For Each Location Create a Marker. */
            foreach( $locations as $location ){
                $name = $location["location_name"];
                $addr = $location["location_address"];
                $map_lat = $location["google_map"]["lat"];
                $map_lng = $location["google_map"]["lng"];
                $image = $location["image"];
                ?>
                /* Set Bound Marker */
                var latlng = new google.maps.LatLng(<?php echo $map_lat; ?>, <?php echo $map_lng; ?>);
                bounds.push(latlng);
                /* Add Marker */
                map.addMarker({
                    lat: <?php echo esc_attr($map_lat); ?>,
                    lng: <?php echo esc_attr($map_lng); ?>,
                    icon:  "<?php echo esc_url($image); ?>",
                    title: "<?php echo esc_attr($name); ?>",
                    infoWindow: {
                        content: "<p><?php echo esc_attr($name); ?></p>"
                    }
                });
                <?php } //end foreach locations ?>

                /* Fit All Marker to map */
                map.fitLatLngBounds(bounds);

                /* Make Map Responsive */
                var $window = $(window);
                function mapWidth() {
                    var size = $(".google-map-wrap").width();
                    $(".google-map").css({width: size + "px", height: (size/2) + "px"});
                }
                mapWidth();
                $(window).resize(mapWidth);

            });
    </script>
    <?php
     /**
      * Converts seconds to years, days, hours, minutes and seconds.
      *
      * @param      time     Number of seconds to convert.
      * @version    1.0
      * @since      1.0
      */

    function wpem_Sec2Time($time){
        if(is_numeric($time)){
            $value = array(
                "years" => 0, "days" => 0, "hours" => 0,
                "minutes" => 0, "seconds" => 0,
                );
            if($time >= 31556926){
                $value["years"] = floor($time/31556926);
                $time = ($time%31556926);
            }
            if($time >= 86400){
                $value["days"] = floor($time/86400);
                $time = ($time%86400);
            }
            if($time >= 3600){
                $value["hours"] = floor($time/3600);
                $time = ($time%3600);
            }
            if($time >= 60){
                $value["minutes"] = floor($time/60);
                $time = ($time%60);
            }
            $value["seconds"] = floor($time);
            return (array) $value;
        }else{
            return (bool) FALSE;
        }
    }
    ?>
    <div class="map-list">
        <h3><span><?php _e('View Events Description','wp-event-map');?></span></h3>
        <table border='1'>
            <tr><th><?php _e('Event Name:','wp-event-map');?></th><th><?php _e('Address:','wp-event-map');?></th><th><?php _e('Location/Street Details:','wp-event-map');?></th><th><?php _e('Start Date:','wp-event-map');?></th><th><?php _e('End Date:','wp-event-map');?></th><th><?php _e('Event Duration:','wp-event-map');?></th></tr>
            <tbody>
                <?php foreach( $locations as $location ){
                $event = $location["event_name"];
                $name = $location["location_name"];
                $addr = $location["location_address"];
                $map_lat = $location["google_map"]["lat"];
                $map_lng = $location["google_map"]["lng"];
                $sdate = $location["sdate"];
                $edate = $location["edate"];
                $stime = $location["stime"];
                $etime = $location["etime"];

                $the_slug = $event;
                $args = array(
                    'name'        => $the_slug,
                    'post_type'   => 'event',
                    'post_status' => 'publish',
                    'numberposts' => 1
                    );
                    $my_posts = get_posts($args); ?>
                    <tr>
                        <td><a target="_blank" itemprop="url" href="<?php echo esc_url(get_permalink($my_posts[0]->ID));?>"><?php echo $event; ?></a></td>
                        <td><?php echo esc_attr($addr); ?></td>
                        <td><?php echo esc_attr($name); ?></td>
                        <td><?php echo esc_attr($sdate." @".$stime);?></td>
                        <td><?php echo esc_attr($edate." @".$etime);?></td>

                        <?php 
                        $now = strtotime(date("Y/m/d H:i:s"));
                        $start = strtotime($sdate.$stime);
                        $end = strtotime($edate.$etime);
                        $secs = $end - $start; //Event duration. ?>

                        <td>
                        <?php if($secs<0){?>
                            <font color="red"><?php _e('End Date must be greater than Start Date','
                            wp-event-map');?></font>
                            <?php } else { $times = wpem_Sec2Time($secs);
                            echo $times["days"]." days, ".$times["hours"]." hours"; 
                        }?>
                        </td>    
                    </tr>
                <?php } //end foreach ?>
            </tbody>
        </table>
        <?php _e('*Note: All the timings are shown according to the server\'s time which is generally UTC.','wp-event-map');?>
    </div>  
</div>  
<?php }
add_shortcode('event-map-sc','wpem_custom_event_map');