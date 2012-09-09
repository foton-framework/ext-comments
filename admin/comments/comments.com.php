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

			if ( ! $model)
			{
				$model = sys::call($modelname);
			}

			if ($model) {
				$this->db->where_in($model->table . '.id', $ids);
				$result = $model->get_result();

				foreach ($result as $row)
				{
					$links[$modelkey][$row->id]['model'] = isset($model->name) ? $model->name : $modelname;
					$links[$modelkey][$row->id]['url']   = isset($row->full_link) ? $row->full_link : 
						( isset($row->full_url) ? $row->full_url : FALSE );
					$links[$modelkey][$row->id]['title'] = isset($row->title) ? hlp::cut_text($row->title,100) : 
							( isset($row->name) ? $row->name : FALSE );
				}
			}
		}

		foreach ($data as $i=>$row)
		{
			if (isset($links[$row->type][$row->rel_id]))
			{
				$page = $links[$row->type][$row->rel_id];
				$data[$i]->page_title = $page['title'];
				$data[$i]->page_model = $page['model'];
				if ( ! $data[$i]->page_url)
				{
					$data[$i]->page_url = $page['url'];
				}
			}

			if (empty($data[$i]->page_title)) $data[$i]->page_title = $data[$i]->page_url;
			if (empty($data[$i]->page_model)) $data[$i]->page_model = $data[$i]->type;
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