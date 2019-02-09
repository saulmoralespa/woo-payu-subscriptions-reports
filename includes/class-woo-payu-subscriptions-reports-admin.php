<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 8/02/19
 * Time: 12:15 PM
 */

class Woo_Payu_Subscriptions_Reports_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'woo_payu_subscriptions_reports_menu'));
        add_action( 'wp_ajax_woo_payu_subscriptions_reports',array($this,'woo_payu_subscriptions_reports_ajax'));
    }

    public function woo_payu_subscriptions_reports_menu()
    {
        $report = woo_payu_subscriptions_reports()->generateReport;

        add_menu_page(woo_payu_subscriptions_reports()->name, woo_payu_subscriptions_reports()->name, 'manage_options', 'menus' . woo_payu_subscriptions_reports()->nameFormatted(), array($this,'menu' . woo_payu_subscriptions_reports()->nameFormatted()), woo_payu_subscriptions_reports()->assets .'images/favicon.jpg');
        $config = add_submenu_page('menus' . woo_payu_subscriptions_reports()->nameFormatted(), __('Generate report', 'woo-payu-subscriptions-reports'), __('Generate report', 'woo-payu-subscriptions-reports'), 'manage_options', 'config-' . woo_payu_subscriptions_reports()->nameFormatted(), array($report,'content'));
        remove_submenu_page('menus' . woo_payu_subscriptions_reports()->nameFormatted(), 'menus' . woo_payu_subscriptions_reports()->nameFormatted());
        add_action( 'admin_print_scripts-' . $config, array($this, 'footer_menu') );
    }

    public function footer_menu()
    {
        wp_enqueue_script('sweetalert2_woo_payu_subscriptions_reports', woo_payu_subscriptions_reports()->plugin_url."assets/js/sweetalert2.js", array('jquery'), woo_payu_subscriptions_reports()->version, true);
        wp_enqueue_script('woo_payu_subscriptions_reports', woo_payu_subscriptions_reports()->plugin_url."assets/js/admin.js", array('jquery'), woo_payu_subscriptions_reports()->version, true);
        wp_localize_script( 'woo_payu_subscriptions_reports', 'woo_payu_subscriptions_reports', array(
            'msjGenerating' => __('Generating report ...', 'woo-payu-subscriptions-reports'),
            'msjNotRegisters' => __('Report not generated!', 'woo-payu-subscriptions-reports'),
            'msjErrorNotRegisters' => __('The record has not been generated because there is no record for the range of selected dates', 'woo-payu-subscriptions-reports')
        ) );
    }

    public function woo_payu_subscriptions_reports_ajax()
    {

        global $wpdb;

        $post_type = 'shop_subscription';


        $subscription_status =  sanitize_text_field($_POST['subscription_status']);
        $startDate = sanitize_text_field($_POST['initial_date']);
        $endDate = sanitize_text_field($_POST['end_date']);
        $sendEmail = isset($_POST['send_email']) ? true : false;

        update_option('woo_payu_subscriptions_reports_send_email', sanitize_email($_POST['woo_payu_subscriptions_reports_send_email']));


        $query = "SELECT ID FROM $wpdb->posts WHERE post_type = '$post_type' AND post_date BETWEEN '$startDate' AND '$endDate' ORDER BY ID DESC";


        if ($subscription_status === 'active'){
            $query = "SELECT ID FROM $wpdb->posts WHERE post_type = '$post_type' AND post_status = 'wc-active' AND post_date BETWEEN '$startDate' AND '$endDate' ORDER BY ID DESC";
        }elseif ($subscription_status === 'expired'){
            $query = "SELECT ID FROM $wpdb->posts WHERE post_type = '$post_type' AND post_status = 'wc-expired' AND post_date BETWEEN '$startDate' AND '$endDate' ORDER BY ID DESC";
        }elseif ($subscription_status === 'cancelled'){
            $query = "SELECT ID FROM $wpdb->posts WHERE post_type = '$post_type' AND post_status = 'wc-cancelled' AND post_date BETWEEN '$startDate' AND '$endDate' ORDER BY ID DESC";
        }


        $result = $wpdb->get_results(
            $query
        );

        $status = wp_json_encode(array('status' => false));

        if (empty($result))
            wp_die($status);

        $reponse = $this->generateReport($result);

        if ($reponse['status'] && $sendEmail)
            $this->sendReporEmail($reponse['nameFile']);

        wp_die(wp_json_encode($reponse));

        die();

    }

    public function generateReport($data)
    {
        require_once (woo_payu_subscriptions_reports()->lib_path . 'PHPExcel/Classes/PHPExcel.php');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Administrador") // Nombre del autor
        ->setLastModifiedBy(__("Woo payU subscriptions reports"))
        ->setTitle(__("Report subscriptions", 'woo-payu-subscriptions-reports')) // Titulo
        ->setSubject("Report subscriptions") //Asunto
        ->setDescription("Report subscriptions") //Descripción
        ->setKeywords("Report subscriptions") //Etiquetas
        ->setCategory("Report"); //Categorias

        $tituloReporte = "Hoja suscripciones PIXIE S.A.S";

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:B1');


        $titlesColumns = array(
            __('Number', 'woo-payu-subscriptions-reports'),
            __('Name of product', 'woo-payu-subscriptions-reports'),
            __('Quantity', 'woo-payu-subscriptions-reports'),
            __('Client name', 'woo-payu-subscriptions-reports'),
            __('Address', 'woo-payu-subscriptions-reports'),
            __('order note', 'woo-payu-subscriptions-reports')
        );


        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $tituloReporte) // Titulo del reporte
            ->setCellValue('A2',  $titlesColumns[0])  //Titulo de las columnas
            ->setCellValue('B2',  $titlesColumns[1])
            ->setCellValue('C2',  $titlesColumns[2])
            ->setCellValue('D2',  $titlesColumns[3])
            ->setCellValue('E2',  $titlesColumns[4])
            ->setCellValue('F2',  $titlesColumns[5]);

        $number = 1;
        $i = 3;

        foreach ($data as $order){

            $id = --$order->ID;
            $order = wc_get_order($id);

            if ($order === false)
                continue;

            $items = $order->get_items();

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $number)
                ->setCellValue('B'.$i, $this->nameProducts($items))
                ->setCellValue('C'.$i, count($items))
                ->setCellValue('D'.$i, $order->get_billing_first_name() ? $order->get_billing_first_name() . " " . $order->get_billing_last_name() : $order->get_shipping_first_name() . " " . $order->get_shipping_last_name())
                ->setCellValue('E'.$i, $order->get_billing_address_1() ? $order->get_billing_address_1() . " " . $order->get_billing_address_2() : $order->get_shipping_address_1() . " " . $order->get_shipping_address_2())
                ->setCellValue('F'.$i, $order->get_customer_order_notes() ? $order->get_customer_order_notes() : $order->get_customer_note());


            $i++;
            $number++;
        }

        for($i = 'A'; $i <= 'U'; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $objPHPExcel->getActiveSheet()->setTitle(__('Suscripciones', 'woo-payu-subscriptions-reports'));
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,3);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $dir = $this->pathUploadReport();
        $dirUrl = $this->urlUploadReport();

        if(!is_dir($dir)){
            woo_payu_subscriptions_reports()->createDirUploads($dir);
        }

        $nameFile = $this->getName();

        $pathSave = $dir . $nameFile;
        $objWriter->save($pathSave);


        $statusCreate = array('status' => false);

        if (file_exists($pathSave)){
            $statusCreate = array('status' => true, 'url' => $dirUrl . $nameFile, 'nameFile' => $nameFile);
        }

        return $statusCreate;

    }

    public function nameProducts($items)
    {
        $namesProducts = array();

        foreach ($items as $item){
            $namesProducts[] = $item->get_name();
        }

       $names = implode(",",  $namesProducts);
       return $names;
    }


    public function getName()
    {
        $nameNow = "Reporte_suscripciones_" . time() . '.xls';
        return $nameNow;
    }

    public function pathUploadReport()
    {
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/payu-suscriptions-reports/';

        return $dir;
    }

    public function urlUploadReport()
    {
        $upload_dir = wp_upload_dir();
        $dirUrl = $upload_dir['baseurl'] . '/payu-suscriptions-reports/';

        return $dirUrl;
    }

    public function sendReporEmail($nameReport)
    {
        $headers = array();

        $emailSend = get_option('woo_payu_subscriptions_reports_send_email');

        $multiple_recipients = array(
            $emailSend
        );

        $attachments = array( $this->pathUploadReport() . $nameReport );

        $suject = __("Report of subscriptions generated", 'woo-payu-subscriptions-reports');

        $message = __("The report is attached", 'woo-payu-subscriptions-reports');

        $receiver = wp_mail( $multiple_recipients, $suject, $message, $headers,  $attachments );

        if (!$receiver)
            woo_payu_subscriptions_reports()->logger->add(woo_payu_subscriptions_reports()->nameFormatted(), 'The email has not been sent');
    }

}