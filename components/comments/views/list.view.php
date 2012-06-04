<? if (count($data)): ?>
	<hr>
	
	
	<b>Комментарии (<?=$count_all ?>) <a href="#add_comment">Оставить комментарий</a></b>
	<br />
	<br />
	
	<? foreach ($data as $row): ?>
		<div class="row_box" id="comment_<?=$row->id ?>">
			
			<?=$row->admin_buttons ?>
			
			<? if(empty($row->full_name)): ?>
				<b>Гость</b>
			<? else: ?>
				<b><a href="<?=$row->profile_url ?>"><?=$row->full_name ?></a></b>
			<? endif ?>
	
			<span class="postdate">(<?=hlp::date($row->postdate) ?>):</span>
			
			<? if ($this->comments->is_manager()): ?>
				<a style="float:right; color:#900; background:#FAA;padding:1px 5px; font-size:12px; text-decoration:none;border-bottom: 1px solid #C33;" href="/comments/delete/<?=$row->id ?>/">Удалить</a>
			<? endif ?>
			
			<div class="message_content">
				<?=nl2br($row->message) ?>
			</div>
		</div>
	<? endforeach ?>
<? endif ?>

<?=$this->load->component("comments/form/{$type}/{$rel_id}") ?>