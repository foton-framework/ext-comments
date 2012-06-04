<?php



class EXT_Comments extends SYS_Model_Database
{
	//--------------------------------------------------------------------------
	
	public $captcha_protection = TRUE;  // Защита от спама каптчей
	public $code_protection    = FALSE; // Защита от спама скрытым кодом
	public $js_validation      = FALSE; // Проверка формы до отправления
	public $allow_guest        = FALSE; // Гости могут добавлять комментарий
	public $auto_registration  = FALSE; // Автоматически регистрировать пользователей оставивших комментарий
	public $post_delay         = 180;   // 3 min
	
	//--------------------------------------------------------------------------
	
	public $table  = 'comments';
	public $type   = '';
	public $rel_id = 0;
	
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		parent::__construct();
		
		sys::set_config_items(&$this, 'comments');
	}
	
	//--------------------------------------------------------------------------
	
	public function init()
	{
		$this->fields['comments'] = array(
			'id'   => array(),
			'uid'  => array(
				'default' => $this->user->id
			),
			'type' => array(
				'default' => $this->type
			),
			'rel_id' => array(
				'default' => $this->rel_id
			),
			'status' => array(
				'label'   => 'Статус',
				'default' => 1,
				'field'   => 'select',
				'options' => 'status_list',
				'user_group' => array(1)
			),
			'message' => array(
				'label' => 'Сообщение',
				'field' => 'textarea',
				'rules' => 'trim|strip_tags|required|min_length[5]',
			),
			'postdate' => array(
				'label'   => 'Дата публикации',
				'default' => time(),
			),
			'ip' => array(
				'default' => ip2long($_SERVER['REMOTE_ADDR'])
			),
			/*'username' => array(
				'label' => 'Имя (для гостя)',
				'field' => 'input',
				'rules' => 'trim|strip_tags|length[3,25]',
			),
			'useremail' => array(
				'label' => 'E-mail (для гостя)',
				'field' => 'input',
				'rules' => 'trim|valid_email|max_length[100]',
			),*/
			'ratio' => NULL
		);
	}
	
	//--------------------------------------------------------------------------
	
	public function check_permissions()
	{
		if ($this->user->id) return TRUE;
		
		if ($this->allow_guest) return TRUE;
		$this->template->error('К сожалению Вы не можете добавить комментарий! :-(');
		
		return FALSE;
	}
	
	//--------------------------------------------------------------------------
	
	public function init_form()
	{
		parent::init_form();
		
		if ( ! $this->user->id)
		{
			if ($this->captcha_protection_enable())
			{
				$this->load->extension('captcha')->init();
			}
			
			if ($this->code_protection_enable())
			{
				$this->load->extension('code_protection')->init();
			}
		}
		
		if ($this->js_validation)
		{
			$this->load->extension('js_validation')->init_form(&$this->form);
		}
		
		if ($this->user->group_id == 1) return;
		
		// post_delay blocking
		if ($this->post_delay)
		{
			$time = time() - $this->post_delay;
			if ($this->user->id)
			{
				$this->db->where('uid=?', $this->user->id);
			}
			else
			{
				$this->db->where('ip=?', ip2long($_SERVER['REMOTE_ADDR']));
			}
			$this->db->select('postdate')->where('postdate>?', $time);
			$row = $this->db->get($this->table())->row();
			
			if ($row)
			{
				$time = h_date::time_elapsed_string($time, $row->postdate);
				$this->form->set_error('', "Оставить новый комментарий вы сможете только через {$time}");
			}
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function code_protection_enable()
	{
		if ($this->user->group_id) return FALSE;
		if ( ! $this->code_protection) return FALSE;
		return TRUE;
	}
	
	//--------------------------------------------------------------------------
	
	public function captcha_protection_enable()
	{
		if ($this->user->group_id) return FALSE;
		if ( ! $this->captcha_protection) return FALSE;
		return TRUE;
	}
	
	//--------------------------------------------------------------------------
	
	public function prepare_row_result(&$row)
	{
		$row = parent::prepare_row_result($row);
		
		//if ($row->postdate) $row->postdate = date('d.m.Y - H:i', $row->postdate);
		
		$row->status_name = $this->status_list($row->status);
		$row->message     = nl2br($row->message);
		
		if (isset($row->group_id)) $this->user->model->prepare_row_result(&$row);

		$row->profile_url = h_url::url("users/{$row->uid}");
		
		return $row;
	}
	
	//--------------------------------------------------------------------------
	
	public function status_list($val = NULL)
	{
		static $list = array(
			0 => 'Отключен',
			1 => 'Включен'
		);
		
		if ($val !== NULL) return $list[$val];
		
		return $list;
	}
	
	//--------------------------------------------------------------------------
	
	public function get()
	{
		$this->db->order_by('comments.postdate DESC');
		
		if ($this->user->group_id != 1)
		{
			$this->db->where('comments.status=1');
			//$this->db->where('users.status=1');
		}
		
		if ($this->type)   $this->db->where('comments.type = ?'  , $this->type);
		if ($this->rel_id) $this->db->where('comments.rel_id = ?', $this->rel_id);
		
		$this->db->select('users.*, comments.*');
		$this->db->join('users', 'users.id = comments.uid', 'left');
//		$this->db->join('comments', 'comments.rel_id = topics.id');
		
		return parent::get();
	}
	
	//--------------------------------------------------------------------------
	
	public function insert()
	{
		$_POST['type']   = $this->type;
		$_POST['rel_id'] = $this->rel_id;
		
		return parent::insert();
	}
	
	//--------------------------------------------------------------------------
	
	public function delete($table=NULL,$where=NULL)
	{
		$res = $this->db->select('comments.id, uid')->where($where)->get('comments')->result();
		$com_id = array();
		foreach ($res as $row)
		{
			$com_id[] = $row->id;
		}
		
		return parent::delete($table, 'comments.id IN (' . implode(',', $com_id) . ')');
	}
	
	//--------------------------------------------------------------------------
	
	public function get_count($type, $rel_id)
	{
		static $resutl;
		$key = $type . $rel_id;
		if (isset($resutl[$key])) return $resutl[$key];
		
		$resutl[$key] = $this->db->select('COUNT(*) AS x')
			->where('type = ? AND rel_id = ?', $type, $rel_id)
			->get($this->table)->row()->x;
			
		return $resutl[$key];
	}
	
	//--------------------------------------------------------------------------
	
	public function is_manager($uid = NULL)
	{
		if ( ! $uid)
		{
			$uid = sys::$ext->user->id;
		}
		
		if ( ! $uid) return FALSE;
		
		return sys::$ext->user->group_id == 1 || in_array($uid, $this->manager_ids);
	}
	
	//--------------------------------------------------------------------------
}