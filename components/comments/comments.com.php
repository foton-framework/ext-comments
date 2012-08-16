<?php


class EXT_COM_Comments extends SYS_Component
{
	//--------------------------------------------------------------------------
	
	function init()
	{
		$this->load->extension('comments');
	}
	
	//--------------------------------------------------------------------------
	
	function get($type, $rel_id = 0)
	{
		$this->view = 'get';
		$this->type = $type;
		
		$this->comments->type   = $type;
		$this->comments->rel_id = $rel_id;
		
		$total = $this->comments->get_count();
		

		$this->data['type']      =& $type;
		$this->data['rel_id']    =& $rel_id;
		$this->data['count_all'] =& $total;

		// $this->data['content']   =& $this->content($data, $childs);
	}
	
	//--------------------------------------------------------------------------
	
	function act_ajax($type, $rel_id, $page)
	{
		$this->template->enable = FALSE;
		$this->view = FALSE;

		$this->comments->type   = $type;
		$this->comments->rel_id = $rel_id;
		$this->comments->page   = $page;
		
		$this->content($type, $rel_id, $page);
	}

	//--------------------------------------------------------------------------

	function form()
	{
		if ($this->user->id || $this->comments->allow_guest)
		{
			$this->comments->init_form();

			if (isset($_POST['message']) && $this->form->validation() && $this->comments->check_permissions())
			{
				$id = $this->comments->insert();
				
				unset($_POST['message']);

				hlp::redirect("/{$this->uri->uri_string}/#comment_{$id}");
			}
		}
		else
		{
			$this->view = 'authorize';
		}
	}
	
	//--------------------------------------------------------------------------
	
	function last($limit = 3)
	{
		$this->db->limit($limit);
		$comments = $this->comments->get_result();
		
		$rel = array();
		foreach ($comments as $row) $rel[$row->type][] = $row->rel_id;
		$parent = array();
		foreach ($rel as $table => $row)
		{
			$result = $this->db->select('id, title')->where_in('id', $row)->get($table)->result();
			foreach ($result as $page)
			{
				$parent[$table][$page->id] = $page;
			}
		}
		
		foreach ($comments as &$row)
		{
			if (empty($parent[$row->type][$row->rel_id])) continue;
			$row->title = $parent[$row->type][$row->rel_id]->title;
			if (empty($parent_link[$row->type]))
			{
				$row->link = '/' . $row->type . '/' . $row->rel_id . '/#comment_' . $row->id;
			}
			else
			{
				$row->link = sprintf($parent_link[$row->type], $row->rel_id);
			}
		}
		error_reporting($this->user->group_id == 1 ? E_ALL : FALSE);
		$this->data['comments'] =& $comments;
	}
	
	//--------------------------------------------------------------------------
	
	function content($type=NULL, $rel_id=NULL, $page=1)
	{
		if ($type)   $this->comments->type   = $type;
		if ($rel_id) $this->comments->rel_id = $rel_id;
		if ($page)   $this->comments->page   = $page;

		$data = $this->comments->get_data();

		$this->data['data'] =& $data;
		$this->view = 'list';
	}

	//--------------------------------------------------------------------------
	
	function script()
	{
		
	}

	//--------------------------------------------------------------------------

	// function act_delete($id)
	// {
	// 	if (! $this->comments->is_manager($this->user->id)) return sys::error_404();
		
	// 	$this->db->where('id=?', $id)->update($this->comments->table, array('status'=>0));
		
	// 	hlp::redirect_back();
	// 	$this->view = FALSE;
	// }
	
	//--------------------------------------------------------------------------

}