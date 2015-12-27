<?php
	if (!isset($html['htmlTable']['class'])) $html['htmlTable']['class'] = 'table table-bordered table-hover';

?>

<table class="<?php echo $html['htmlTable']['class'];?>">
	<?php if ($data):?>
		<?php foreach ($data as $arr):?>
			<tr>
				<?php foreach ($arr as $val):?>
					<td>
						<?php echo $val;?>
					</td>
				<?php endforeach;?>
			</tr>
		<?php endforeach;?>
	<?php endif;?>
</table>
