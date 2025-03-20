<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Missingreport extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (staff_cant('view', 'missing_report')) {
            access_denied('missing report');
        }
    }

    public function report()
    {
        $this->load->view('admin/reports/missing_report');
    }
}