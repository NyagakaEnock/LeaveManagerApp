
			$("#loginbtn").click(function () {
		      var dataString = $('#login').serialize(); // Collect data from form
			
            $.ajax({
                type: "POST",
                url: $('#login').attr('action'),
                data: dataString,
                timeout: 6000,
                error: function (request, error) {
                },
                success: function (response) {
			   document.getElementById('responce6').innerHTML=response;
				    if(response==1){
					window.location="http://localhost:8012/ids2/setup";
					}
                }
			
            });
			
            return false;
    });
	
				$("#logoutbtn").click(function () {
		      var dataString = $('#logout').serialize(); // Collect data from form
			
            $.ajax({
                type: "POST",
                url: $('#logout').attr('action'),
                data: dataString,
                timeout: 6000,
                error: function (request, error) {
                },
                success: function (response) {
			   	   
					window.location="http://localhost:8012/ids2/setup";
					
                }
			
            });
			
            return false;
    });
	
	
	function fillCombo(Id,val)
 {  

	 if(val == "")
	 {
		 return;
	 } else {
		 
		 document.getElementById(Id).value = val
		 
	 } 
 }
 
 function calculation(){
	 var totalVal = $('#TotalValue').val();
	 if(totalVal==""){
		 totalVal=0;
	 }
	
	 var PackagesReceived = $('#PackagesReceived').val();
	  var ProductName = $('#ProductName').html();
	 var ValuePerPack = $('#ValuePerPack').val();
	 var UnitValue = $('#UnitValue').val();
	 var UnitsPerPack = $('#UnitsPerPack').val();
	 var UnitValueKsh = $('#UnitValueKsh').val();
	 var ExchRate = $('#ExchRate').val();
	 var Units = $('#Units').val();
	var UnitSize = $('#UnitSize').val();
	var QttyUnits = $('#QttyUnits').val();
	var PackageType = $('#PackageType').val();
	
	 if(totalVal!=""){
	 if(isNaN(totalVal)==false){	
	 
	 $('#ValuePerPack').val(parseInt(totalVal)/parseInt(PackagesReceived));
	 $('#UnitValue').val(parseFloat(ValuePerPack)/parseFloat(UnitsPerPack));
	 $('#UnitValueKsh').val((parseFloat(UnitValue)*parseFloat(ExchRate))/parseFloat(Units));
	  $('#FullDescription').val(PackagesReceived+" "+PackageType+" of "+ProductName+" "+UnitsPerPack+" / "+UnitSize+QttyUnits);
	 }
	 }
	 else {
		 return;
	 }
	 
 }


 


	