<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

	protected $title = "Perpustakaan";

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
	}

	
	public function index()
	{
		$data['title'] = $this->title;

		if ($this->session->userdata('email')) {
			redirect('dashboard');
		}

		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');

		if ($this->form_validation->run() == false) {
			$this->load->view('auth/login', $data);
		} else {
			// validasinya success
			$this->_login();
		}
	}

	private function _login()
	{
		$email = $this->input->post('email');
		$password = $this->input->post('password');

		$user = $this->db->get_where('users', ['email' => $email])->row_array();

		// jika usernya ada
		if ($user) {

			//cek password
			if (password_verify($password, $user['password'])) {

				$data = [
					'id' => $user['id'],
					'email' => $user['email'],
					'role_id' => $user['role_id']
				];
				$this->session->set_userdata($data);
				redirect('dashboard');
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" 
                    role="alert"> Mohon maaf kata sandi salah </div>');
				redirect('auth');
			}
		} else {
			$this->session->set_flashdata('message', '<div class="alert alert-danger" 
            role="alert"> alamat email tidak terdaftar </div>');
			redirect('auth');
		}
	}

	public function register()
    {
        if ($this->session->userdata('email')) {
            redirect('dashboard');
        }
		$data['title'] = $this->title;
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[users.email]', [
            'is_unique' => 'Email tersebut tersedia'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[5]|matches[password2]', [
            'matches' => 'Katasandi tidak sesuai!',
            'min_length' => 'Katasandi terlalu pendek!'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim');

        if ($this->form_validation->run() == false) {
			$this->load->view('auth/register', $data);
        } else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($this->input->post('email', true)),
                'password' => password_hash($this->input->post("password"), PASSWORD_DEFAULT),
                'role_id' => 2

            ];
            $this->db->insert('users', $data);

            $this->session->set_flashdata('message', '<div class="alert alert-success" 
            role="alert"> Congratulation! your account has been created. Please Login</div>');
            redirect('auth');
        }
    }

	public function logout()
	{
		$this->session->sess_destroy();
		$this->session->set_flashdata('message', '<div class="alert alert-success" 
        role="alert"> You have been logout!</div>');
		redirect('auth');
	}

	public function blocked()
    {
        $this->load->view('auth/blocked');
    }
}
