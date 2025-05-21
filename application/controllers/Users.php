<?php
// phpinfo();
// exit;
defined('BASEPATH') OR exit('No direct script access allowed');
#[\AllowDynamicProperties]
class Users extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Users_model');
        $this->load->helper('url');
        header('Content-Type: application/json');
    }

    public function index() {
        if ($this->input->method() !== 'get') {
            echo json_encode(['status' => false, 'message' => 'Invalid HTTP method. Use GET method.']);
            return;
        }
        $this->load->helper('url');
        $query = $this->db->get('users');
        $result = $query->result();

        if(!empty($result)){
            echo json_encode($result);
        }else {
            echo json_encode(["status"=> false, "messgae"=> "No Users Found!"]);
        }
        return;
    }

    public function create() {
        if ($this->input->method() !== 'post') {
            echo json_encode(['status' => false, 'message' => 'Invalid HTTP method. Use POST method.']);
            return;
        }
        $this->load->library('upload');
        $base_url = base_url();
        $query = $this->db->get('users');

        $config['upload_path'] = FCPATH . 'uploads/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size']      = 2048;
        $config['encrypt_name']  = TRUE;

        $this->upload->initialize($config);

        $name  = $this->input->post('name');
        $email = $this->input->post('email');

        $image_name = $base_url . 'uploads/' . 'Default.jpg';
        if (!empty($_FILES['image']['name'])) {
            if(count($_FILES['image']['name']) != 1){
                echo json_encode(['status' => false, 'message' => "Multiple images not allowed!"]);
                return;
            }else {
                $_FILES['single_image']['name']     = $_FILES['image']['name'][0];
                $_FILES['single_image']['type']     = $_FILES['image']['type'][0];
                $_FILES['single_image']['tmp_name'] = $_FILES['image']['tmp_name'][0];
                $_FILES['single_image']['error']    = $_FILES['image']['error'][0];
                $_FILES['single_image']['size']     = $_FILES['image']['size'][0];
            }            
            if (!$this->upload->do_upload('single_image')) {
                echo json_encode(['status' => false, 'message' => $this->upload->display_errors()]);
                return;
            } else {
                $uploaded_data = $this->upload->data();
                $image_name = $base_url . 'uploads/' . $uploaded_data['file_name'];
            }
        }

        $user_data = [
            'name'  => $name,
            'email' => $email,
            'image' => $image_name,
        ];

        // echo json_encode(['status' => false, 'message' => $user_data]);
        // exit;

        $insert_id = $this->Users_model->create_user($user_data);

        if ($insert_id) {
            echo json_encode(['status' => true, 'message' => 'User created', 'user_id' => $insert_id]);
        } else {
            $db_error = $this->db->error();
            echo json_encode(['status' => false,'message' => $db_error['message']]);
        }
        return;
    }

    public function get_user($id){
        if ($this->input->method() !== 'get') {
            echo json_encode(['status' => false, 'message' => 'Invalid HTTP method. Use GET method.']);
            return;
        }
        $user = $this->Users_model->get_user_by_id($id);
        if (!empty($user)) {
            echo json_encode(['status' => true, 'user' => $user]);
        } else {
            echo json_encode(['status' => false, 'message' => 'User Not Found with id ' . $id]);
        }
        return;
    }

    public function update($id) {
        if ($this->input->method() !== 'post') {
            echo json_encode(['status' => false, 'message' => 'Invalid HTTP method. Use POST method.']);
            return;
        }

        $this->load->helper('url');
        $this->load->library('upload');
        $base_url = base_url();

        $user = $this->Users_model->get_user_by_id($id);
        
        if (empty($user)) {
            echo json_encode(['status' => false, 'message' => 'User not found']);
            return;
        }

        $name  = !empty($this->input->post('name')) ? $this->input->post('name') : $user->name;
        $email =  !empty($this->input->post('email')) ? $this->input->post('email') : $user->email;
        $image_name = $user->image;

        if (!empty($_FILES['image']['name'])) {
            if(count($_FILES['image']['name']) != 1){
                echo json_encode(['status' => false, 'message' => "Multiple images not allowed!"]);
                return;
            }else {
                $_FILES['single_image']['name']     = $_FILES['image']['name'][0];
                $_FILES['single_image']['type']     = $_FILES['image']['type'][0];
                $_FILES['single_image']['tmp_name'] = $_FILES['image']['tmp_name'][0];
                $_FILES['single_image']['error']    = $_FILES['image']['error'][0];
                $_FILES['single_image']['size']     = $_FILES['image']['size'][0];
            }            
            if (!$this->upload->do_upload('single_image')) {
                echo json_encode(['status' => false, 'message' => $this->upload->display_errors()]);
                return;
            } else {
                $uploaded_data = $this->upload->data();
                $image_name =  $base_url . 'uploads/' . $uploaded_data['file_name'];
            }
        }

        $update_data = [
            'name'  => $name,
            'email' => $email,
            'image' => $image_name,
        ];

        // echo json_encode($update_data);
        // exit;

        $updated = $this->Users_model->update_user($id, $update_data);
        // echo $updated;
        // exit;

        if ($updated) {
            echo json_encode(['status' => true, 'message' => 'User updated successfully']);
        } else {
            $db_error = $this->db->error();
            echo json_encode(['status' => false,'message' => $db_error['message']]);        
        }
        return;
    }

    public function delete($id) {
         if ($this->input->method() !== 'delete') {
            echo json_encode(['status' => false, 'message' => 'Invalid HTTP method. Use DELETE method.']);
            return;
        }
        $user = $this->Users_model->get_user_by_id($id);
        if (!$user) {
            echo json_encode(['status' => false, 'message' => 'User not found']);
            return;
        }
        $result = $this->Users_model->delete_user($id);
        echo json_encode([
            'status' => $result,
            'message' => $result ? 'Deleted user of id ' . $id : 'Failed to delete user'
        ]);
        return;
    }
}
