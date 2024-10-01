<?php

/**
 * Plugin updater for EngineThemes
 */
class ET_Update
{

    public $current_version, $product_slug, $update_path, $product_url, $license_key;
    public $et_plugins = array();

    /**
     * Initialize a new instance of the Engine Theme Auto-Update class
     *
     * @param string $current_version
     * @param string $update_path
     * @param string $plugin_slug
     */
    function __construct($current_version, $update_path, $product_slug, $product_url = '')
    {
        $this->current_version = $current_version;
        $this->update_path = $update_path;
        $this->product_slug = $product_slug;
        $this->product_url = $product_url;
        $this->license_key = get_option('et_license_key');

        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_themes', array(&$this, 'et_check_theme_update'));

        // check for new updates in batch for all related extensions to this theme
        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'et_batch_update_plugins'));

        // replace the "view details" link of new version with the product url
        add_filter('admin_url', array(&$this, 'et_replace_plugin_links'), 10, 1);
    }

    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     * @param $transient
     * @return object $ transient
     */
    public function et_check_theme_update($transient)
    {
        // get remote version
        $remote_version = $this->get_remote_theme_version();
        if (is_string($remote_version)) {
            $item = array(
                'theme'        => $this->product_slug,
                'new_version'  => $this->current_version,
                'url'          => $this->product_url,
                'package'      => add_query_arg(array(
                    'key' => $this->license_key,
                    'type' => 'theme'
                ), $this->update_path),
                'requires'     => '',
                'requires_php' => '',
            );

            // if a new version is alvaiable, add the update
            if (version_compare($this->current_version, $remote_version, '<')) {
                $item['new_version'] = $remote_version;
                $transient->response[$this->product_slug] = $item;
            } else {
                $transient->no_update[$this->product_slug] = $item;
            }
        }
        return $transient;
    }

    /**
     * Return the remote update data for theme
     * @return string $remote_version
     */
    public function get_remote_theme_version()
    {
        return $this->et_send_update_request($this->update_path, 'version', $this->product_slug);
    }

    public function et_batch_update_check()
    {
        $plugin_slugs = array_keys($this->et_plugins);
        if (!empty($plugin_slugs)) {
            return $this->et_send_update_request($this->update_path, "batch_update_check", $plugin_slugs);
        } else {
            return array();
        }
    }

    public function et_send_update_request($url, $action, $product)
    {
        $res = wp_remote_post($url, array(
            'body' => array(
                'action' => $action,
                'product' => $product,
                'key' => $this->license_key,
                'site' => site_url()
            )
        ));

        if (!is_wp_error($res) || wp_remote_retrieve_response_code($res) === 200) {
            return maybe_unserialize(preg_replace('/[\x00-\x1F\x80-\xFF]/', "", $res['body']));
        } else {
            return false;
        }
    }

    public function et_batch_update_plugins($transient)
    {
        $this->et_plugins = apply_filters('et_add_batch_plugin_update', $this->et_plugins);
        // do_action('qm/debug', array_keys($this->et_plugins));

        $remote_update_data = $this->et_batch_update_check();

        if (!empty($remote_update_data)) {
            foreach ($remote_update_data as $plugin_slug => $remote_data) {
                $current_item = $this->et_plugins[$plugin_slug];

                // if a new version is alvaiable, add the update
                if (
                    null === $current_item['new_version']
                    || version_compare($current_item['new_version'], $remote_data['new_version'], '<')
                ) {
                    $current_item = $this->et_get_update_plugin_transient($current_item, $remote_data);
                    $transient->response[$current_item['slug']] = (object) $current_item;
                } else {
                    $transient->no_update[$current_item['slug']] = (object) $current_item;
                }
            }
        }
        return $transient;
    }

    public function et_replace_plugin_links($url)
    {
        if (false !== strpos($url, "plugin-information")) {
            foreach ($this->et_plugins as $plugin_slug => $plugin_data) {
                if (
                    false !== strpos($url, $plugin_slug)
                    || false !== strpos($url, $plugin_data['slug'])
                ) {
                    return (isset($this->et_plugins[$plugin_slug]['url']))
                        ? $this->et_plugins[$plugin_slug]['url']
                        : $this->et_plugins[$plugin_slug]['homepage'];
                }
            }
        }
        return $url;
    }



    public function et_get_update_plugin_transient($item = array(), $remote_data = array())
    {
        // if a new version is alvaiable, add the update
        if (null === $item['new_version'] || version_compare($item['new_version'], $remote_data['new_version'], '<')) {
            if (isset($remote_data['new_version'])) $item['new_version'] = $remote_data['new_version'];
            if (isset($remote_data['tested'])) $item['tested'] = $remote_data['tested'];
            if (isset($remote_data['update_url'])) $item['url'] = $remote_data['update_url'];
            if (isset($remote_data['requires_php'])) $item['requires_php'] = $remote_data['requires_php'];
        }
        return $item;
    }
}

// initialize theme update
add_action('admin_init', 'et_check_update');
function et_check_update()
{
    /**
     * check theme name defined or not
     */
    if (!defined('THEME_NAME')) return false;

    // install themes updater
    $update_path = ET_UPDATE_PATH . '&product=' . THEME_NAME . '&type=theme';
    new ET_Update(ET_VERSION, $update_path, THEME_NAME, 'https://www.enginethemes.com/themes/' . THEME_NAME);
}
