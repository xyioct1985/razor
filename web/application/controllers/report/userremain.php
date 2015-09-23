<?php

/**
 * Cobub Razor
 *
 * An open source mobile analytics system
 *
 * PHP versions 5
 *
 * @category  MobileAnalytics
 * @package   CobubRazor
 * @author    Cobub Team <open.cobub@gmail.com>
 * @copyright 2011-2016 NanJing Western Bridge Co.,Ltd.
 * @license   http://www.cobub.com/docs/en:razor:license GPL Version 3
 * @link      http://www.cobub.com
 * @since     Version 0.1
 */

/**
 * Userremain Controller
 *
 * @category PHP
 * @package  Controller
 * @author   Cobub Team <open.cobub@gmail.com>
 * @license  http://www.cobub.com/docs/en:razor:license GPL Version 3
 * @link     http://www.cobub.com
 */
class Userremain extends CI_Controller
{
    private $data = array();

    /**
     * Construct funciton, to pre-load database configuration
     *
     * @return void
     */
    function __construct()
    {
        parent::__construct();
        $this->load->Model('common');
        $this->load->model('product/userremainmodel', 'userremain');
        $this->common->requireLogin();
        $this->load->library('export');
        $this->load->model('event/userevent', 'userevent');
        $this->load->model('channelmodel', 'channel');
        $this->common->checkCompareProduct();
    }

    /**
     * Index function , load view userremainview
     *
     * @return void
     */
    function index()
    {
        if (isset($_GET['type']) && $_GET['type'] == 'compare') {
            $this->common->loadCompareHeader(lang('m_rpt_userRetention'));
            $this->load->view('compare/userremain');
            return;
        }
        $this->common->requireProduct();
        $this->common->loadHeaderWithDateControl();
        $this->load->view('usage/userremainview', $this->data);
    }

    /**
     * Adduserremainreport function , load report userremain
     *
     *@param string $delete delete
     *@param string $type   type
     *
     * @return void
     */
    function adduserremainreport($delete = null, $type = null)
    {
        if (isset($_GET['type']) && $_GET['type'] == 'compare') {
            $products = $this->common->getCompareProducts();
            if (count($products) == 0) {
                echo 'redirecthome';
                return;
            }
            $this->load->view('layout/reportheader');
            $this->data['show_version'] = 0;
            $this->load->view('widgets/userremain', $this->data);
        } else {
            $productId = $this->common->getCurrentProduct();
            $this->common->requireProduct();
            $productId = $productId->id;
            $procuctversion = $this->userevent->getProductVersions($productId);
            $product_channels = $this->channel->getChannelList($productId);
            
            if ($procuctversion != null && $procuctversion->num_rows > 0) {
                $this->data['productversion'] = $procuctversion;
            }
            $this->data['product_channels'] = $product_channels;
            if ($delete == null) {
                $this->data['add'] = "add";
            }
            if ($delete == "del") {
                $this->data['delete'] = "delete";
            }
            if ($type != null) {
                $this->data['type'] = $type;
            }
            $this->load->view('layout/reportheader');
            $this->load->view('widgets/userremain', $this->data);
        }
    }

    /**
     * GetUserRemainweekMonthData function 
     *
     *@param string $version version
     *@param string $channel channel
     *
     * @return encode json
     */
    function getUserRemainweekMonthData($version = 'all', $channel = 'all')
    {
        $channel = urldecode($channel);
        $data = array();
        $productId = $this->common->getCurrentProduct();
        $from = $this->common->getFromTime();
        $to = $this->common->getToTime();
        $products = $this->common->getCompareProducts();
        if (count($products) >= 2) {
            for ($i = 0; $i < count($products); $i ++) {
                $data['userremainday'][$i]['data'] = $this->userremain->getUserRemainCountByDay($version, $products[$i]->id, $from, $to, $channel)->result_array();
                $data['userremainday'][$i]['name'] = $products[$i]->name;
                $data['userremainweek'][$i]['data'] = $this->userremain->getUserRemainCountByWeek($version, $products[$i]->id, $from, $to, $channel)->result_array();
                $data['userremainweek'][$i]['name'] = $products[$i]->name;
                $data['userremainmonth'][$i]['data'] = $this->userremain->getUserRemainCountByMonth($version, $products[$i]->id, $from, $to, $channel)->result_array();
                $data['userremainmonth'][$i]['name'] = $products[$i]->name;
            }
            echo json_encode($data);
        } else if (! empty($productId)) {
                $productId = $productId->id;
                $this->common->requireProduct();
                $userremain_d = $this->userremain->getUserRemainCountByDay($version, $productId, $from, $to, $channel);
                $userremain_w = $this->userremain->getUserRemainCountByWeek($version, $productId, $from, $to, $channel);
                $userremain_m = $this->userremain->getUserRemainCountByMonth($version, $productId, $from, $to, $channel);
                $data['userremainday'] = $userremain_d->result();
                $data['userremainweek'] = $userremain_w->result();
                $data['userremainmonth'] = $userremain_m->result();
                echo json_encode($data);
        } else {
                echo json_encode($data);
        }
    }
}
