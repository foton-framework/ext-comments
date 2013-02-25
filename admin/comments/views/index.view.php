<script type="text/javascript">
var del = 'Удалить коментарий?';
</script>

<h1>Комментарии</h1>

<?=$this->pagination->render() ?>

<table class="data_table inline_admin_buttons">
	<tr>
		<th>ID</th>
		<th>&star;</th>
		<th>Сообщение</th>
		<th>Автор</th>
		<th width=130>Дата</th>
		<th width=150>Ссылка</th>
		<th width=90></th>
	</tr>
	
	<? $index = 0 ?>
	<? foreach ($data as $row): ?>
		<tr class="<?=$index++%2==0 ? 'a' : 'b' ?>">
			<td><?=$row->id ?></td>
			<td style="color:#<?=$row->ratio>0?'0B0':($row->ratio<0?'900':'BBB') ?>"><?=$row->ratio>0?'+':($row->ratio<0?'-':'') ?><?=$row->ratio ?></td>
			<td><?=$row->message ?></td>
			<td>
				<? if ($row->email): ?>
					<a href="<?=$row->profile_url ?>"><?=isset($row->login) ? $row->login : $row->email ?></a>
				<? else: ?>
					<span style="color:#666">Гость</span>
				<? endif ?>
			</td>
			<td><?=hlp::date($row->postdate) ?></td>
			<td>
				<? if (isset($links[$row->type]) && isset($links[$row->type][$row->rel_id])): ?>
					<? $link = $links[$row->type][$row->rel_id] ?>
					<? if ( ! $link['link'] || $link['link']{0} != '/'): ?>
						<?=$link['model'] ?>:<br><?=$link['title'] ?> (<?=$row->rel_id ?>)
					<? else: ?>
						<a href="<?=$link['link'] . "#comment_{$row->id}" ?>"><?=$link['model'] ?>:<br><?=$link['title'] ?> (<?=$row->rel_id ?>)</a>
					<? endif ?>
				<? else: ?>
					нет ссылки
				<? endif ?>
			</td>
			<td>
				<?=$row->admin_buttons ?>
			</td>

		</tr>
	<? endforeach ?>	
</table>


<?=$this->pagination->render() ?>

