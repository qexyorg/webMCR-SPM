$(function(){
	$("[rel='tooltip']").tooltip({container: 'body'});

	$(".del-accept").click(function(){
		return confirm("Вы уверены, что хотите удалить выбранный элемент?");
	});
});