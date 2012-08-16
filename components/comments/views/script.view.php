

<div id="comments_form_js" style="display:none">
	<?=$this->load->component('comments/form') ?>
</div>

<div id="comments_page_divider" style="display:none">
	
	<div class="comment_divider">
		Комментарии (страница %page%)
	</div>

</div>

<script type="text/javascript">


function comments_answer(obj, pid)
{
	var $parent = $(obj).parent();
	$(obj).replaceWith( $('#comments_form_js').html() );
	$parent.find('input[name=pid]').val(pid);
	return false;
}

function comments_form(obj)
{
	var $parent = $(obj).parent();
	$(obj).replaceWith( $('#comments_form_js').html() );
	// $parent.find('input[name=pid]').val(pid);
	return false;
}

function comments_more(obj, type, rel_id, page)
{
	$(obj).attr('onclick', 'return false').addClass('loading');
	$.post('/comments/ajax/' + type + '/' + rel_id + '/' + page + '/', function(data) {
		$(obj).parent().replaceWith( $('#comments_page_divider').html().replace('%page%', page) + data );
	});
	return false;
}

</script>