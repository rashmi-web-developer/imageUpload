<?php
class Mdl_common extends CI_Model
{
/*=================================================================================
	Upload file
	==================================================================================*/

	function upload_file($uploadFile, $filetype, $passed_data,$max_size = '')
	{
		if (!file_exists($passed_data['upload_path'])) {
			mkdir($passed_data['upload_path'], 0777, true);
		}

		$resultArr = array();
		$config['image_library'] = 'gd2';
		if ($max_size != '') {
			$config['max_size'] = '1024000';
		}
		if($filetype == 'img') 	$config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
		if($filetype == 'All') 	$config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|doc|docx|zip|xls';
		if($filetype == 'csv') 	$config['allowed_types'] = 'csv';
		if($filetype == 'swf') 	$config['allowed_types'] = 'swf';
		if($filetype == 'mp3') 	$config['allowed_types'] = 'mp3|wma|wav|.ra|.ram|.rm|.mid|.ogg';
		if($filetype == 'mp4') 	$config['allowed_types'] = 'mp4';
		if($filetype == 'media') 	$config['allowed_types'] = 'gif|jpg|png|jpeg|webp|mp4|mp3|wma|wav';
		if($filetype == 'html') $config['allowed_types'] = 'html|htm';
		$config['maintain_ratio'] = $passed_data['maintain_ratio'];
		$config['upload_path'] = $passed_data['upload_path'];
		if (isset($passed_data['master_dim'])) {
			$config['master_dim'] = $passed_data['master_dim'];
		}
		if (isset($passed_data['height']) && $passed_data['height'] != '') {
			$config['height'] = $passed_data['height'];
		}
		if (isset($passed_data['width']) && $passed_data['width'] != '') {
			$config['width'] = $passed_data['width'];
		}
		if (isset($passed_data['quality']) && $passed_data['quality'] != '') {
			$config['quality'] = $passed_data['quality'];
		}
		$config['file_name']     = $passed_data['file_name'];
		$this->load->library('upload', $config);
		$this->upload->initialize($config);
		if(!$this->upload->do_upload($uploadFile))
		{
			$resultArr['error'] = true;
			$resultArr['msg'] = $this->upload->display_errors();
		}
		else
		{
			$resArr = $this->upload->data();
			if ($filetype =='media' && $resArr['file_type'] == "video/mp4" ) {
				$resultArr['error'] = false;
				$resultArr['msg'] = '';
				$resultArr['is_video'] = '1';
				$resultArr['file_name'] = $resArr['file_name'];
			} else {
				$config['source_image'] = $config['upload_path'].$resArr['file_name'];
				if (isset($config['width']) && $config['width'] != '' && $resArr['image_width'] < $config['width']) {
					$config['width'] = $passed_data['width'];
					$config['height'] = $passed_data['height'];
				}
				$config['quality'] = '20';
				$this->load->library('image_lib', $config);
				$this->image_lib->initialize($config);
				if(!$this->image_lib->resize()){
					$resultArr['error'] = true;
					$resultArr['msg'] = $this->image_lib->display_errors('', '');
					return $resultArr;
				} else {
					$resultArr = $this->_create_thumbs($resArr,$passed_data);
				}
			}
		}
		return $resultArr;
	}

	function _create_thumbs($res,$passed_data) {
		$resultArr = [];
		if (!empty($passed_data['resize_img_config'])) {
			foreach ($passed_data['resize_img_config'] as $item) {
				if (!file_exists($item['new_upload_path'].'/')) {
					mkdir($item['new_upload_path'].'/', 0777, true);
				}
				if (isset($item['width']) && $item['width'] != '' && $res['image_width'] < $item['width']) {
					$item['width'] = $passed_data['width'];
					$item['height'] = $passed_data['height'];
				}
				$resize_config = $item;
				$resize_config['image_library'] = 'gd2';
				$resize_config['source_image'] = $passed_data['upload_path'].$res['file_name'];
				$resize_config['new_image'] = $item['new_upload_path'].'/'.$res['file_name'];
				$this->load->library('image_lib', $resize_config);
				$this->image_lib->initialize($resize_config);
				if(!$this->image_lib->resize()){
					$resultArr['error'] = true;
					$resultArr['msg'] = $this->image_lib->display_errors('', '');
					return $resultArr;
				}
				$this->image_lib->clear();
			}
		} 
		
		$resultArr['error'] = false;
		$resultArr['msg'] = '';
		$resultArr['is_video'] = '0';
		$resultArr['file_name'] = $res['file_name'];
		return $resultArr;
	}

	function copy_image_file_upload($from_file,$passed_data) {
		if (!file_exists($passed_data['upload_path'])) {
			mkdir($passed_data['upload_path'], 0777, true);
		}
		
		if (copy($from_file,$passed_data['upload_path'].$passed_data['file_name'].$passed_data['ext'])) {
			if ($passed_data['mid_upload_path'] != '') {
				if (!file_exists($passed_data['mid_upload_path'].'medium/')) {
					mkdir($passed_data['mid_upload_path'].'medium/', 0777, true);
				}
				if (copy($from_file,$passed_data['mid_upload_path'].'medium/'.$passed_data['file_name'].$passed_data['ext'])) {
					$res_json = '1';
				}
			} else {
				$res_json = '1';
			}
			if ($res_json) {
				$image_info = getimagesize($from_file);
				$res['image_width'] = $image_info[0];
				$res['file_name'] = $passed_data['file_name'].$passed_data['ext'];
				$res['ext'] = $passed_data['ext'];
				$resultArr = $this->_create_thumbs($res,$passed_data);
				return $resultArr;
			} else {
				$resultArr['error'] = true;
				$resultArr['msg'] = "Image not copied from gallery";
			}
		} else {
			$resultArr['error'] = true;
			$resultArr['msg'] = "Image not copied from gallery";
		}
		return $resultArr;

	}

	/*=================================================================================
	download the image
	==================================================================================*/
	function save_image_from_url($url, $output_file)
	{
		$ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $header = curl_exec($ch);
        $img_success = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($img_success == '200') {
            $redir = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $ch1 = curl_init($redir);
            $fp = fopen($output_file, 'wb');
            curl_setopt($ch1, CURLOPT_FILE, $fp);
            curl_setopt ($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch1, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch1, CURLOPT_HEADER, 0);
            curl_exec($ch1);
            curl_close($ch1);
            fclose($fp);
            curl_close($ch);
            return $output_file;
        }
        curl_close($ch);
        return FALSE;
	}
}
?>