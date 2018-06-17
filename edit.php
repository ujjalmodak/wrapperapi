<?php
require('classes/class.ion-wrapper-api-edit.php');

if(isset($_REQUEST['frm_submitted'])){
	$obj = new Wrapperapi\Ion_wrapper_api_edit($_POST);
	$obj->__updatePost();	
}


// $url        = "http://testdemo.iondemo.in/wp-json/wp/v2/posts/";
// $username   = 'testdemo';
// $password   = 'testdemo99**';
// $method     = strtoupper('post'); 


?>

<!DOCTYPE html>
<html>
<head>
	<title>Test ION Wrapper API</title>	
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<script src="js/jquery.min.js"></script>
	<script src="js/popper.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="ckeditor.js"></script>
    
</head>
<body>
<div class="container">
<h1>Wrapper API - Edit Post</h1>
<p>Enter your post ID to edit the post</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" name="frm_wrapper_data" id="frm_wrapper_data" enctype="multipart/text-data">

<div class="form-group">
    <label for="baseurl">Base URL*:</label>
    <input type="text" class="form-control" id="baseurl" name="baseurl" value="http://iondemo.in" required="required" tabindex="1">
</div>

<div class="form-group">
    <label for="post_id">Post ID*:</label>
    <input type="text" class="form-control col-1" id="post_id" name="post_id" required="required" tabindex="2">
    <span class="form-inline" id="loading"><img src="img/rolling.gif" /> Please Wait...</span>
    <small><a href="#" data-toggle="tooltip" data-placement="right" title="Positive Integer and non ZERO value Only">(Integer value Only)</a></small>
</div>



<div class="form-group">
    <label for="title">Title*:</label>
    <input type="text" class="form-control" id="title" name="title" required="required" tabindex="3">
</div>
<div class="form-group">
    <label for="description">Description:</label>
    <textarea class="form-control" rows="5" id="description" name="description" tabindex="4"></textarea>
      
</div>
<div class="form-group">
    <label for="category">Category:</label>
    <input type="text" class="form-control" id="category" name="category" tabindex="5"><small><a href="#" data-toggle="tooltip" data-placement="right" title="You can add multiple categories seperated by comma. If you enter slug, it should be in lowercase and hypens.">(Comma seperated category name or slug Only)</a></small>
</div>
<div class="form-group">
    <label for="category">Tags:</label>
    <input type="text" class="form-control" id="tags" name="tags" tabindex="6"><small><a href="#" data-toggle="tooltip" data-placement="right" title="You can add multiple tags seperated by comma. If you enter slug, it should be in lowercase and hypens.">(Comma seperated tag name or slug Only)</a></small>
</div>
<div class="form-group">
    <label for="img_url">Image:</label>
    <input type="text" class="form-control" id="img_url" name="img_url" tabindex="7"><small><a href="#" data-toggle="tooltip" data-placement="right" title="A absolute path of the image. Example: https://www.image1.jpg">(Image path)</a></small>    
</div>

<div class="form-group form-inline">
    <label for="img_alt">Alt Text:&nbsp;</label>
    <input type="text" class="form-control col" id="img_alt" name="img_alt" tabindex="8">&nbsp;

    <label for="img_caption">Caption:&nbsp;</label>
    <input type="text" class="form-control col" id="img_caption" name="img_caption" tabindex="9">&nbsp;

    <label for="img_description">Description:&nbsp;</label>
    <input type="text" class="form-control col" id="img_description" name="img_description" tabindex="10">
</div>

<div class="form-group">
  <label for="status">Status:</label>
  <select class="form-control col-2" id="status" name="status" tabindex="11">
    <option value="publish">Publish</option>
    <option value="draft">Draft</option>
  </select>
</div> 

<div class="form-group">
    <label for="username">Username*:</label>
    <input type="text" class="form-control col-2" id="username" name="username" required="required" tabindex="12">
</div>

<div class="form-group">
    <label for="category">Password*:</label>
    <input type="Password" class="form-control col-2" id="userpassword" name="userpassword" required="required" tabindex="13">
</div>

<input type="hidden" name="frm_submitted" value="1"/>
<button type="submit" class="btn btn-primary" name="btn_submit" tabindex="14">Update</button>

</form>



</div>
<p>&nbsp;</p>
<style>
    #loading{
        display: none;
        position: absolute;
        margin-top: -32px;
        margin-left: 100px;
    }
</style>
<script src="js/wrapper-custom.min.js"></script>
<script>
$(document).ready(function(){ 
    CKEDITOR.replace( 'description' );
    $('[data-toggle="tooltip"]').tooltip();
    $('#post_id').on('blur', function() { 
    $.fn.getPost();
    });
});
</script>
</body>
</html>