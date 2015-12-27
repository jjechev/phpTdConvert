<? /* 
<div id="login">
	<h2>login</h2>
		
		<form id="login-form" action="/login" method="post">
		<p>
			<label for="name">username:</label>
			<input type="text" value="" name="username" id="name"/>
		</p>
		<p>
			<label for="password">password:</label>
			<input type="password" value="" name="password" id="password"/>
		</p>
			<?php if (isset($_GET['url'])):?>
				<input type="hidden" name="redirectUrl" value="<?php echo $url?>">
			<?php endif;?>
		<p>
			<input class="button" name="submit" type="submit" value="login"/>
		</p>
	</form>
</div>
*/
$title = "Вход";

?>
<style>
#user-login form {
    margin: 40px auto 20px;
    max-width: 300px;
    padding: 19px 29px 29px;
}
#user-login form .control-group{
    margin-bottom: 10px;
}
</style>
<div id="user-login" class="container">
	<form id="login-form" action="/login" method="post">
	<div class="well">
		<h2 class="form-signin-heading"><span class="glyphicon glyphicon-log-in"></span> <?php echo $title; ?></h2>
		<div class="control-group">
			<input class="input-block-level field-type-text" id="username" name="username" placeholder="Потребителско име" type="text">
		</div>
		<div class="control-group">
			<input class="input-block-level field-type-password" id="password" name="password" placeholder="Парола" type="password">
		</div>
		<div class="control-group">
			<input class="btn btn-large btn-primary field-type-submit" id="submit" name="submit" value="Вход" type="submit">
		</div>
                <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl?>">
	</div>
        </form>
</div>
<script>
$(function(){
	$('input:text:first').focus();
});
</script>