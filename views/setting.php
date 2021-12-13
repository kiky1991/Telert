<div class="wrap telert-wrapper">
	<h1 class="wp-heading-inline"><?php esc_html_e('Telert', 'telert'); ?></h1><span><?php esc_html_e(sprintf('v%s', TELERT_VERSION), 'telert'); ?></span>
	<hr class="wp-header-end">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php foreach ( $tabs as $key => $value ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=telert&tab=' . $key ) ); ?>" class="nav-tab<?php echo $tab === $key ? ' nav-tab-active' : ''; ?><?php echo $key == 'logs' ? ' logs' : ''; ?>"><?php echo esc_html( $value['label'] ); ?></a>
		<?php endforeach; ?>
	</nav>
	<div class="telert-setting-content">
		<?php
		if ( isset( $tabs[ $tab ]['callback'] ) ) {
			call_user_func( $tabs[ $tab ]['callback'] );
		}
		?>
	</div>
</div>
