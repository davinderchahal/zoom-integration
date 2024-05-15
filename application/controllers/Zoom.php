<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Author: Davinder Singh Chahal <imdschahal@gmail.com>
 * Date: May 2023
 * Class: Zoom
 * Description: Zoom meeting creation and starting
 */
class Zoom extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('common_model');
	}

	public function index()
	{
		redirect('zoom/video_conference');
	}

	public function video_conference()
	{
		$user_name = 'TestUser';	//zoom meeting host or participant name
		$user_email = 'testuser@example.com'; //zoom meeting host or participant name
		$duration = 0; //zoom meeting duration in minutes
		$zoom_user_id = ''; //you can get from zoom admin account 
		$client_id = $meeting_id = $meeting_password = $signature = $zak_token = '';

		/* Get if there is active zoom meeting form database which is you created*/
		$active_conference = get_active_conference();
		if (!empty($active_conference)) {
			$meetings = $active_conference[0];
			$meeting_id = $meetings['vc_room_id'];
			$meeting_password = $meetings['vc_room_password'];
			$duration = $meetings['vc_duration'];
		}
		if ($meeting_id != '' && $duration > 0) {
			$host = 1;	//set the host to 1 for host and 0 for participants
			if ($host == 1) {
				$zak_token = generate_zoom_access_key($zoom_user_id);  //find the more detail about the token in the zoom official documentation
			}
			$this->load->library('Zoom_lib');
			$client_id = $this->zoom_lib->zoom_sdk_client_id();	//required for start and join meeting
			$signature = $this->zoom_lib->generate_signature($meeting_id, $host, $duration); //required for start and join meeting
		}
		//pass the data to the view
		$data['client_id'] = $client_id;
		$data['signature'] = $signature;
		$data['meeting_id'] = $meeting_id;
		$data['meeting_password'] = $meeting_password;
		$data['zak_token'] = $zak_token;
		$data['host'] = $host;
		$data['user_name'] = $user_name;
		$data['user_email'] = $user_email;
		$this->load->view('video_conference', $data);
	}

	function create_room()
	{
		$this->form_validation->set_rules('title', 'Title', 'trim|required|alpha_numeric_spaces|max_length[100]');
		$this->form_validation->set_rules('duration', 'Duration', 'trim|required|integer');

		if ($this->form_validation->run() == false) {
			$errors = validation_errors();
			$this->session->set_userdata('form-fail', $errors);
			redirect('zoom/video_conference');
		} else {
			$title = $this->input->post('title');
			$duration = $this->input->post('duration');
			$zoom_user_id = ''; //you can get from zoom admin account 
			$meeting_data = create_zoom_meeting($title, $duration, $zoom_user_id); //create zoom meeting using zoom api
			//check data return by zoom api is not empty and save it in database
			if (!empty($meeting_data)) {
				$room_id = $meeting_data['id'];
				$host_id = $meeting_data['host_email'];
				$password = $meeting_data['encrypted_password'];
				date_default_timezone_set('Asia/Kolkata');	// set default timezone. You can configure timezone in config file for whole application
				$start_time = date('Y-m-d H:i:s');
				$end_time = date('Y-m-d H:i:s', strtotime('+' . $duration . ' minute'));	//creating meeting expiery time using duration to check later whether meeting is active or not

				$data = array(
					'vc_host_id' => $host_id,
					'vc_room_id' => $room_id,
					'vc_room_password' => $password,
					'vc_title' => $title,
					'vc_duration' => $duration,
					'vc_start_time' => $start_time,
					'vc_end_time' => $end_time,
					'api_response' => json_encode($meeting_data),
				);
				$table = 'video_conference';
				$result = $this->common_model->insert($data, $table);	//save meeting data into table
				if ((int)$result > 0) {
					$this->session->set_userdata('form-success', 'Meeting Successfully Added');
				} else {
					$this->session->set_userdata('form-fail', 'Something Went Wrong! Please Try Again');
				}
			}
			sleep(2);	//sleep for 2 seconds to ensure meeting status is activated
			redirect('zoom/video_conference');
		}
	}
}//End Class
