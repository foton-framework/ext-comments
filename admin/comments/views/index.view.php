<script type="text/javascript">
var del = 'Удалить коментарий?';
</script>

<h1>Комментарии</h1>

<?=$this->pagination->render() ?>

<table class="data_table">
	<tr>
		<th>ID</th>
		<th>Рейтинг</th>
		<th>Сообщение</th>
		<th>Автор</th>
		<th>Дата</th>
		<th>Ссылка</th>
		<th></th>
	</tr>
	
	<? $index = 0 ?>
	<? foreach ($data as $row): ?>
		<tr class="<?=$index++%2==0 ? 'a' : 'b' ?>">
			<td><?=$row->id ?></td>
			<td style="color:#<?=$row->ratio>0?'0B0':($row->ratio<0?'900':'BBB') ?>"><?=$row->ratio>0?'+':($row->ratio<0?'-':'') ?><?=$row->ratio ?></td>
			<td><?=$row->message ?></td>
			<td><?=isset($row->full_name) ? $row->full_name : $row->name ?></td>
			<td><?=hlp::date($row->postdate) ?></td>
			<td>
				<? $link = "/{$row->type}/" . ($row->rel_id ? "{$row->rel_id}/" : '') ?>
				<a href="<?=$link . "#comment_{$row->id}" ?>"><?=$link ?></a>
			</td>
			<td>
				<a href="/admin/comments/remove/<?=$row->id ?>/" onclick="return confirm(del)">Удалить</a>
			</td>
		</tr>
	<? endforeach ?>	
</table>


<?=$this->pagination->render() ?>

