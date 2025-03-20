<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Leads extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('leads_model'); // Adjust the model name according to your setup
    }

    public function count_get() {
        $total_leads = $this->leads_model->get_total_leads_count(); // Assuming you have this method in your model
        $this->response([
            'status' => TRUE,
            'total_leads' => $total_leads
        ], REST_Controller::HTTP_OK);
    }
}
