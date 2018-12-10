<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
  <title>Number Plate Recognition System</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://adminlte.io/themes/AdminLTE/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="https://adminlte.io/themes/AdminLTE/dist/css/skins/_all-skins.min.css">
  <!-- jQuery 2.2.3 -->
  <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
  <!-- Bootstrap 3.3.6 -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="styles.css">
<script type="text/javascript">
$(document).ready(function() {
	
	var validExt = ".png, .gif, .jpeg, .jpg";
	function fileExtValidate(fdata) {
	 var filePath = fdata.value;
	 var getFileExt = filePath.substring(filePath.lastIndexOf('.') + 1).toLowerCase();
	 var pos = validExt.indexOf(getFileExt);
	 if(pos < 0) {
		alert("This file is not allowed, please upload valid file.");
		return false;
	  } else {
		return true;
	  }
	}
	
	var maxSize = '5120';
	function fileSizeValidate(fdata) {
		 if (fdata.files && fdata.files[0]) {
					var fsize = fdata.files[0].size/1024;
					if(fsize > maxSize) {
						 alert('Maximum file size exceed, This file size is: ' + fsize + "KB");
						 return false;
					} else {
						return true;
					}
		 }
	 }
	
	$('#photoButtonLabel').click(function(){
		
		var data = "";
		$("#targetLayer").html(data);
		
		//reset progressbar
		var progress_bar_id 		= '#progress-wrp';
		var percent =0;		
		$(progress_bar_id +" .progress-bar").css("width", + percent +"%");
		$(progress_bar_id + " .status").text(percent +"%");
	});
	
	$('#fileUpload').change(function(){
	 if(fileExtValidate(this)) {
	 if(fileSizeValidate(this)) {
	$("#processing").show();
	var file_data = $('#fileUpload').prop('files')[0];   
	var form_data = new FormData();  
	var progress_bar_id 		= '#progress-wrp'; //ID of an element for response output
	form_data.append('file', file_data);
	$.ajax({
		url: "upload.php",
		type: "POST",
		data:  form_data,
		contentType: false,
		cache: false,
		processData:false,
		xhr: function(){
			//upload Progress
			var xhr = $.ajaxSettings.xhr();
			if (xhr.upload) {
				xhr.upload.addEventListener('progress', function(event) {
					var percent = 0;
					var position = event.loaded || event.position;
					var total = event.total;
					if (event.lengthComputable) {
						percent = Math.ceil(position / total * 100);
					}
					//update progressbar
					$(progress_bar_id +" .progress-bar").css("width", + percent +"%");
					$(progress_bar_id + " .status").text(percent +"%");
				}, true);
			}
			return xhr;
		},
		beforeSend: function() {
		  $("#processing").show();
	    },
		success: function(data){
			console.log(data);
			$("#targetLayer").html(data);
			$("#processing").hide();						
		  }
	    });
	}
	}
	  
	  });
	});






</script>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <b></b>
    </div>
    <!-- /.login-logo -->
    <div id='overlay'>
        <div id='form' class="login-box-body">
           <form id='uploadForm' action="upload.php" method="post" ENCTYPE="multipart/form-data" >
             <label class="btn btn-block btn-primary" id='photoButtonLabel'> Take Photo
			   <input type="file" accept="image/*" name="userImage" class="inputFile" id="fileUpload" capture="camera" style="display: none;">
			 </label>
            </form>
        </div>        
    </div>
	<div id="progress-wrp"><div class="progress-bar"></div ><div class="status">0%</div></div>
    <div id="output"><!-- error or success results --></div>
	<div id="processing" style="display:none;">	
		<img id="loading-image" src="processing.gif" />
	</div>
	<div id="targetLayer">	
	</div>
	
	
    <!-- /.login-box-body -->
</div>
</body>
</html>