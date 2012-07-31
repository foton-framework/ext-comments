<? //-------------------------------------------------------------------------- ?>
<? function comments(&$data, &$childs, $level=1){ ?>
<? //-------------------------------------------------------------------------- ?>

	<? foreach ($data as $row): ?>
		<div class="comment_row level_<?=$level ?>" id="comment_<?=$row->id ?>">
			
			<?=$row->admin_buttons ?>
			
			<? if ( ! empty($row->avatar)): ?>
				<a href="<?=$row->profile_url ?>" class="avatar"><img src="<?=$row->avatar ?>" alt="" /></a>
			<? else: ?>
				<span class="avatar"><img src="<?=isset(sys::$ext->user->no_avatar) ? sys::$ext->user->no_avatar : '/' . sys::$config->user_model->thumbs['avatar']['dist'] . 'no_avatar.jpg' ?>" alt="" /></span>
			<? endif ?>

			<div class="comment_content">

				<span class="login">
					<? if(empty($row->full_name)): ?>
						Гость
					<? else: ?>
						<a href="<?=$row->profile_url ?>"><?=$row->full_name ?></a>
					<? endif ?>
				</span>
		
				<span class="postdate"><?=hlp::date($row->postdate) ?></span>
				
				<div class="message_content">
					<?=nl2br($row->message) ?>
				</div>

				<? if (sys::$ext->comments->tree_mode): ?>
					<a class="comment_answer" href="#" onclick="return comments_answer(this, <?=$row->id ?>)">Ответить</a>
				<? endif ?>
			</div>

			<div class="clr"></div>
		</div>
		
		<? if ( ! empty($childs[$row->id])): ?>
			<?=comments($childs[$row->id], $childs, $level+1) ?>
			<br>
		<? endif ?>

	<? endforeach ?>

	<? if ($level == 1 && sys::$ext->comments->total_pages>sys::$ext->comments->page): ?>
		<div class="comments_more">
			<a href="#" onclick="return comments_more(this, '<?=sys::$ext->comments->type ?>', <?=sys::$ext->comments->rel_id ?>, <?=sys::$ext->comments->page+1 ?>)">
				Показать остальные комментарии…
			</a>
		</div>
	<? endif ?>

<? //-------------------------------------------------------------------------- ?>
<? } ?>
<? //-------------------------------------------------------------------------- ?>

<?=comments($data->parents, $data->childs) ?>