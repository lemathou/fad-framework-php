<?php

class captcha
{

static function disp()
{
?>
<img src="/img/captcha.jpg" alt="Captcha" align="absmiddle" />
<?php
}

static function form_field()
{
?>
<input name="_captcha_code" size="6" /> <?php self::disp(); ?>
<?php
}

static function verify($value)
{

if (!isset($_SESSION["captcha_code"]) || $_SESSION["captcha_code"] != $value)
{
	unset($_SESSION["captcha_code"]);
	return false;
}
else
{
	unset($_SESSION["captcha_code"]);
	return true;
}

}

}

?>