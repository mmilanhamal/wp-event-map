<?php
/**
 * Register a event post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
add_action( 'init', 'map_event_init' );

function map_event_init() {
    $labels = array(
        'name'               => _x( 'Events', 'post type general name', 'wp-event-map' ),
        'singular_name'      => _x( 'Event', 'post type singular name', 'wp-event-map' ),
        'menu_name'          => _x( 'Events', 'admin menu', 'wp-event-map' ),
        'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'wp-event-map' ),
        'add_new'            => _x( 'Add New', 'event', 'wp-event-map' ),
        'add_new_item'       => __( 'Add New Event', 'wp-event-map' ),
        'new_item'           => __( 'New Event', 'wp-event-map' ),
        'edit_item'          => __( 'Edit Event', 'wp-event-map' ),
        'view_item'          => __( 'View Event', 'wp-event-map' ),
        'all_items'          => __( 'All Events', 'wp-event-map' ),
        'search_items'       => __( 'Search Events', 'wp-event-map' ),
        'parent_item_colon'  => __( 'Parent Events:', 'wp-event-map' ),
        'not_found'          => __( 'No events found.', 'wp-event-map' ),
        'not_found_in_trash' => __( 'No events found in Trash.', 'wp-event-map' )
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Description.', 'wp-event-map' ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'menu_icon'           => 'dashicons-calendar-alt',
        'rewrite'            => array( 'slug' => 'event' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'author', 'excerpt' )
    );

    register_post_type( 'event', $args );
}
/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function event_add_meta_box() {

    $screens = array( 'event' );

    foreach ( $screens as $screen ) {

        add_meta_box(
            'event_header_section',
            __( 'Location Info', 'event' ),
            'event_meta_box_callback',
            $screen
        );
    }
}
add_action( 'add_meta_boxes', 'event_add_meta_box' );

/**
 * Prints the box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
function event_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'event_save_meta_box_data', 'event_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $Label1 = get_post_meta( $post->ID, 'event_label1', true );
    $Label2 = get_post_meta( $post->ID, 'event_label2', true );
    $Label3 = get_post_meta( $post->ID, 'event_label3', true );
    $Label4 = get_post_meta( $post->ID, 'event_label4', true );

    echo '<label for="event_label1">';
    _e( 'Latitude:', 'wp-event-map' );
    echo '</label> ';
    echo '<input type="text" id="event_label1" name="event_label1" value="' . esc_attr( $Label1 ) . '" size="25"
    /><br><br><hr>';

    echo '<label for="event_label2">';
    _e( 'Longitude:', 'wp-event-map' );
    echo '</label> ';
    echo '<input type="text" id="event_label2" name="event_label2" value="' . esc_attr( $Label2 ) . '"
    size="25" /><br><br><hr>';

    echo '<label for="event_label3">';
    _e( 'Address:', 'wp-event-map' );
    echo '</label> ';
    echo '<input type="text" id="event_label3" name="event_label3" value="' . esc_attr( $Label3 ) . '"
    size="25" /><br><br><hr>';

    echo '<label for="event_label4">';
    _e( 'Street/Location Details:', 'wp-event-map' );
    echo '</label> ';
    echo '<input type="text" id="event_label4" name="event_label4" value="' . esc_attr( $Label4 ) . '"
    size="25"
    />';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function event_save_meta_box_data( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['event_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['event_meta_box_nonce'], 'event_save_meta_box_data' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Make sure that it is set.
    if ( ! isset( $_POST['event_label1'] ) && !isset($_POST['event_label2']) && !isset($_POST['event_label3']) && !isset($_POST['event_label4'])) {
        return;
    }

    // Sanitize user input.
    $Label1 = sanitize_text_field( $_POST['event_label1'] );
    $Label2 = sanitize_text_field( $_POST['event_label2'] );
    $Label3 = sanitize_text_field( $_POST['event_label3'] );
    $Label4 = sanitize_text_field( $_POST['event_label4'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'event_label1', $Label1 );
    update_post_meta( $post_id, 'event_label2', $Label2 );
    update_post_meta( $post_id, 'event_label3', $Label3 );
    update_post_meta( $post_id, 'event_label4', $Label4 );

}
add_action( 'save_post', 'event_save_meta_box_data' );


class Custom_Post_Type_Image_Upload {
    
    
    public function __construct() {
        
        //add_action( 'init', array( $this, 'init' ) );
        
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }
    }    
    
    /**
     * Initialize the admin, adding actions to properly display and handle 
     * the event custom post type add/edit page
     */
    public function admin_init() {
        global $pagenow;
        
        if ( $pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php' ) {
            
            add_action( 'add_meta_boxes', array( &$this, 'meta_boxes' ) );
            add_filter( 'enter_title_here', array( &$this, 'enter_title_here' ), 1, 2 );
            
            add_action( 'save_post', array( &$this, 'meta_boxes_save' ), 1, 2 );
        }
    }
    
    
    /**
     * Save meta boxes
     * 
     * Runs when a post is saved and does an action which the write panel save scripts can hook into.
     */
    public function meta_boxes_save( $post_id, $post ) {
        if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( is_int( wp_is_post_revision( $post ) ) ) return;
        if ( is_int( wp_is_post_autosave( $post ) ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( $post->post_type != 'event' ) return;
            
        $this->process_event_meta( $post_id, $post );
    }
    
    
    /**
     * Function for processing and storing all event data.
     */
    private function process_event_meta( $post_id, $post ) {
        update_post_meta( $post_id, '_image_id', $_POST['upload_image_id'] );
    }
    
    
    /**
     * Set a more appropriate placeholder text for the New event title field
     */
    public function enter_title_here( $text, $post ) {
        if ( $post->post_type == 'event' ) return __( 'event Title' );
        return $text;
    }
    
    
    /**
     * Add and remove meta boxes from the edit page
     */
    public function meta_boxes() {
        add_meta_box( 'event-image', __( 'Event Icon to show in Map' ), array( &$this, 'event_image_meta_box' ), 'event', 'normal', 'high' );
    }
    
    
    /**
     * Display the image meta box
     */
    public function event_image_meta_box() {
        global $post;
        
        $image_src = '';
        
        $image_id = get_post_meta( $post->ID, '_image_id', true );
        $image_src = wp_get_attachment_url( $image_id );
        
        ?>
        <img id="event_image" src="<?php echo $image_src ?>" style="max-width:100%;" />
        <input type="hidden" name="upload_image_id" id="upload_image_id" value="<?php echo $image_id; ?>" />
        <p>
            <a title="<?php esc_attr_e( 'Set event image' ) ?>" href="#" id="set-event-image"><?php _e( 'Set event image' ) ?></a>
            <a title="<?php esc_attr_e( 'Remove event image' ) ?>" href="#" id="remove-event-image" style="<?php echo ( ! $image_id ? 'display:none;' : '' ); ?>"><?php _e( 'Remove event image' ) ?></a>
        </p>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            
            // save the send_to_editor handler function
            window.send_to_editor_default = window.send_to_editor;
    
            $('#set-event-image').click(function(){
                
                // replace the default send_to_editor handler function with our own
                window.send_to_editor = window.attach_image;
                tb_show('', 'media-upload.php?post_id=<?php echo $post->ID ?>&amp;type=image&amp;TB_iframe=true');
                
                return false;
            });
            
            $('#remove-event-image').click(function() {
                
                $('#upload_image_id').val('');
                $('img').attr('src', '');
                $(this).hide();
                
                return false;
            });
            
            // handler function which is invoked after the user selects an image from the gallery popup.
            // this function displays the image and sets the id so it can be persisted to the post meta
            window.attach_image = function(html) {
                
                // turn the returned image html into a hidden image element so we can easily pull the relevant attributes we need
                $('body').append('<div id="temp_image">' + html + '</div>');
                    
                var img = $('#temp_image').find('img');
                
                imgurl   = img.attr('src');
                imgclass = img.attr('class');
                imgid    = parseInt(imgclass.replace(/\D/g, ''), 10);
    
                $('#upload_image_id').val(imgid);
                $('#remove-event-image').show();
    
                $('img#event_image').attr('src', imgurl);
                try{tb_remove();}catch(e){};
                $('#temp_image').remove();
                
                // restore the send_to_editor handler function
                window.send_to_editor = window.send_to_editor_default;
                
            }
    
        });
        </script>
        <?php
    }
}

// finally instantiate our plugin class and add it to the set of globals
$GLOBALS['custom_post_type_image_upload'] = new Custom_Post_Type_Image_Upload();


class WordPress_jQuery_Date_Picker {
    
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
    /**
     * Initializes the plugin by setting localization, filters, and administration functions.
     *
     * @version     1.0
     * @since       1.0
     */
     public function __construct() {

        // Register the date meta box
        add_action( 'add_meta_boxes', array( $this, 'wpem_add_date_meta_box' ) );
        add_action( 'save_post', array( $this, 'wpem_save_the_date' ) );        
        // Display the date in the post
        add_action( 'the_content', array( $this, 'wpem_prepend_the_date' ) );
         
     } // end __construct

     /**
      * Registers the meta box for displaying the 'Date' option in the post editor.
      *
      * @version    1.0
      * @since      1.0
      */
     public function wpem_add_date_meta_box() {
         add_meta_box(
            'the_date',
            __( 'Event Date and Time', 'wp-wp-event-map' ),
            array( $this, 'wpem_the_date_display' ),
            'event',
            'side',
            'low'
         );
     } // end add_date_meta_box
     
     /**
      * Renders the user interface for completing the project in its associated meta box.
      *
      * @version    1.0
      * @since      1.0
      */
     public function wpem_the_date_display( $post ) {
    
         wp_nonce_field( plugin_basename( __FILE__ ), 'wp-jquery-date-picker-nonce' );
    
         echo __('Starting Date:','wp-wp-event-map').'<input type="text" id="datepicker" name="the_date" value="' . get_post_meta( $post->ID, 'the_date', true ) . '" />'.'<br><hr>';
         echo __('Starting Time:','wp-wp-event-map').'<input type="text" id="datetimepicker1" name="datetimepicker1" value="' . get_post_meta( $post->ID, 'datetimepicker1', true ) . '"/><br><hr>';
         echo __('Ending Date:','wp-wp-event-map').'<br>'.'<input type="text" id="datepicker1" name="the_date1" value="' . get_post_meta( $post->ID, 'the_date1', true ) . '" /><br><hr>';         
         echo __('Ending Time:','wp-wp-event-map').'<br>'.'<input type="text" id="datetimepicker2" name="datetimepicker2" value="' . get_post_meta( $post->ID, 'datetimepicker2', true ) . '"/><br><br>';

    
     } // end the_date_display
     
     /**
      * Saves the project completion data for the incoming post ID.
      *
      * @param      int     The current Post ID.
      * @version    1.0
      * @since      1.0
      */
     public function wpem_save_the_date( $post_id ) {
         
         // If the user has permission to save the meta data...
         if( $this->wpem_user_can_save( $post_id, 'wp-jquery-date-picker-nonce' ) ) { 
         
            // Delete any existing meta data for the owner
            if( get_post_meta( $post_id, 'the_date' ) ) {
                delete_post_meta( $post_id, 'the_date' );
            } // end if
            update_post_meta( $post_id, 'the_date', strip_tags( $_POST[ 'the_date' ] ) );

             if( get_post_meta( $post_id, 'the_date1' ) ) {
                delete_post_meta( $post_id, 'the_date1' );
            } // end if
            update_post_meta( $post_id, 'the_date1', strip_tags( $_POST[ 'the_date1' ] ) );
            
             if( get_post_meta( $post_id, 'datetimepicker1' ) ) {
                delete_post_meta( $post_id, 'datetimepicker1' );
            } // end if
            update_post_meta( $post_id, 'datetimepicker1', strip_tags( $_POST[ 'datetimepicker1' ] ) );
            
             if( get_post_meta( $post_id, 'datetimepicker2' ) ) {
                delete_post_meta( $post_id, 'datetimepicker2' );
            } // end if
            update_post_meta( $post_id, 'datetimepicker2', strip_tags( $_POST[ 'datetimepicker2' ] ) );
             
         } // end if
         
     } // end save_the_date 
     
     /**
      * Shows timings in single event page.
      *
      * @param      content     Appends timings to content.
      * @return     content     Appended timing.
      * @version    1.0
      * @since      1.0
      */ 
      public function wpem_prepend_the_date( $content ) {
        if( 0 != ( $the_date = get_post_meta( get_the_ID(), 'datetimepicker2', true ) ) ) {
            $content =  '<br>' .sprintf('<b>%s</b>',__('Event will be finished at: ','wp-event-map')).$the_date . $content.'</p>';
        }  
        if( 0 != ( $the_date = get_post_meta( get_the_ID(), 'the_date1', true ) ) ) {
            $content = '<br>' . sprintf('<b>%s</b>',__('Event will last till: ','wp-event-map')).$the_date . $content;
        } 
        if( 0 != ( $the_date = get_post_meta( get_the_ID(), 'datetimepicker1', true ) ) ) {
            $content = '<br>' .sprintf('<b>%s</b>',__('Starting Time: ','wp-event-map')).$the_date . $content;
        }
        if( 0 != ( $the_date = get_post_meta( get_the_ID(), 'the_date', true ) ) ) {
            $content = '<p>' .sprintf('<b>%s</b>',__('Event starts on: ','wp-event-map')) .$the_date . $content;
        }      
        return $content;
     
    } // end prepend_the_date
     
    /*---------------------------------------------*
     * Helper Functions
     *---------------------------------------------*/
     
     /**
      * Determines whether or not the current user has the ability to save meta data associated with this post.
      *
      * @param      int     $post_id    The ID of the post being save
      * @param      bool                Whether or not the user has the ability to save this post.
      * @version    1.0
      * @since      1.0
      */
     private function wpem_user_can_save( $post_id, $nonce ) {
        
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) ) ? true : false;
        
        // Return true if the user is able to save; otherwise, false.
        return ! ( $is_autosave || $is_revision) && $is_valid_nonce;
     } // end user_can_save
     
} // end class
new WordPress_jQuery_Date_Picker();