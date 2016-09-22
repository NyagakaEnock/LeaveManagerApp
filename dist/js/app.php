<script>
$("#postCompany").click(){
	$.ajax({
		type: "post",
		url: "http://localhost/CodeIgnitorTutorial/index.php/usercontroller/verifyUser",
		cache: false,				
		data: $('#userForm').serialize(),
		success: function(json){						
		try{		
			var obj = jQuery.parseJSON(json);
			alert( obj['STATUS']);
					
			
		}catch(e) {		
			alert('Exception while request..');
		}		
		},
		error: function(){						
			alert('Error while request..');
		}
 });
}

</script>