<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


function randomString($length = 12, $type = 'alpha_numeric')
{
    $str = "";
    if ($type == 'alpha_numeric') {
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
    } else {
        $characters = array_merge(range('A', 'Z'), range('a', 'z'));
    }

    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }
    return $str;
}

/** 
 * Function: get_active_conference
 * Description: Get currently active meeting from the database 
 */
function get_active_conference()
{
    $CI = &get_instance();
    $CI->load->model('common_model');
    date_default_timezone_set('Asia/Kolkata');
    $current_time = date('Y-m-d H:i:s');
    $where = "vc_start_time < '$current_time' AND  vc_end_time > '$current_time'";
    $result = $CI->common_model->getRecordData('video_conference', $where);
    return $result;
}

function zoom_api_url()
{
    return 'https://api.zoom.us/v2';
}

/** 
 * Function: get_zoom_access_token
 * Description: Requests an access token from the Zoom authorization server. 
 * Uses the access token to make API requests or modify resources. 
 * You can find the more detail about the token here: 
 * https://developers.zoom.us/docs/zoom-rooms/s2s-oauth/#generate-access-token
 */
function get_zoom_access_token()
{
    $CI = &get_instance();
    $CI->load->library('Zoom_lib');
    $CI->load->model('common_model');
    $access_token = '';
    $table = 'zoom_access_token';
    date_default_timezone_set('Asia/Kolkata');
    $current_time = date('Y-m-d H:i:s');
    $where = "expiry_time > '$current_time'";
    $valid_token_data = $CI->common_model->getRecordData($table, $where);   // Check for valid token in the database table
    if (!empty($valid_token_data)) {
        $access_token = $valid_token_data[0]['access_token'];
    } else {
        // If the valid access token not in the database table then create new one
        $account_id = $CI->zoom_lib->zoom_account_id();
        $client_id = $CI->zoom_lib->zoom_client_id();
        $client_secret = $CI->zoom_lib->zoom_client_secret();
        $auth_string = base64_encode($client_id . ':' . $client_secret);
        $headers = [
            "authorization: Basic $auth_string",
            "host: zoom.us",
            "content-type: application/json",
        ];
        $requestUrl = 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=' . $account_id;
        $result = curl_with_header($requestUrl, '', $headers);  // send request using curl helper function
        if (!empty($result)) {
            $json_to_array = json_decode($result, true);    // convert result to array
            if (isset($json_to_array['access_token'])) {
                $access_token = $json_to_array['access_token'];
            }
            $expires_in = 0;
            if (isset($json_to_array['expires_in'])) {
                $expires_in = $json_to_array['expires_in'];
            }
            if ($access_token != '' && $expires_in > 0) {
                $expires_in_min = (int)$expires_in / 60;    // convert to minutes
                $expires_in_min = round($expires_in_min);
                date_default_timezone_set('Asia/Kolkata');
                $expiry_time = date('Y-m-d H:i:s', strtotime('+' . $expires_in_min . ' minute'));   // create expiry time
                $data = array(
                    'access_token' => $access_token,
                    'expiry_time' => $expiry_time,
                    'api_response' => $result,
                );

                $rdata = $CI->common_model->insert($data, $table);  // save the access token to the table in the database
            }
        }
    }
    return $access_token;
}
/** 
 * Function: generate_zoom_access_key
 * Description: you can get zoom access key from the Zoom. Pass the ZAK token to the Meeting SDK to start the Zoom user's meeting. 
 * You can find the more detail about the token here: 
 * https://developers.zoom.us/docs/meeting-sdk/auth/#start-meetings-and-webinars-with-a-zoom-users-zak-token

 */
function generate_zoom_access_key($zoom_user_id = '')
{
    $zoom_user_id = ($zoom_user_id) ? $zoom_user_id : 'me';
    $zak_token = '';
    $access_token = get_zoom_access_token();
    $headers = [
        "authorization: Bearer " . $access_token,
        "host: api.zoom.us",
        "content-type: application/json",
    ];
    $requestUrl = zoom_api_url() . "/users/$zoom_user_id/token?type=zak";
    $result = get_curl_with_header($requestUrl, $headers);
    if (!empty($result)) {
        $json_to_array = json_decode($result, true);
        if (isset($json_to_array['token'])) {
            $zak_token = $json_to_array['token'];
        }
    }
    return $zak_token;
}

/** 
 * Function: create_zoom_meeting
 * Description: Create a new zoom meeting using the api.
 * You can find the more detail about the api here: 
 * https://developers.zoom.us/docs/meeting-sdk/apis/#operation/meetingCreate
 */

function create_zoom_meeting($title, $duration, $zoom_user_id = '')
{
    $zoom_user_id = ($zoom_user_id) ? $zoom_user_id : 'me';
    $meetings_data = array();
    $access_token = get_zoom_access_token();
    $headers = [
        "authorization: Bearer " . $access_token,
        "host: api.zoom.us",
        "content-type: application/json",
    ];
    $password = randomString(10);
    $request_body = array(
        'agenda' => $title,
        'duration' => $duration,
        'password' => $password
    );
    $request_body = json_encode($request_body);
    $requestUrl = zoom_api_url() . "/users/$zoom_user_id/meetings";
    $result = curl_with_header($requestUrl, $request_body, $headers);
    if (!empty($result)) {
        $meetings_data = json_decode($result, true);
    }
    return $meetings_data;
}

/** 
 * Function: get_zoom_meeting_list
 * Description: Get list of meetings according to type.
 * You can find the more detail about the api here: 
 * https://developers.zoom.us/docs/meeting-sdk/apis/#operation/meetings
 */

function get_zoom_meeting_list($type = 'scheduled', $zoom_user_id = '')
{
    $zoom_user_id = ($zoom_user_id) ? $zoom_user_id : 'me';
    $meetings_data = array();
    $access_token = get_zoom_access_token();
    $headers = [
        "authorization: Bearer " . $access_token,
        "host: api.zoom.us",
        "content-type: application/json",
    ];
    $requestUrl = zoom_api_url() . "/users/$zoom_user_id/meetings?type=" . $type;
    $result = get_curl_with_header($requestUrl, $headers);
    if (!empty($result)) {
        $meetings_data = json_decode($result, true);
    }
    return $meetings_data;
}

/** 
 * Function: create_zoom_user
 * Description: Add a new user to your Zoom account.
 * You can find the more detail about the api here: 
 * https://developers.zoom.us/docs/api/rest/reference/user/methods/#operation/userCreate
 */
function create_zoom_user($email, $first_name, $last_name, $display_name)
{
    $user_data = array();
    $access_token = get_zoom_access_token();
    $headers = [
        "authorization: Bearer " . $access_token,
        "host: api.zoom.us",
        "content-type: application/json",
    ];

    $request_body = array(
        'action' => 'create',
        'user_info' => array(
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $display_name,
            'type' => 1
        ),
    );
    $request_body = json_encode($request_body);
    $requestUrl = zoom_api_url() . '/users';
    $result = curl_with_header($requestUrl, $request_body, $headers);
    if (!empty($result)) {
        $user_data = json_decode($result, true);
    }
    return $user_data;
}

/** 
 * Function: assign_role
 * Description: User roles can have a set of permissions that allows access only to the pages a user needs to view or edit. Use this API to assign a role to members.
 * You can find the more detail about the api here: 
 * https://developers.zoom.us/docs/api/rest/reference/account/methods/#operation/AddRoleMembers
 */
function assign_role($role_id, $members = array())
{
    $access_token = get_zoom_access_token();
    $headers = [
        "authorization: Bearer " . $access_token,
        "host: api.zoom.us",
        "content-type: application/json",
    ];

    $request_body = array(
        'members' => $members
    );
    $request_body = json_encode($request_body);
    $requestUrl = zoom_api_url() . "/roles/$role_id/members";
    $result = curl_with_header($requestUrl, $request_body, $headers);
    echo $result;
    exit;
}

/** 
 * Function: get_zoom_user_list
 * Description: Retrieve a list your account's users.
 * You can find the more detail about the api here: 
 * https://developers.zoom.us/docs/api/rest/reference/user/methods/#operation/users
 */
function get_zoom_user_list($status = 'active')
{
    $user_data = array();
    $access_token = get_zoom_access_token();
    $headers = [
        "authorization: Bearer " . $access_token,
        "host: api.zoom.us",
        "content-type: application/json",
    ];
    $requestUrl = zoom_api_url() . '/users?status=' . $status;
    $result = get_curl_with_header($requestUrl, $headers);
    if (!empty($result)) {
        $user_data = json_decode($result, true);
    }
    return $user_data;
}

/** 
 * Function: get_zoom_user_role_list
 * Description: List roles on your account
 * You can find the more detail about the api here: 
 * https://developers.zoom.us/docs/api/rest/reference/account/methods/#operation/roles
 */
function get_zoom_user_role_list($type = 'common')
{
    $user_roles = array();
    $access_token = get_zoom_access_token();
    $headers = [
        "authorization: Bearer " . $access_token,
        "host: api.zoom.us",
        "content-type: application/json",
    ];
    $requestUrl = zoom_api_url() . '/roles?type=' . $type;
    $result = get_curl_with_header($requestUrl, $headers);
    if (!empty($result)) {
        $user_roles = json_decode($result, true);
    }
    return $user_roles;
}
