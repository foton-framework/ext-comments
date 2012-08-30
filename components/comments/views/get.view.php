<?=$this->load->component('comments/script') ?>

<hr>
<? if ($count_all): ?>
	<b>Комментарии (<?=$count_all ?>)</b>
	<a class="right" href="#" onclick="return comments_form(this)">Добавить комментарий</a>
	<hr>
	<?=$this->load->component("comments/content/{$type}/{$rel_id}") ?>
<? endif ?>

<div id="add_comment"></div>
<hr>

<div id="comment_add"><?=$this->load->component("comments/form/{$type}/{$rel_id}") ?></div>