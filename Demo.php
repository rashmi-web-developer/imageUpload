<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Demo extends CI_Controller
{
    /**
     * Updates constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mdl_common');
    }

    function image_upload()
    {
        if ( $this->input->server('REQUEST_METHOD') == 'POST' )
        {
            $upload_path = $_POST['upload_path'];
            $passed_data['upload_path'] = $upload_path;
            $passed_data['maintain_ratio'] = TRUE;
            $passed_data['quality'] = '50';
            $passed_data['file_name'] = 'pf_'.time();
            $passed_data['resize_img_config'] = Array(
                Array(
                    'maintain_ratio' => TRUE,
                    'width' => 1024,
                    'height' => 1024,
                    'folder' => 'medium',
                    'new_upload_path' => $upload_path.'medium',
                ),
                Array(
                    'maintain_ratio' => TRUE,
                    'height' => 400,
                    'width' => 400,
                    'folder' => 'small',
                    'new_upload_path' => $upload_path.'small',
                ),
            );

            //call 
            $json_res =  $this->mdl_common->upload_file('update_image','media',$passed_data);
            echo json_encode($result);
            exit;
        }
    }

    function copy_image() {
        if ( $this->input->server('REQUEST_METHOD') == 'POST' )
        {
            $upload_path = $_POST['upload_path'];
            $file_name = $_POST['file_name'];
            $passed_data['upload_path'] = $upload_path;
            $passed_data['maintain_ratio'] = TRUE;
            $passed_data['quality'] = '50';
            $passed_data['file_name'] = 'pf_'.time();
            $passed_data['ext'] = '.' . pathinfo($file_name, PATHINFO_EXTENSION);
            $passed_data['resize_img_config'] = Array(
                Array(
                    'maintain_ratio' => TRUE,
                    'width' => 1024,
                    'height' => 1024,
                    'folder' => 'medium',
                    'new_upload_path' => $upload_path.'medium',
                ),
                Array(
                    'maintain_ratio' => TRUE,
                    'height' => 400,
                    'width' => 400,
                    'folder' => 'small',
                    'new_upload_path' => $upload_path.'small',
                ),
            );
            $resArr = $this->mdl_common->copy_image_cover_upload($file_name, $passed_data);
            echo json_encode($result);
            exit;
        }
    }

    function download_image() {
        if ( $this->input->server('REQUEST_METHOD') == 'POST' )
        {
            $url = $_POST['url'];
            $resArr = $this->mdl_common->save_image_from_url($url, './image/img1.png');
            echo json_encode($result);
            exit;
        }
    }
    
}
