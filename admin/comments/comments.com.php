<?php


class COM_Comments extends SYS_Component
{

	function init()
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
		
		$data = $this->comments->get_result();

		$models_ids = array();
		foreach ($data as $row)
		{
			$models_ids[$row->type][$row->rel_id] = $row->rel_id;
		}

		$links = array();
		foreach ($models_ids as $modelname => $ids)
		{
			$modelkey = $modelname;
			if (isset($this->comments->model_aliases[$modelname]))
			{
				$modelname = $this->comments->model_aliases[$modelname];
			}
			if (isset(sys::$model->$modelname)) continue;
			$modelclass = MODEL_CLASS_PREFIX . $modelname;
			$model = $this->load->model($modelname, FALSE);

			if ($model) {
				$this->db->where_in($model->table . '.id', $ids);
				$result = $model->get_result();

				foreach ($result as $row)
				{
					$links[$modelkey][$row->id]['model'] = isset($model->name) ? $model->name : $modelkey;
					$links[$modelkey][$row->id]['link'] = isset($row->full_link) ? $row->full_link : 
						( isset($row->full_url) ? $row->full_url : FALSE );
					if ($links[$modelkey][$row->id]['link'])
					{
						$links[$modelkey][$row->id]['title'] = isset($row->title) ? $row->title : 
							( isset($row->name) ? $row->name : FALSE );
					}
				}
			}
		}

		$this->data['data']  =& $data;
		$this->data['links'] =& $links;
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