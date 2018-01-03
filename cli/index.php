<!DOCTYPE html>
<html>
<head>
	<title>知派</title>
	<meta charset="utf-8">
	<meta name="baidu_union_verify" content="5c13c4926eee7bdfd468bea2d30f753b">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<script src="https://code.jquery.com/jquery-1.11.3.js"></script>

	<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
<?php 

session_start();
if(empty($_SESSION['acc']))
	header('location:/?ope=' . urlencode('/nefuer/cli/index.php'));
$db = mysqli_connect('localhost:3306', 'root', 'GT338570');
mysqli_select_db($db, 'nefuer');
$sql = '
	SELECT `user`.`last_upd`
	FROM `user`
	WHERE `acc` = ' . $_SESSION['acc'] . '
	LIMIT 1;
';
$result = mysqli_query($db, $sql);
$status = mysqli_fetch_row($result)[0] == -1 ? '关闭' : '开启';
$change = $status == '关闭' ? '开启' : '关闭';

mysqli_close($db); ?>
	<div class="container">
		<div class="row clearfix">
			<div class="col-md-12 column">
				<div class="row clearfix" style="margin-top: 100px;">
					<div class="col-md-3 column">
					</div>
					<div class="col-md-6 column">
						<div class="jumbotron" style="background-color: rgb(235, 255, 235);">
							当前状态：<?=$status?>
						</div>
						<form class="form-horizontal" onsubmit="return false;" role="form">
							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-10">
									 <button type="submit" class="btn btn-default" style="width: 100%" id="btn" onclick="changeStatus();" style="float: right;padding-left: 30px; padding-right: 30px"><?=$change?></button>
								</div>
							</div>
						</form>
					</div>
					<div class="col-md-3 column">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="error">
		
	</div>
	<script type="text/javascript" src="/lib/jquery.md5.js"></script> 
	<script type="text/javascript">
		function changeStatus()
		{
			document.getElementById('btn').disabled = true;
			$.ajax({
				url : '/nefuer/cli/change.php',
				method : 'post',
				dataType : 'json',
				success : function(result)
				{
					document.getElementById('btn').disabled = false;
					$('.jumbotron').html('当前状态：' + result['status']);
					$('.btn').html(result['change']);
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					alert(XMLHttpRequest.responseText);
				}
			});
		}
	</script>
</body>
</html>