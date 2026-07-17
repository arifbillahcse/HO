<?php
/**
 * Theme Setup Pages Tool.
 *
 * Adds an admin page under Appearance > Setup Pages that auto-creates
 * all required WordPress pages with the correct templates assigned.
 *
 * @package Hosting_Theme
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Setup Pages admin menu.
 */
function hosting_theme_setup_pages_menu() {
	add_theme_page(
		__( 'Setup Pages', 'hosting-theme' ),
		__( 'Setup Pages', 'hosting-theme' ),
		'manage_options',
		'hosting-theme-setup-pages',
		'hosting_theme_setup_pages_render'
	);
}
add_action( 'admin_menu', 'hosting_theme_setup_pages_menu' );

/**
 * Pages to create: slug => [ title, template, description ].
 *
 * Template values must match the "Template Name:" header in the template file.
 * Leave template empty for default page.php.
 */
function hosting_theme_required_pages() {
	return array(
		'web-hosting'    => array(
			'title'       => 'Web Hosting',
			'template'    => 'page-templates/template-pricing.php',
			'description' => 'Hosting packages pricing page',
		),
		'vps-hosting'    => array(
			'title'       => 'VPS Hosting',
			'template'    => 'page-templates/template-vps.php',
			'description' => 'VPS hosting plans page',
		),
		'business-email' => array(
			'title'       => 'Business Email',
			'template'    => 'page-templates/template-business-email.php',
			'description' => 'Business email packages page',
		),
		'offers'         => array(
			'title'       => 'Offers',
			'template'    => 'page-templates/template-offers.php',
			'description' => 'Special promotional offers page',
		),
		'about-us'       => array(
			'title'       => 'About Us',
			'template'    => '',
			'description' => 'About the company',
		),
		'contact'        => array(
			'title'       => 'Contact',
			'template'    => '',
			'description' => 'Contact page',
		),
		'privacy-policy' => array(
			'title'       => 'Privacy Policy',
			'template'    => '',
			'description' => 'Privacy policy page',
		),
		'terms-of-service' => array(
			'title'       => 'Terms of Service',
			'template'    => '',
			'description' => 'Terms and conditions',
		),
	);
}

/**
 * Handle the "Create Pages" form submission.
 */
function hosting_theme_handle_create_pages() {
	if (
		! isset( $_POST['hosting_create_pages_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hosting_create_pages_nonce'] ) ), 'hosting_create_pages' )
	) {
		return array( 'error' => 'Security check failed.' );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return array( 'error' => 'You do not have permission to do this.' );
	}

	$pages   = hosting_theme_required_pages();
	$created = array();
	$skipped = array();

	foreach ( $pages as $slug => $page ) {
		// Check if a page with this slug already exists.
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$skipped[] = $page['title'];
			continue;
		}

		$page_data = array(
			'post_title'   => $page['title'],
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		);

		$page_id = wp_insert_post( $page_data );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			// Assign page template if specified.
			if ( ! empty( $page['template'] ) ) {
				update_post_meta( $page_id, '_wp_page_template', $page['template'] );
			}
			$created[] = array(
				'title' => $page['title'],
				'url'   => get_permalink( $page_id ),
				'id'    => $page_id,
			);
		}
	}

	return array(
		'created' => $created,
		'skipped' => $skipped,
	);
}

/**
 * Render the Setup Pages admin screen.
 */
function hosting_theme_setup_pages_render() {
	$result = null;

	if ( isset( $_POST['hosting_create_pages_nonce'] ) ) {
		$result = hosting_theme_handle_create_pages();
	}

	$pages       = hosting_theme_required_pages();
	$all_pages   = get_pages();
	$page_slugs  = wp_list_pluck( $all_pages, 'post_name' );
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Hostorio — Setup Pages', 'hosting-theme' ); ?></h1>
		<p><?php esc_html_e( 'Click the button below to automatically create all required pages with the correct templates assigned.', 'hosting-theme' ); ?></p>

		<?php if ( $result ) : ?>
			<?php if ( ! empty( $result['error'] ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $result['error'] ); ?></p></div>
			<?php else : ?>
				<?php if ( ! empty( $result['created'] ) ) : ?>
					<div class="notice notice-success">
						<p><strong><?php esc_html_e( 'Pages created successfully:', 'hosting-theme' ); ?></strong></p>
						<ul style="list-style: disc; margin-left: 20px;">
							<?php foreach ( $result['created'] as $p ) : ?>
								<li>
									<strong><?php echo esc_html( $p['title'] ); ?></strong> —
									<a href="<?php echo esc_url( $p['url'] ); ?>" target="_blank"><?php echo esc_url( $p['url'] ); ?></a>
									| <a href="<?php echo esc_url( get_edit_post_link( $p['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'hosting-theme' ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $result['skipped'] ) ) : ?>
					<div class="notice notice-warning">
						<p><strong><?php esc_html_e( 'Skipped (already exist):', 'hosting-theme' ); ?></strong>
						<?php echo esc_html( implode( ', ', $result['skipped'] ) ); ?></p>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>

		<!-- Page Status Table -->
		<h2><?php esc_html_e( 'Required Pages', 'hosting-theme' ); ?></h2>
		<table class="widefat striped" style="max-width: 800px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Page', 'hosting-theme' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'hosting-theme' ); ?></th>
					<th><?php esc_html_e( 'Template', 'hosting-theme' ); ?></th>
					<th><?php esc_html_e( 'Status', 'hosting-theme' ); ?></th>
					<th><?php esc_html_e( 'Link', 'hosting-theme' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $pages as $slug => $page ) :
					$exists     = in_array( $slug, $page_slugs, true );
					$page_obj   = get_page_by_path( $slug );
					$status_label = $exists
						? '<span style="color:green;">&#10003; Exists</span>'
						: '<span style="color:#999;">&#10007; Not created</span>';
					$template_label = $page['template']
						? '<code>' . esc_html( basename( $page['template'], '.php' ) ) . '</code>'
						: '<em>Default</em>';
				?>
					<tr>
						<td><strong><?php echo esc_html( $page['title'] ); ?></strong><br><small><?php echo esc_html( $page['description'] ); ?></small></td>
						<td><code>/<?php echo esc_html( $slug ); ?>/</code></td>
						<td><?php echo $template_label; // phpcs:ignore ?></td>
						<td><?php echo $status_label; // phpcs:ignore ?></td>
						<td>
							<?php if ( $exists && $page_obj ) : ?>
								<a href="<?php echo esc_url( get_permalink( $page_obj->ID ) ); ?>" target="_blank">View</a>
								| <a href="<?php echo esc_url( get_edit_post_link( $page_obj->ID ) ); ?>">Edit</a>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<br>

		<!-- Create Pages Form -->
		<form method="post">
			<?php wp_nonce_field( 'hosting_create_pages', 'hosting_create_pages_nonce' ); ?>
			<input
				type="submit"
				class="button button-primary button-large"
				value="<?php esc_attr_e( 'Create All Missing Pages', 'hosting-theme' ); ?>"
			>
			<p class="description" style="margin-top: 8px;">
				<?php esc_html_e( 'Already existing pages will be skipped — no duplicates will be created.', 'hosting-theme' ); ?>
			</p>
		</form>
	</div>

	<?php
}
