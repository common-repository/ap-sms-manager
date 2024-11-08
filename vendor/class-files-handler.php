<?php
/**
 * This file was automatically generated by automattic/jetpack-autoloader.
 *
 * @package automattic/jetpack-autoloader
 */

namespace Automattic\Jetpack\Autoloader\jp574d64515cbc27960117035bf1acde50;

 // phpcs:ignore

/**
 * This class selects the package versions for the package files.
 */
class Files_Handler {

	/**
	 * The Plugins_Handler object.
	 *
	 * @var Plugins_Handler
	 */
	private $plugins_handler = null;

	/**
	 * The Version_Selector object.
	 *
	 * @var Version_Selector
	 */
	private $version_selector = null;

	/**
	 * The constructor.
	 *
	 * @param Plugins_Handler  $plugins_handler The Plugins_Handler object.
	 * @param Version_Selector $version_selector The Version_Selector object.
	 */
	public function __construct( $plugins_handler, $version_selector ) {
		$this->plugins_handler  = $plugins_handler;
		$this->version_selector = $version_selector;
	}

	/**
	 * Adds the version of a package file to the $jetpack_packages_filemap global
	 * array so that we can load the most recent version.
	 *
	 * @param string $file_identifier Unique id to file assigned by composer based on package name and filename.
	 * @param string $version Version of the file.
	 * @param string $path Absolute path to the file so that we can load it.
	 */
	public function enqueue_package_file( $file_identifier, $version, $path ) {
		global $jetpack_packages_filemap;

		if ( isset( $jetpack_packages_filemap[ $file_identifier ]['version'] ) ) {
			$selected_version = $jetpack_packages_filemap[ $file_identifier ]['version'];
		} else {
			$selected_version = null;
		}

		if ( $this->version_selector->is_version_update_required( $selected_version, $version ) ) {
			$jetpack_packages_filemap[ $file_identifier ] = array(
				'version' => $version,
				'path'    => $path,
			);
		}
	}

	/**
	 * Creates a path to the plugin's filemap. The filemap filename is the filename
	 * generated by Jetpack Autoloader version >= 2.0.
	 *
	 * @param String $plugin_path The plugin path.
	 *
	 * @return String The filemap path
	 */
	public function create_filemap_path( $plugin_path ) {
		return trailingslashit( $plugin_path ) . 'vendor/composer/jetpack_autoload_filemap.php';
	}

	/**
	 *  Initializes the filemap.
	 */
	public function set_file_paths() {
		$active_plugin_paths = $this->plugins_handler->get_all_active_plugins_paths();
		$filemap_paths       = array_map( array( $this, 'create_filemap_path' ), $active_plugin_paths );

		foreach ( $filemap_paths as $path ) {
			if ( is_readable( $path ) ) {
				$file_map = require $path;

				if ( is_array( $file_map ) ) {
					foreach ( $file_map as $file_identifier => $file_data ) {
						$this->enqueue_package_file( $file_identifier, $file_data['version'], $file_data['path'] );
					}
				}
			}
		}
	}

	/**
	 * Include latest version of all enqueued files.
	 */
	public function file_loader() {
		global $jetpack_packages_filemap;
		foreach ( $jetpack_packages_filemap as $file_identifier => $file_data ) {
			if ( empty( $GLOBALS['__composer_autoload_files'][ $file_identifier ] ) ) {
				require_once $file_data['path'];

				$GLOBALS['__composer_autoload_files'][ $file_identifier ] = true;
			}
		}
	}
}
