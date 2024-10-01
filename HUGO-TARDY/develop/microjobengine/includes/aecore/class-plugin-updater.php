<?php
if (!class_exists('AE_Plugin_Updater')) {
	/**
	 * Handle updating plugin for engine themes
	 */
	abstract class AE_Plugin_Updater
	{

		public $product_slug, $update_path, $license_key, $product_url = "https://www.enginethemes.com/";
		public $current_version, $update_url;
		public $slug;

		public function __construct()
		{
			if (empty($this->update_path)) {
				$this->update_path = add_query_arg(array(
					'product' => $this->slug,
					'type' => 'plugin',
					'key'	=> $this->license_key,
				), ET_UPDATE_PATH);
			}
			// do_action('qm/info', $this->slug . ' constructed');
			add_filter('et_add_batch_plugin_update', array($this, 'et_add_to_batch_update'));

			// this filter is for showing plugin info when clicking "view details"
			// we don't use it anymore, we replace the link with the product url instead
			// add_filter('plugins_api', array(&$this, 'get_plugin_information'), 10, 3);
		}
		public function et_add_to_batch_update($et_plugins)
		{
			$item = $this->et_get_origin_plugin_data();
			$et_plugins[$this->slug] = $item;
			return $et_plugins;
		}

		public function et_get_origin_plugin_data()
		{
			return array(
				'id'			=> $this->product_slug,
				'plugin'		=> $this->slug,
				'slug'          => $this->product_slug,
				'homepage'		=> $this->product_url,
				'url'			=> $this->product_url,
				'new_version'	=> $this->current_version,
				'tested'		=> '',
				'requires_php'	=> '',
				'package'       => add_query_arg(array('key' => $this->license_key), $this->update_path),
			);
		}

		/**
		 * Add our self-hosted autoupdate plugin to the filter transient
		 * Not used anymore. Moved to theme updater.
		 * Since MjE v1.5, we don't send a separate api call for each extension.
		 * The theme updater will collect all ET plugins initialized with this class, 
		 * then send in batch in one api call to ET update server
		 * @param $transient
		 * @return object $ transient
		 * @deprecated since v1.5
		 */
		public function check_update($transient)
		{
			// get remote version
			$new_update_signal = $this->get_remote_version();

			$item = $this->et_get_origin_plugin_data();

			// if a new version is alvaiable, add the update
			if (null === $this->current_version || version_compare($this->current_version, $new_update_signal->new_version, '<')) {
				$new_item = $this->et_get_update_plugin_transient($item, $new_update_signal);
				$transient->response[$this->product_slug] = (object) $new_item;
			} else {
				$transient->no_update[$this->product_slug] = (object) $item;
			}

			return $transient;
		}

		public function et_get_update_plugin_transient($item, $remote_data)
		{
			// if a new version is alvaiable, add the update
			if (null === $this->current_version || version_compare($this->current_version, $remote_data->new_version, '<')) {
				if (isset($remote_data->new_version)) $item['new_version'] = $remote_data->new_version;
				if (isset($remote_data->tested)) $item['tested'] = $remote_data->tested;
				if (isset($remote_data->update_url)) $item['url'] = $remote_data->update_url;
				if (isset($remote_data->requires_php)) $item['requires_php'] = $remote_data->requires_php;
			}
			return $item;
		}

		protected function get_remote_version()
		{
			// send version request
			$request = wp_remote_post($this->update_path, array(
				'body' => array(
					'action' => 'plugin_version',
					'product' => $this->slug,
					'key' => $this->license_key
				)
			));
			// check request if it is valid
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return unserialize($request['body']);
			}
			return false;
		}

		// This function is not used anymore. We replace them in Theme Updater with a single add_filter.
		public function et_replace_plugin_links($url)
		{
			if (false !== strpos($url, "plugin-information") && false !== strpos($url, $this->slug)) {
				return (isset($this->update_url))
					? $this->update_url : $this->product_url;
			}
			return $url;
		}

		/**
		 * Add our self-hosted description to the filter
		 * We don't use this anymore, instead of getting plugin info, we replace with the product url
		 *
		 * @param boolean $false
		 * @param array $action
		 * @param object $arg
		 * @return bool|object
		 * @deprecated since v1.5
		 */

		public function get_plugin_information($res, $action, $args)
		{

			if ('plugin_information' !== $action) {
				return $res;
			}

			// do nothing if it is not our plugin
			if ($this->product_slug !== $args->slug) {
				return $res;
			}

			$request_args = array(
				'body' => array(
					'action' => $action,
					'plugin' => $this->product_slug,
					'product' => $this->slug,
					'key' => $this->license_key,
					'slug'	=> $this->product_slug,
				)
			);

			$request = wp_remote_post($this->update_path, $request_args);

			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return unserialize($request['body']);
			}
			return $res;
		}
	}
}
