<?php


class COM_Comments extends SYS_Component
{

	function main_init()
	{
		$this->load->extension('comments');
	}
	
	//--------------------------------------------------------------------------
	
	function index()
	{
		$this->router();
	}
	
	//--------------------------------------------------------------------------
	
	function router()
	{
		$this->load->library('pagination');
		
		$this->pagination->init($this->comments->count_all(), 50);
		$this->pagination->set_db_limit();
		
		$this->data['data'] = $this->comments->get_result();
		$this->view = 'index';
	}
	
	//--------------------------------------------------------------------------
	
	function act_remove($id)
	{
		$this->db->where('comments.id=?', $id);
		$this->comments->delete();
		hlp::redirect_back();
	}
		
	//--------------------------------------------------------------------------
	
}