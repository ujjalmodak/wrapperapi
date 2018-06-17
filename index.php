<?php
require('classes/class.ion-wrapper-api2.php');
use Wrapperapi as ionw;
if(isset($_REQUEST['frm_submitted'])){
	$obj = new ionw\Ion_wrapper_api($_POST);
	$obj->__createPost();	
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Test ION Wrapper API</title>	
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- <link rel="stylesheet" href="css/bootstrap.min.css"> -->    
	<script src="js/jquery.min.js"></script>
    <script src="js/moment.js"></script>
    <script src="js/transition.js"></script>
    <script src="js/collapse.js"></script>
	<script src="js/popper.min.js"></script>
	<script src="js/bootstrap.min.js"></script>




<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker-standalone.min.css" />






<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">



    <script type="text/javascript" src="ckeditor.js"></script>
</head>
<body>
<div class="container">
    
<div class="row">
    <div class="col-10"><h1>Wrapper API - Create Post</h1><p>&nbsp;</p></div>
    <div class="col-2">Update</div>
</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" name="frm_wrapper_data" id="frm_wrapper_data" enctype="multipart/text-data">

<div class="form-group">
    <label for="baseurl">Base URL:</label>
    <input type="text" class="form-control" id="baseurl" name="baseurl" value="http://myrightdoctor.com/piles">
</div>
<div class="form-group">
    <label for="title">Title*:</label>
    <input type="text" class="form-control" id="title" name="title" required="required">
</div>
<div class="form-group">
    <label for="description">Description:</label>
    <textarea class="form-control" rows="5" id="description" name="description"></textarea>
    
</div>
<div class="form-group">
    <label for="category">Category:</label>
    <input type="text" class="form-control" id="category" name="category"><small><a href="#" data-toggle="tooltip" data-placement="right" title="You can add multiple categories seperated by comma. If you enter slug, it should be in lowercase and hypens.">(Comma seperated category name or slug Only)</a></small>
</div>
<div class="form-group">
    <label for="category">Tags:</label>
    <input type="text" class="form-control" id="tags" name="tags"><small><a href="#" data-toggle="tooltip" data-placement="right" title="You can add multiple tags seperated by comma. If you enter slug, it should be in lowercase and hypens.">(Comma seperated tag name or slug Only)</a></small>
</div>
<div class="form-group">
    <label for="img_url">Image:</label>
    <input type="text" class="form-control" id="img_url" name="img_url"><small><a href="#" data-toggle="tooltip" data-placement="right" title="A absolute path of the image. Example: https://www.image1.jpg">(Image path)</a></small>    
</div>

<div class="form-group form-inline">
    <label for="img_alt">Alt Text:&nbsp;</label>
    <input type="text" class="form-control col" id="img_alt" name="img_alt">&nbsp;

    <label for="img_caption">Caption:&nbsp;</label>
    <input type="text" class="form-control col" id="img_caption" name="img_caption">&nbsp;

    <label for="img_description">Description:&nbsp;</label>
    <input type="text" class="form-control col" id="img_description" name="img_description">
</div>

<div class="form-group">
    <label for="authorname">Author:</label>
    <input type="text" class="form-control" id="authorname" name="authorname"><small>(If it is a existing Author, no need for email id otherwise provide email id)</small>
</div>

<div class="form-group">
    <label for="authorname">Author Email ID:</label>
    <input type="email" class="form-control" id="emailid" name="emailid">
</div>

<div class="form-group">
  <label for="status">Status:</label>
  <select class="form-control col-2" id="status" name="status">
    <option value="publish">Publish</option>
    <option value="future">Future</option>
    <option value="draft">Draft</option>
  </select>
</div>

<div class="container form-group">
    <div class="row">
        <div class='col-sm-6'>
            <div class="form-group">
                <div class='input-group date' id='datetimepicker1'>
                    <input type='text' class="form-control" name="customdatetime" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(function () {
                $('#datetimepicker1').datetimepicker();
            });
        </script>
    </div>
</div>








<div class="form-group">
    <label for="username">Username*:</label>
    <input type="text" class="form-control col-2" id="username" name="username" required="required">
</div>

<div class="form-group">
    <label for="category">Password*:</label>
    <input type="Password" class="form-control col-2" id="userpassword" name="userpassword" required="required">
</div>

<input type="hidden" name="frm_submitted" value="1"/>
<button type="submit" class="btn btn-primary" name="btn_submit" >Create</button>
</form>
</div>
<p>&nbsp;</p>
<script type="text/javascript">
$(document).ready(function(){
    CKEDITOR.replace( 'description' );
    $('[data-toggle="tooltip"]').tooltip();   
});
</script>
</body>
</html>