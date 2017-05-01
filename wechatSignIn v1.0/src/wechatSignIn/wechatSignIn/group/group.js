 $(document).on("change","#members",function() { 
	 		var group=$("#group option:selected").text();
	 		var time_y_b=$("#years option:selected").text();
			var time_m_b=$("#months option:selected").text();
			var time_d_b=$("#days option:selected").text();
			var time_y_a=$("#_years option:selected").text();
			var time_m_a=$("#_months option:selected").text();
			var time_d_a=parseInt($("#_days option:selected").val())+1;
			var time_before=time_y_b+"-"+time_m_b+"-"+time_d_b;
			var time_after=time_y_a+"-"+time_m_a+"-"+time_d_a;
			var members=$("#members option:selected").val();
	   		$.post('result.php',{name:group,
	   							 time_before:time_before,
	   							 time_after:time_after,
	   							 headman:members
	   							},function(result){
	   								$("#container").empty();
	   								$("#container").append(result);
	   									
   					 });
   	});
	$(document).on("change","#group",function() { 
	 		var group=$("#group option:selected").text();
	 		var members=$("#members");
	 		members.empty();  
	   		$.post('select_headman.php',{name:group},function(json){
 			     		var attr=JSON.parse(json);
 			     		if (attr!=null) { 
 			     		for (var person_name in attr) {
       						members.append("<option value='"+attr[person_name]+"'>"+person_name+"</option>");
         				 }
         				}
   					 });
   			 
   		});
    $(document).on("click","#Submit",function() { 
	 		var group=$("#group option:selected").text();
	 		var time_y_b=$("#years option:selected").text();
			var time_m_b=$("#months option:selected").text();
			var time_d_b=$("#days option:selected").text();
			var time_y_a=$("#_years option:selected").text();
			var time_m_a=$("#_months option:selected").text();
			var time_d_a=parseInt($("#_days option:selected").val())+1;
			var time_before=time_y_b+"-"+time_m_b+"-"+time_d_b;
			var time_after=time_y_a+"-"+time_m_a+"-"+time_d_a;
			var members=$("#members option:selected").val();
	   		$.post('result.php',{name:group,
	   							 time_before:time_before,
	   							 time_after:time_after,
	   							 headman:members
	   							},function(result){
	   								$("#container").empty();
	   								$("#container").append(result);
	   									
   					 });		
   	});
   	$(document).ready(function(){
   		$.post("search_main.php",{},function(result) {
   			$("#control").empty();
   			$("#control").append(result);
   		});	 
   	}); 
   	$(document).on("change","select.months",function() { 
   		var month=$("#_months option:selected").text();
	   	var days = $("#_days");
	   	var _month=$("#months option:selected").text();
	   	var _days = $("#days");
	   	var group=$("#group option:selected").text();
	 		var time_y_b=$("#years option:selected").text();
			var time_m_b=$("#months option:selected").text();
			var time_d_b=$("#days option:selected").text();
			var time_y_a=$("#_years option:selected").text();
			var time_m_a=$("#_months option:selected").text();
			var time_d_a=parseInt($("#_days option:selected").val())+1;
			var time_before=time_y_b+"-"+time_m_b+"-"+time_d_b;
			var time_after=time_y_a+"-"+time_m_a+"-"+time_d_a;
	 		var members=$("#members option:selected").val();
	   		$.post('result.php',{name:group,
	   							 time_before:time_before,
	   							 time_after:time_after,
	   							 headman:members
	   							},function(result){
	   								$("#container").empty();
	   								$("#container").append(result);
	   									
   					 });		
	   	if (month==4||month==6||month==9||month==11) 
	   	{
	   		days.empty();
	   		for(var index=1;index<31;index++)
			{	
				days.append("<option value='"+index+"'>"+index+"</option>"); 
    		}
   		}
   		if (month==1||month==3||month==5||month==7||month==8||month==10||month==12) 
	   	{
	   		days.empty();
	   		for(var index=1;index<32;index++)
			{	
				days.append("<option value='"+index+"'>"+index+"</option>"); 
    		}
   		}
   		if (_month==4||_month==6||_month==9||_month==11) 
	   	{
	   		_days.empty();
	   		for(var index=1;index<31;index++)
			{	
				_days.append("<option value='"+index+"'>"+index+"</option>"); 
    		}
   		}
   		if (_month==1||_month==3||_month==5||_month==7||_month==8||_month==10||_month==12) 
	   	{
	   		_days.empty();
	   		for(var index=1;index<32;index++)
			{	
				_days.append("<option value='"+index+"'>"+index+"</option>"); 
    		}
   		}
	 		 
   	});
 	$(document).on("change","select.time",function() { 
   		var month=$("#_months option:selected").text();
	   	var days = $("#_days");
	   	var _month=$("#months option:selected").text();
	   	var _days = $("#days");
	   	var group=$("#group option:selected").text();
	 		var time_y_b=$("#years option:selected").text();
			var time_m_b=$("#months option:selected").text();
			var time_d_b=$("#days option:selected").text();
			var time_y_a=$("#_years option:selected").text();
			var time_m_a=$("#_months option:selected").text();
			var time_d_a=parseInt($("#_days option:selected").val())+1;
			var time_before=time_y_b+"-"+time_m_b+"-"+time_d_b;
			var time_after=time_y_a+"-"+time_m_a+"-"+time_d_a;
	 		var members=$("#members option:selected").val();
	   		$.post('result.php',{name:group,
	   							 time_before:time_before,
	   							 time_after:time_after,
	   							 headman:members
	   							},function(result){
	   								$("#container").empty();
	   								$("#container").append(result);
	   									
   					 });		 
   	});