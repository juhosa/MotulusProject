$(document).ready(function(){

	$('#lasnaolo').hide();
	$('#kurssit').hide();
	$('#tentit').hide();

	jQuery.fn.GetValue = function(){
		var v = $(this).val();

		if(v == "Läsnäolo")
		{
			$('#lasnaolo').show();
			$('#kurssit').hide();
			$('#tentit').hide();
		}
		else if(v == "Kurssit")
		{
			$('#kurssit').show();
			$('#tentit').hide();
			$('#lasnaolo').hide();
		}
		else if(v == "Tentit")
		{
			alert('ALL YOUR BASE ARE BELONG TO US');
			$('#tentit').show();
			$('#lasnaolo').hide();
			$('#kurssit').hide();
		}
	};
});