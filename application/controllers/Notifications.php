<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notifications extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('misc_model');
    }

    public function data() {
        $user_id = get_staff_user_id();
        $is_logged_in = is_staff_logged_in();
        $total_unread_notifications = 0;

        if ($is_logged_in && $user_id) {
            $this->db->where('touserid', $user_id);
            $this->db->where('isread', 0);
            $total_unread_notifications = $this->db->count_all_results('tblnotifications');
        }

    	$notifications = [];
    	foreach ($this->misc_model->get_user_notifications() as $notification) {
        	$profileImageUrl = '';
        
			$additional_data = '';
            if (!empty($notification['additional_data'])) {
                $additional_data = unserialize($notification['additional_data']);

                $i = 0;
                foreach ($additional_data as $data) {
                    if (strpos($data, '<lang>') !== false) {
                        $lang = get_string_between($data, '<lang>', '</lang>');
                        $temp = _l($lang);
                        if (strpos($temp, 'project_status_') !== false) {
                            $status = get_project_status_by_id(strafter($temp, 'project_status_'));
                            $temp   = $status['name'];
                        }
                        $additional_data[$i] = $temp;
                    }
                    $i++;
                }
            }
    
			$description = _l($notification['description'], $additional_data);
            if (($notification['fromcompany'] == null && $notification['fromuserid'] != 0)
            || ($notification['fromcompany'] == null && $notification['fromclientid'] != 0)) {
                if ($notification['fromuserid'] != 0) {
                    $description = e($notification['from_fullname']) . ' - ' . $description;
                } else {
                    $description = e($notification['from_fullname']) . ' - ' . $description . '<br /><span class="label inline-block mtop5 label-info">' . _l('is_customer_indicator') . '</span>';
                }
            }
    
        	if (($notification['fromcompany'] == null && $notification['fromuserid'] != 0) || ($notification['fromcompany'] == null && $notification['fromclientid'] != 0)) {
                if ($notification['fromuserid'] != 0) {
                    $image = $this->staff_profile_image($notification['fromuserid'], ['staff-profile-image-small', 'img-circle notification-image', 'pull-left']);
                } else {
                    $image = '<img src="' . e($this->contact_profile_image_url($notification['fromclientid'])) . '" class="client-profile-image-small img-circle pull-left notification-image">';
                }
            }
        
			$notifications[] = [
				'id' => $notification['id'],
				'link' => empty($notification['link']) ? '#' : admin_url($notification['link']),
				'isread_inline' => $notification['isread_inline'],
				'profileImage' => $image,
				'description' => $description,
				'date' => $notification['date'],
				'timeAgo' => time_ago($notification['date'])
			];
		}
    
        $data = [
            'count' => $total_unread_notifications,
            'notifications' => $notifications
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    public  function contact_profile_image_url($contact_id, $type = 'small') {
        $url  = base_url('assets/images/user-placeholder.jpg');

        if (!$path) {
            $this->db->select('profile_image');
            $this->db->from(db_prefix() . 'contacts');
            $this->db->where('id', $contact_id);
            $contact = $this->db->get()->row();

            if ($contact && !empty($contact->profile_image)) {
                $path = 'uploads/client_profile_images/' . $contact_id . '/' . $type . '_' . $contact->profile_image;
            }
        }

        if ($path && file_exists($path)) {
            $url = base_url($path);
        }

        return $url;
    }

	public function staff_profile_image($id, $classes = ['staff-profile-image'], $type = 'small', $img_attrs = [])
    {
        $url = base_url('assets/images/user-placeholder.jpg');
        $id = trim($id);

        $_attributes = '';
        foreach ($img_attrs as $key => $val) {
            $_attributes .= $key . '=' . '"' . e($val) . '" ';
        }

        $blankImageFormatted = '<img src="' . $url . '" ' . $_attributes . ' class="' . implode(' ', $classes) . '" />';

        if ((string) $id === (string) get_staff_user_id() && isset($GLOBALS['current_user'])) {
            $result = $GLOBALS['current_user'];
        } else {
            $this->db->select('profile_image, firstname, lastname');
            $this->db->where('staffid', $id);
            $result = $this->db->get(db_prefix() . 'staff')->row();
        }

        if (!$result) {
            return $blankImageFormatted;
        }

        if ($result->profile_image !== null) {
            $profileImagePath = 'uploads/staff_profile_images/' . $id . '/' . $type . '_' . $result->profile_image;
            if (file_exists($profileImagePath)) {
                return '<img ' . $_attributes . ' src="' . base_url($profileImagePath) . '" class="' . implode(' ', $classes) . '" />';
            }
        }

        return $blankImageFormatted;
    }

}