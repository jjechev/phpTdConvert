<select<?php if (isset($data['name'])) echo ' name="'.$data['name'].'"';if(isset($data['cssSelect'])) echo ' class="'.$data['cssSelect'].'"';?>>
<?php foreach($data['items'] as $key => $val):?>
	<option 
		value="<?php echo $key;?>" 
		<?php if(isset($data['cssOption'])) echo ' class="'.$data['cssOption'].'"';?>
		<?php if(isset($data['disabled']))  echo 'disabled';?> 
	>	<?php echo $val;?></option>
<?php endforeach;?>
</select>