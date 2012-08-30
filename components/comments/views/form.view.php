<?=h_form::open(($this->uri->uri_string ? '/' : '')  . "{$this->uri->uri_string}/") ?>
	
	<?=$this->form->form_errors() ?>

	<?=$this->form->field('message', NULL, 'style="width:99%; height:100px"') ?>
		
	<? if ( ! $this->user->id): ?>
		<div class="info">
		<?//=$this->form->field('code', rand(100000,999999), 'id="code"') ?>
		<a href="/users/login/" >Авторизуйтесь</a> если вы уже зарегистрированы
		</div>
	<? endif ?>
	
	
	<? if ($this->comments->captcha_protection_enable()): ?>
		<table class="wrapper">
		<tr>
			<td><?=$this->captcha->image() ?></td>
			<td>&nbsp;</td>
			<td><?=$this->captcha->label() ?>:<br /><?=$this->captcha->field() ?></td>
		</tr>
		</table>
	<? endif ?>
	
	<?=h_form::hidden('pid') ?>
	
	<button type="submit">Добавить комментарий</button>

	<div class="clr"></div>
		
<?=h_form::close() ?>