<?php
$id = md5(rand(6000,PHP_INT_MAX));
?>
<?
include('geturl.php');
?>
<? $subdomain = $_GET['subdomain']; ?>
<html>
<head>
<link href="data:image/x-icon;base64,AAABAAEAEBACAAAAAACwAAAAFgAAACgAAAAQAAAAIAAAAAEAAQAAAAAAQAAAAAAAAAAAAAAAAgAAAAAAAAAAAAAAHtRtAAAAAAAH4AAAD/AAABw4AAA4HAAAOBwAADgcAAA4HAAAOBwAADgcAAA4HAAAOBwAABw4AAAP8AAAB+AAAAAAAAD//wAA+B8AAPAPAADjxwAAx+MAAMfjAADH4wAAx+MAAMfjAADH4wAAx+MAAMfjAADjxwAA8A8AAPgfAAD//wAA" rel="icon" type="image/x-icon">
<Title>Free Web Hosting with Free Subdomain Name</Title>
  <meta charset="UTF-8">
  <meta name="description" content="<?echo $yourdomain;?> - Free Web Hosting with Free Subdomain Name.">
  <meta name="keywords" content="HTML, CSS, JavaScript, PHP, SQL, Hosting, Subdomain Name">
  <meta name="author" content="Mehrab Mazmanian">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="bootstrap.min.css">
<script type="text/javascript">
var searchWinHref = window.location.href;
   if(searchWinHref.indexOf("?i") > -1) { 
   window.location.href = window.location.href.split('?')[0];
   }
</script>
</head>
<body>
<div style="max-width: 400px;" class="container">
<div class="row">
<div class="col"><p>
<div class="card">
<form method=post action="http://order.<?echo $yourdomain;?>/register2.php">
<h6 class="card-header">Free Hosting - Sign Up</h6>
  <div class="card-body">
<div class="input-group mb-3">
<input placeholder="Enter sub-domain name" type=text class="form-control" name=username value="<?echo $subdomain;?>" pattern="[a-z0-9]{4,16}" maxlength="16" oninvalid="this.setCustomValidity('Enter sub-domain name')" oninput="setCustomValidity('')" required>  <span class="input-group-text">.<?echo $yourdomain;?></span>
</div>
<div class="input-group mb-3"><input placeholder="Enter password" type=password class="form-control" name=password pattern=".{6,16}" maxlength="16" oninvalid="this.setCustomValidity('Enter password')" oninput="setCustomValidity('')" id="password" required></div>
<div class="input-group mb-3"><input placeholder="Enter e-mail address" class="form-control" oninvalid="this.setCustomValidity('Enter e-mail')" oninput="setCustomValidity('')" type="email" name=email value="" required></div><div class="input-group mb-3"><input type=hidden name=id value="<?PHP echo $id; ?>"><span class="input-group-text"><img alt="captcha" src="http://order.<? echo $yourdomain;?>/image.php?id=<?PHP echo $id; ?>"></span><input placeholder="Enter captcha" size="10" type=text class="form-control" pattern=".{5,5}" name=number oninvalid="this.setCustomValidity('Enter captcha')" oninput="setCustomValidity('')" required></div>
<center><input type="submit" class="btn btn-primary" value="Register"></center></form></div></div></p></div></div></div>
<div style="padding: 5px; margin: 5px; text-align: left; position: fixed; left: 0; bottom: 0; color: black;">&copy; 20<?php echo date('y'); ?></div><div style="padding: 5px; margin: 5px; text-align: right; position: fixed; right: 0; bottom: 0;"><?php include 'stat.php'; ?></div>
</body>
</html>
