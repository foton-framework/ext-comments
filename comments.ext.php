<?php



class EXT_Comments extends SYS_Model_Database
{
	//--------------------------------------------------------------------------
	
	public $per_page           = 50;    // Кол-во комментариев на страницу
	public $tree_mode          = TRUE;  // Включить древовидный режим. (кол-во уровней+1)
	public $mailing_author     = TRUE;  // Оповещать автора коммента об ответе
	public $captcha_protection = TRUE;  // Защита от спама каптчей
	public $code_protection    = FALSE; // Защита от спама скрытым кодом
	public $js_validation      = FALSE; // Проверка формы до отправления
	public $allow_guest        = FALSE; // Гости могут добавлять комментарий
	public $auto_registration  = FALSE; // Автоматически регистрировать пользователей оставивших комментарий
	public $post_delay         = 180;   // 3 min

	// public $allow_reply        = TRUE;  // Разершить отвечать на комментарии (древовидная стр-ра)
	public $mailing            = array();
	// public $manager_ids        = array(); // Идентификаторы менеджеров
	
	//--------------------------------------------------------------------------
	
	public $table  = 'comments';
	public $type   = '';
	public $rel_id = 0;
	public $page   = 1;
	public $total_pages = 1;
	
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		parent::__construct();
		
		sys::set_config_items($this, 'comments');
	}
	
	//--------------------------------------------------------------------------
	
	public function init()
	{
		$this->fields['comments'] = array(
			'id'   => array(),
			'pid'  => array(),
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
				'rules' => 'trim|strip_tags|required|min_length[2]',
			),
			'postdate' => array(
				'label'   => 'Дата публикации',
				'default' => time(),
			),
			'ip' => array(
				'default' => isset($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : 0
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
	
	public function get_data()
	{
		$result = new stdClass;
		$result->parents = array();
		$result->childs  = array();

		// $this->db->where();
		// $count_all = $this->get_count();
		
		$count_parents = $this->get_count(NULL,NULL,$this->tree_mode?'pid=0':'');

		if ($count_parents > $this->per_page)
		{
			$this->total_pages = floor($count_parents / $this->per_page);
			$this->db->limit( $this->page*$this->per_page, ($this->page-1)*$this->per_page );
			// $this->load->library('pagination');
			// $this->pagination->set_group('comments');
			// $this->pagination->init($total, $this->per_page, NULL, 'comments_?.html');
			// $this->pagination->set_db_limit();
			// $this->db->limit($this->per_page);
		}

		// get parents
		$this->db->where('pid=0');
		$result->parents = $this->get_result();
		
		if ($this->tree_mode && $result->parents)
		{
			foreach ($result->parents as $row)
			{
				$min_date = isset($min_date) ? min($min_date, $row->postdate) : $row->postdate;
				// $max_date = isset($max_date) ? max($max_date, $row->postdate) : $row->postdate;
			}

			$this->db->where('pid>0 AND postdate > ' . $min_date)->order_by('postdate');
			$childs = $this->get_result();
			foreach ($childs as $i=>$row)
			{
				$result->childs[$row->pid][$row->id] =& $childs[$i];
			}
		}
		
		return $result;
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

		// $row->profile_url = h_url::url("users/{$row->uid}");
		
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
		
		$insert_id = parent::insert();
		// $insert_id = 19;

		$this->mail_process($insert_id);

		return $insert_id;
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
	
	public function get_count($type = NULL, $rel_id = NULL, $where = NULL)
	{
		static $resutl = array();

		if ($type === NULL)   $type   = $this->type;
		if ($rel_id === NULL) $rel_id = $this->rel_id;

		$key = $type . $rel_id . $where;

		if (isset($resutl[$key])) return $resutl[$key];
		
		if ($where) $this->db->where($where);

		$resutl[$key] = $this->db->select('COUNT(*) AS x')
			->where('type = ? AND rel_id = ?', $type, $rel_id)
			->get($this->table)->row()->x;
			
		return $resutl[$key];
	}

	//--------------------------------------------------------------------------

	public function mail_process($comment_id)
	{
		if (empty($this->mailing[$this->type]) || ! $this->rel_id) return;

		$mailing   = $this->mailing[$this->type];
		$template  = $mailing['template'];

		// GET DATA
		$model =& $this->load->model($this->type);
		$this->db->where($model->table . '.id=?', $this->rel_id);
		$data = $model->get_row();

		if ( ! $data || empty($data->uid) || $data->uid == sys::$ext->user->id)
		{
			return;
		}

		$this->load->library('Mail');
		
		$full_link = $this->mail->base_url . $this->uri->uri_string . '/#comment_' . $comment_id;

		// GET COMMENT DATA
		// $this->db->where($this->table . '.id=?', $comment_id);
		// $comment = $this->get_row();

		// Mail data
		// $email_data['comment']    =& $comment;
		$email_data['full_link']  =& $full_link;
		$email_data['data']       =& $data;
		$email_data['comment_id'] =& $comment_id;
		$email_data['mailing']    =& $mailing;

		// SEND
		// $this->mail->debug       = TRUE;
		// $this->mail->debug_email = FALSE;
		// echo "<hr>";
		$this->mail->send_to_user($data->uid, $template, $email_data);

		// die('<hr>mail_process');
	}

	//--------------------------------------------------------------------------
	
	// public function is_manager($uid = NULL)
	// {
	// 	if ( ! $uid)
	// 	{
	// 		$uid = sys::$ext->user->id;
	// 	}
		
	// 	if ( ! $uid) return FALSE;
		
	// 	return sys::$ext->user->group_id == 1 || in_array($uid, $this->manager_ids);
	// }
	
	//--------------------------------------------------------------------------
}