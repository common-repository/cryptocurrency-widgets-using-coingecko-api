<?php 

    add_action( 'widgets_init', 'ccwca_register_widget' );
    // Register the widget.
    function ccwca_register_widget() { 
      register_widget( 'ccwca_Widget' );
    }


  class ccwca_Widget extends WP_Widget {
      // Set up the widget name and description.
      public function __construct() {
        $widget_options = array( 'classname' => 'ccwca_widget', 'description' => 'Cryptocurrency Widgets by Cool Plugins' );
        parent::__construct( 'ccwca_widget', 'Crypto Widget', $widget_options );
      }

    // Create the admin area widget settings form.
    public function form( $instance ) {
      $ccwca_shortcode_id = ! empty( $instance['ccwca_shortcode'] ) ? $instance['ccwca_shortcode'] : ''; 
    $title = ! empty( $instance['title'] ) ? $instance['title'] : ''; 
      ?>
  <p>
    <span class="imp-note" style="color:red">Important Note : Use widget shortcode according to available width</br></span>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
    <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
  </p>

      <p>
        <label for="<?php echo $this->get_field_id( 'ccwca_shortcode' ); ?>"> Shortcode:</label>
         <select style="width:70%" id="<?php echo $this->get_field_id( 'ccwca_shortcode' ); ?>" name="<?php echo $this->get_field_name( 'ccwca_shortcode' ); ?>" >
          
    <?php    
        global $post;
       $args = array( 'numberposts' => -1, 'post_type' => 'ccwca');
         $postlist = get_posts($args);
         if($postlist){
        foreach ( $postlist as $post ) : setup_postdata( $post ); 
          $p_id=get_the_id();

          if($ccwca_shortcode_id==$p_id){
            echo'<option selected="selected" value="'.$p_id.'">[ccwca id="'.$p_id.'"]'.'</option>';
          }else{
            echo'<option value="'.$p_id.'">[ccwca id="'.$p_id.'"]'.'</option>';
          }
     
          endforeach; 
        }else{
           echo'<option value="">Shortcode Not Found</option>';
        }
          ?>
       
        </select>
      </p><?php
    }


    // Apply settings to the widget instance.
    public function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance[ 'ccwca_shortcode' ] = strip_tags( $new_instance[ 'ccwca_shortcode' ] );
       $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
      return $instance;
    }

    // Create the widget output.
    public function widget( $args, $instance ) {
      $ccwca_shortcode=$instance['ccwca_shortcode'];
      $title = apply_filters('widget_title',$instance[ 'title' ] );

     echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];

     if(isset($ccwca_shortcode) && !empty($ccwca_shortcode)){
         echo do_shortcode('[ccwca id="'.$ccwca_shortcode.'"]');
       }

       echo $args['after_widget'];
    }

    

}

