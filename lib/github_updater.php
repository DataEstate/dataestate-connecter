<?php 

include_once('Parsedown.php');
class Git_Updater {
	protected $file;
	protected $plugin;
	protected $basename;
	protected $active;

	private $git_username;
	private $repo;
	private $github_response;
	private $parsedown;

	public function __construct($file) {
		$this->file = $file;
		$this->parsedown = new Parsedown();
		add_action('admin_init', [$this, 'set_plugin_properties']);
		return $this;
	}

	public function set_plugin_properties() {
		$this->plugin = get_plugin_data($this->file);
		$this->basename = plugin_basename($this->file);
		$this->active = is_plugin_active($this->basename);
	}
	public function set_username($username) {
		$this->git_username = $username;
	}

	public function set_repository($repository) {
		$this->repo = $repository;
	}

	private function get_repository_info() {
		if (is_null($this->github_response)) {
			$request_url = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->git_username, $this->repo);
			$raw_response =wp_remote_retrieve_body(wp_remote_get($request_url)); 
	
			$response = json_decode($raw_response, true);
			$this->github_response = $response;
		}
	}

	public function init() {
		add_filter('pre_set_site_transient_update_plugins', [$this, 'modify_transient'], 10, 1);
		add_filter('plugins_api', [$this, 'plugin_popup'], 10, 3);
		add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
	}

	public function modify_transient($transient) {
		if (property_exists($transient, 'checked')) { //Check if transient has a checked property
			if ($checked = $transient->checked) {
				$this->get_repository_info();
				$out_of_date = version_compare($this->github_response['tag_name'], $checked[$this->basename], 'gt');
				if ($out_of_date) {
					$new_files = $this->github_response['zipball_url'];
					$slug = current(explode('/', $this->basename));
					$plugin = [
						'url'=>$this->plugin["PluginURI"],
						'slug'=>$slug, 
						'package'=>$new_files,
						'new_version'=>$this->github_response['tag_name']
					];
					$transient->response[$this->basename] = (object)$plugin;
				}
			}
		}
		return $transient;
	}
	public function plugin_popup( $result, $action, $args ) {
		if( ! empty( $args->slug ) ) { // If there is a slug	
			wp_register_style( 'update-style', DEC_URL . '/css/update-style.css' );
			wp_enqueue_style( 'update-style' );
			if( $args->slug == current( explode( '/' , $this->basename ) ) ) { // And it's our slug
				$this->get_repository_info(); // Get our repo info
				$short_description = $this->parsedown->text($this->github_response['body']);
				// Set it to an array
				$plugin = array(
					'name'				=> $this->plugin["Name"],
					'slug'				=> $this->basename,
					'requires'					=> '4',
					'tested'						=> '4.9.8',
					'rating'						=> '0',
					'added'							=> '2016-01-05',
					'version'			=> $this->github_response['tag_name'],
					'author'			=> $this->plugin["AuthorName"],
					'author_profile'	=> $this->plugin["AuthorURI"],
					'last_updated'		=> $this->github_response['published_at'],
					'homepage'			=> $this->plugin["PluginURI"],
					'short_description' => $short_description,
					'sections'			=> array(
						'Description'	=> $this->plugin["Description"],
						'Updates'		=> $short_description,
					),
					'download_link'		=> $this->github_response['zipball_url']
				);

				return (object) $plugin; // Return the data
			}

		}
		return $result; // Otherwise return default
	}
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem; // Get global FS object
		$install_directory = plugin_dir_path( $this->file ); // Our plugin directory
		$wp_filesystem->move( $result['destination'], $install_directory ); // Move files to the plugin dir
		$result['destination'] = $install_directory; // Set the destination for the rest of the stack

		if ( $this->active ) { // If it was active
			activate_plugin( $this->basename ); // Reactivate
		}

		return $result;
	}
}