<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 8/02/19
 * Time: 12:28 PM
 */

class Woo_Payu_Subscriptions_Reports_Admin_Generate_Report
{

    public function content()
    {
        ?>
        <div class="wrap about-wrap">
            <form id="woo-payu-subscriptions-reports">
                <table>
                    <tbody>
                        <!--<tr>
                            <th><?php /*_e("Weekly report", 'woo-payu-subscriptions-reports'); */?></th>
                            <td>
                                <input type="checkbox" id="weekly_report" name="weekly_report">
                            </td>
                        </tr>-->
                        <tr>
                            <th><?php _e("Initial date", 'woo-payu-subscriptions-reports'); ?></th>
                            <td>
                                <input type="date" id="initial_date" name="initial_date" required>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("End date", 'woo-payu-subscriptions-reports'); ?></th>
                            <td>
                                <input type="date" id="end_date" name="end_date" required>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Subscription status", 'woo-payu-subscriptions-reports'); ?></th>
                            <td>
                                <select name="subscription_status" required>
                                    <option value="default" selected><?php _e('All', 'woo-payu-subscriptions-reports'); ?></option>
                                    <option value="active"><?php _e('Active', 'woo-payu-subscriptions-reports'); ?></option>
                                    <option value="expired"><?php _e('Expired', 'woo-payu-subscriptions-reports'); ?></option>
                                    <option value="cancelled"><?php _e('	Cancelled', 'woo-payu-subscriptions-reports'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Email", 'woo-payu-subscriptions-reports'); ?></th>
                            <td>
                                <input type="email" id="woo_payu_subscriptions_reports_send_email" name="woo_payu_subscriptions_reports_send_email" value="<?php echo get_option('woo_payu_subscriptions_reports_send_email'); ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e("Send for email", 'woo-payu-subscriptions-reports'); ?></th>
                            <td>
                                <input type="checkbox" id="send_email" name="send_email">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button(__('Generate report', 'woo-payu-subscriptions-reports')); ?>
            </form>
        </div>
        <?php
    }
}