$(function(){
	$("#lesson_menu .menu-bd a").click(function(){
		$('#lesson_menu #lesson-current a').text($(this).text()); // ���õ�ǰѡ�е��������label	
		$(this).parents("#lesson_menu .menu-bd").hide().siblings("#lesson_menu .menu-hd").removeClass("selected");  // �������˵�����, ȡ��ѡ��״̬
		$("#lesson_menu .menu-hd #lesson-current a.collapsible").removeClass("open"); // �����ѡ�����ָ�������״̬
		return false;
	});
});

$(function(){
	$("#show_menu .menu-bd a").click(function(){
		$('#show_menu #show-current a').text($(this).text()); // ���õ�ǰѡ�е��������label	
		$(this).parents("#show_menu .menu-bd").hide().siblings("#show_menu .menu-hd").removeClass("selected");  // �������˵�����, ȡ��ѡ��״̬
		$("#show_menu .menu-hd #show-current a.collapsible").removeClass("open"); // �����ѡ�����ָ�������״̬
		return false;
	});
});

$(function(){
	$("#anew_menu .menu-bd a").click(function(){
		$('#anew_menu #anew-current a').text($(this).text()); // ���õ�ǰѡ�е��������label	
		$(this).parents("#anew_menu .menu-bd").hide().siblings("#anew_menu .menu-hd").removeClass("selected");  // �������˵�����, ȡ��ѡ��״̬
		$("#anew_menu .menu-hd #anew-current a.collapsible").removeClass("open"); // �����ѡ�����ָ�������״̬
		return false;
	});
});
