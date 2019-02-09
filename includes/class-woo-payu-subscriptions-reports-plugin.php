<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 8/02/19
 * Time: 11:31 AM
 */

class Woo_Payu_Subscriptions_Reports_Plugin
{
    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $lib_path;
    /**
     * @var bool
     */
    private $_bootstrapped = false;
    /**
     * @var string
     */
    public $assets;

    public $logger;

    public function __construct($file, $version, $name)
    {
        $this->file = $file;
        $this->version = $version;
        $this->name = $name;

        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->lib_path = $this->plugin_path . trailingslashit( 'lib' );
        $this->assets = $this->plugin_url . trailingslashit( 'assets');
        $this->logger = new WC_Logger();


        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
    }

    public function run_woo_payu_reports()
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( __( 'Custom create design can only be called once', 'woo-payu-subscriptions-reports'));
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action(
                    'admin_notices',
                    function() use($e) {
                        woo_payu_subscriptions_reports_notices( 'Custom Creative Design: ' . $e->getMessage());
                    }
                );
            }
        }
    }

    protected function _run()
    {
        $this->_load_handlers();
    }

    protected function _load_handlers()
    {
        require_once ($this->includes_path . 'class-woo-payu-subscriptions-reports-admin.php');
        require_once ($this->includes_path . 'class-woo-payu-subscriptions-reports-admin-generate-report.php');

        $this->admin = new Woo_Payu_Subscriptions_Reports_Admin();
        $this->generateReport = new Woo_Payu_Subscriptions_Reports_Admin_Generate_Report();
    }

    public function plugin_action_links($links)
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="'.admin_url( 'admin.php?page=config-woopayusubscriptionsreports').'">' . esc_html__( 'Settings', 'woo-payu-subscriptions-reports' ) . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function nameFormatted($domain = false)
    {
        $name = ($domain) ? str_replace(' ', '-', $this->name)  : str_replace(' ', '', $this->name);
        return strtolower($name);
    }

    public function createDirUploads($dir)
    {
        mkdir($dir,0755);
    }
}