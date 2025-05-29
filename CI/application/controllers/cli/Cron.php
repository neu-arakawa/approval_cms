<?php

class Cron extends CI_Controller
{
    public function __construct()
    {
        if (!is_cli()) {
            exit;
        }
        ini_set('max_execution_time', 0);
        parent::__construct();
    }


    public function per_minute()
    {
        $this->load->model('admin/admin_staff_tmp_model');
        $this->admin_staff_tmp_model->update_by_tmp();

        $this->load->model('admin/admin_doctor_tmp_model');
        $this->admin_doctor_tmp_model->update_by_tmp();

        $this->load->model('admin/admin_department_tmp_model');
        $this->admin_department_tmp_model->update_by_tmp();
    }


    public function test()
    {
    }
}
