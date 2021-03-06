<script type="text/javascript" src="{$url}includes/javascript/jquery.validate.js"></script>

<script type="text/javascript">

jQuery.validator.addMethod("validateDomain", 
function(value, element) {
		var domain = $("#domain").val();
		var subdomain_id= $("#csub2").val();
		if (subdomain_id == 'undefined') {
			subdomain_id = 0;
		}			
		$.ajax({
			url:"{$ajax}function=checkSubDomainExistsSimple&domain="+domain+"&subdomain_id="+subdomain_id,
			async:false,
			type: "GET",
			success:  function(data) {
				result = (data=='0') ? true : false;				
			}
		});
		return result;			
});

var wrong = '<img src="{$url}themes/icons/cross.png">';
var right = '<img src="{$url}themes/icons/accept.png">';

	tinyMCE.init({
		mode : "textareas",
		skin : "o2k7",
		theme : "simple"
	});
	$(function() {
		$("#created_at").datepicker({ 
			dateFormat: 'yy-mm-dd',
			showOn: 'button',
			buttonImage: '{$url}themes/icons/calendar_add.png'			 
		});
		 /* $("#addorder").validate(); */
		$("#addorder").validate({$json_encode});		
	});
	
</script>

<script type="text/javascript">
function changeAddons(obj) {	
	var id=obj.options[obj.selectedIndex].value;
	$.get("{$ajax}function=changeAddons&package_id="+id+"&order_id="+document.getElementById("order_id").value, function(data) {
		document.getElementById("showdata").innerHTML = data;
	});
}

function loadPackages(obj) {
	var id=obj.options[obj.selectedIndex].value;
	$.get("{$ajax}function=loadPackages&action=add&billing_id="+id, function(data) {
		document.getElementById("showpackages").innerHTML = data;
	});
	
	var packages = document.getElementById("package_id");
	document.getElementById("showaddons").innerHTML = '-';
	//loadAddons(packages);
}

function loadAddons(obj) {
	var id=obj.options[obj.selectedIndex].value;
	var billing_obj = document.getElementById("billing_cycle_id");
	var billing_id=billing_obj.options[billing_obj.selectedIndex].value;
	
	$.get("{$ajax}function=loadaddons&action=add&package_id="+id+"&billing_id="+billing_id, function(data) {
		document.getElementById("showaddons").innerHTML = data;
	});
}

function lookup(inputString) {
    if(inputString.length == 0) {
        // Hide the suggestion box.
        $('#suggestions').hide();
    } else {
        $.post("{$ajax}function=searchuser",{
            query:inputString
            }, 
            function(data) {
            	if (data.length >0) {
                	$('#suggestions').show();
                	$('#autoSuggestionsList').html(data);
            	}
        } );
    }
}

function changeDomain() {
	var domain_obj = document.getElementById("domain_type");
	var id=domain_obj.options[domain_obj.selectedIndex].value;
		
	var text = '<input name="domain" maxlength="40" autocomplete="off" type="text" id="domain" class="required" /><span id="domain_result"></span>';
	if (id == 1) {
		 $('#domain_input').html(text);
	} else if(id == 2) {		
		if (document.getElementById("package_id") != undefined) {
			var pid = document.getElementById("package_id").value;
			if (pid != '') {				
				$.get("{$ajax}function=sub&pack="+pid, function(data) {		
					 if (data == '') {		
						 domain_obj.selectedIndex = 0;				 
						 data = 'No subdomains available for the moment';					 
					 } else if(data== '0') {
					 	//domain_obj.selectedIndex = 2;
					 	 domain_obj.selectedIndex = 0;		 
					 	$('#domain_input').html('No subdomains available for the server related a this package');
					 } else {
						$('#domain_input').html(data + "<br />" +text ); 
					 }
				});
			} else {
				domain_obj.selectedIndex = 0;
				$('#domain_input').html("Select a package");
			}		
		} else {
			domain_obj.selectedIndex = 0;		
			$('#domain_input').html('Select a billing cycle and a package first');
		}
	}
}

function fill(thisValue,id) {
    $('#inputString').val(thisValue);
    $('#user_id').val(id);
   	$('#suggestions').hide();   	
   	$('#inputString').attr('disabled', 'disabled');
}

function reset() {	
	$('#inputString').removeAttr('disabled');
	$('#inputString').val('');	
}

function checkdomain() {
	var domain = document.getElementById("domain").value;
	$.get("{$ajax}function=checkSubDomainExistsSimple&domain="+domain, function(data) {
		if (data == 1 ) {
			document.getElementById("domain_result").innerHTML = wrong;	
		} else {
			document.getElementById("domain_result").innerHTML = right;
		}		
	});	
}

</script>



<style type="text/css">
.suggestionsBox {
    position: relative;
   /* left: 30px; */
    margin: 0px 0px 5px 0px;
    width: 350px;
    background-color: #fff;    
    border: 2px solid #CFD0D2;
    color: #000;
}
.suggestionList {
    margin: 0px;
    padding: 0px;
    list-style-type:none;
}
.suggestionList li {
	cursor: pointer;
	padding:3px;
}

.suggestionList li:hover {
    background-color: #D2E0F9;
    padding:3px;
}
</style>
    
<div class="page-header">
	<h2>New Order</h2>
</div>

<form class="content"  id="addorder" name="addorder" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">    
    <tr>
    <td width="20%" valign="top">User</td>
    <td >            
	    <input name="user_id" type="hidden" id="user_id" />
	    <input value="Search an user" onfocus="this.value=(this.value=='Search an user') ? '' : this.value;" onblur="this.value=(this.value=='') ? 'Search an user' : this.value;" size="45" autocomplete="off" id="inputString" onkeyup="lookup(this.value);" type="text" class="required" />
	    <img title="Reset" onclick="reset();" src="{$url}themes/icons/arrow_refresh.png">
		<div class="suggestionsBox" id="suggestions" style="display: none;">
			<div class="suggestionList" id="autoSuggestionsList"></div>
		</div> 		
    </td>
  </tr>  
 
   <tr>
    <td valign="top">Billing cycles</td>
    <td>
    {$BILLING_CYCLES}
    <div id = "showdata"></div>
    </td>
  </tr> 
    
    
     <tr>
    <td valign="top">Packages</td>
    <td>
   
    <div id = "showpackages"> {$PACKAGES} </div>
    </td>
  </tr>
  
       <tr>
    <td valign="top">Addons</td>
    <td>
   <div id = "showaddons">-</div>
    </td>
  </tr>
  
    
  
    <tr>
    <td valign="top">Domain type</td>
    <td>
		{$DOMAIN_TYPE}    
    </td>
  </tr> 
  
	<tr>
    	<td valign="top">Domain</td>
    	<td>
    		<div id="domain_input">---</div>    		
    	</td>
  	</tr>
    
   <tr>
    <td valign="top">Order status</td>
    <td>
    {$STATUS}
    <a class="tooltip" title="Will operate on the Control Panel server"><img src="{$icon_dir}information.png"></a>
    </td>
  </tr>
     
	<tr>
	    <td valign="top">Control Panel Username</td>
	    <td>
	  		<input size="30" id="username" name="username" type="text" value="{$DOMAIN_USERNAME}"  class="required"/>
	  		<a class="tooltip" title="The username to login in the Control Panel"><img src="{$icon_dir}information.png"></a>
	    </td>
	</tr>
  
      <tr>
    <td valign="top">Control Panel Password</td>
    <td>
  		<input size="30" id="password"  name="password" type="text" value="{$DOMAIN_PASSWORD}"  class="required"/>
  		<a class="tooltip" title="The password to login in the Control Panel"><img src="{$icon_dir}information.png"></a>
    </td>
  </tr>	
<tr>
    <td valign="top">Creation date</td>
    <td>  		
  		<input name="created_at" type="text" id="created_at" value="{$CREATED_AT}"  class="required"/>
    </td>
  </tr>  
  
    
  <tr>
    <td valign="top">Emails sent when editing this order</td>
    
    <td>
    <div id="show_preview" ></div>  	
    	<ul>	
    	<!-- onclick="send('neworder', {$ID});" -->
  		<li><a target="_blank" href="?page=email&sub=templates&do=19">Edit New Order email</a> 		<a href="?page=email&sub=templates&do=19"><img src="{$url}themes/icons/pencil.png"></a></li>  		  		
  		</ul>
    </td>    
  </tr>   
</table>
<div class="actions">
<input type="submit" name="add" id="add" value="Add order" class="btn primary"/>
</div>
</form>