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
		$this->view = 'list';
		$this->type = $type;
		
		$this->comments->type   = $type;
		$this->comments->rel_id = $rel_id;
		
		$this->data['type']      = $type;
		$this->data['rel_id']    = $rel_id;
		$this->data['count_all'] = $this->comments->get_count($type, $rel_id);
		$this->data['data']      = $this->comments->get_result();
	}
	
	//--------------------------------------------------------------------------
	
	function form($type, $rel_id = 0)
	{
		if ($this->user->id || $this->comments->allow_guest)
		{
			$this->comments->init_form();
			
			if ($this->form->validation() && $this->comments->check_permissions())
			{
				$this->comments->type   = $type;
				$this->comments->rel_id = $rel_id;
				
				$id = $this->comments->insert();
				
				hlp::redirect("#comment_{$id}");
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
	
	function act_delete($id)
	{
		if (! $this->comments->is_manager($this->user->id)) return sys::error_404();
		
		$this->db->where('id=?', $id)->update($this->comments->table, array('status'=>0));
		
		hlp::redirect_back();
		$this->view = FALSE;
	}
	
	//--------------------------------------------------------------------------

}