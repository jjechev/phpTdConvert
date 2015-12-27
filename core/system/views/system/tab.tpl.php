<!-- Nav tabs -->
<div class ='clearfix'></div>
<ul class="nav nav-tabs navbar-default">
 <?php foreach(array_keys($data) as $key):?>
  <li><a href="#<?php echo $key;?>" data-toggle="tab" ><?php echo $key;?></a></li>
 <?php endforeach;?>
</ul>

<!-- Tab panes -->
<div class="tab-content">
 <?php foreach(array_keys($data) as $name):?>
  <?php //foreach($data[$name] as $val ):?>
   <div class="tab-pane fade" id="<?php echo $name;?>">
	<?php print View::template('system/table', array ('data' => $data[$name]))?>
   </div>
  <?php //endforeach;?>
 <?php endforeach;?>
</div>
