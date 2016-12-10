<?php
$student_id = (int) $_GET['student_id'];
$student = get_userdata( $student_id );
$avatar = get_avatar( $student->user_email, 92 );
$enrolled_courses = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );
$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );
?>
<div class="wrap coursepress_wrapper course-student-profile">
	<h2><?php esc_html_e( 'Student Profile', 'cp' ); ?></h2>

	<table class="widefat striped">
		<tr>
			<td rowspan="3" width="5%"><?php echo $avatar; ?></td>
			<td width="15%"><?php esc_html_e( 'Student ID', 'cp' ); ?></td>
			<td><?php echo $student_id; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'First Name', 'cp' ); ?></td>
			<td><?php echo $student->first_name; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Last Name', 'cp' ); ?></td>
			<td><?php echo $student->last_name; ?></td>
		</tr>
	</table>

	<h3><?php esc_html_e( 'Enrolled Courses', 'cp' ); ?></h3>
	<?php $this->enrolled_courses->display(); ?>
</div>