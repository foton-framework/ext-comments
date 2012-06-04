<? if (count($comments)): ?>
	<? foreach ($comments as $c): ?>
		<!--<?=$c->uid ?>-->
		<div class="comment_row" id="comment_<?=$c->id ?>">
			
			<?=$c->admin_buttons ?>
	
			<a class="more_link blue" href="<?=$c->link ?>"><span><?=$c->title ?></span></a>
			
			<? if(empty($c->full_name)): ?>
				Гость
			<? else: ?>
				<b class="user"><?=strip_tags($c->full_name) ?></b>
			<? endif ?>
			<span class="postdate"><?=hlp::date($c->postdate) ?></span>
			
			
			<div class="message_content">
				<div class="decor"></div>
				<?=nl2br($c->message) ?>
			</div>
		</div>
	<? endforeach ?>
<? endif ?>
