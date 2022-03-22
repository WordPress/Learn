<?php
/**
 * View that shows the course field on the general tab for simple and subscription products.
 *
 * @package sensei-wc-paid-courses
 *
 * @global int    $post_id            The current post ID.
 * @global int[]  $current_course_ids The current course IDs for this post.
 * @global string $product_type       The product type.
 * @global string $field_index        The field index to use in the form.
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View file.

$field_name         = "sensei_course_ids[$field_index][]";
$field_id           = "sensei_course_ids_$field_index";
$additional_classes = [ 'hidden', 'show_if_simple', 'show_if_subscription' ];
$select_width       = '50';

if ( 'variation' === $product_type ) {
	$additional_classes = [ 'form-row' ];
	$select_width       = '100';
}
?>

<div class="options_group <?php echo esc_attr( implode( ' ', $additional_classes ?? [] ) ); ?> ">
	<p class="form-field">
		<label for="<?php echo esc_attr( $field_id ); ?>">
			<?php esc_html_e( 'Courses', 'sensei-pro' ); ?>
		</label>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by function.
		echo wc_help_tip( __( 'Learners who purchase this product, or who previously purchased this product, will be enrolled in the selected courses. Removing a course will unenroll all learners who previously purchased this product.', 'sensei-pro' ) );
		?>
		<select id="<?php echo esc_attr( $field_id ); ?>" class="sensei-wc-paid-courses__course-search wc-product-search" multiple="multiple" style="width: <?php echo esc_attr( $select_width ); ?>%;" name="<?php echo esc_attr( $field_name ); ?>" data-placeholder="<?php esc_attr_e( 'Search for a course&hellip;', 'sensei-pro' ); ?>" data-action="sensei_wc_paid_courses_get_courses" data-exclude="<?php echo intval( $post_id ); ?>">
			<?php
			foreach ( $current_course_ids as $course_id ) {
				$course = get_post( $course_id );
				if ( $course instanceof \WP_Post && 'course' === get_post_type( $course ) ) {
					echo '<option value="' . esc_attr( $course_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $course->post_title ) ) . '</option>';
				}
			}
			?>
		</select>
	</p>
</div>
