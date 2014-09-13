/**
 * Control moder panel (CMP) for webmcr
 * 
 * @author Qexy.org (admin@qexy.org)
 *
 * @copyright Copyright (c) 2014-2014 Qexy.org
 *
 * @version 1.0
 *
 */

$(function(){
	$("[rel='tooltip']").tooltip({container: 'body'});

	$(".del-accept").click(function(){
		return confirm("Вы уверены, что хотите удалить выбранный элемент?");
	});
});

function bb(obj, leftcode, rightcode)
{
	if(document.selection)
	{ // Для IE
		var s = document.selection.createRange();
		if (s.text){ s.text = leftcode + s.text + rightcode; }
	}else{ // Opera, FireFox, Chrome

		var start = obj.selectionStart;
		var end = obj.selectionEnd;
		s = obj.value.substr(start,end-start);
		obj.value = obj.value.substr(0, start) + leftcode + s + rightcode + obj.value.substr(end);
	}
}