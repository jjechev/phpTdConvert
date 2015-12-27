<br />
<br />
<br />
<br />

<div class="container-fluid container">
    <div class="row">
        <div class ='col-sm-3 text-left'></div>
        <div class ='col-sm-8 text-left'>
            <div class='form-group'> 
                <label>
                    <h3>Вид на импорта</h3> 
                </label>
            </div>
            <form method="POST" action="<?php echo $formAction?>" >
                <?php foreach ($exportsName as $name):?>
                    <div class='form-group'>
                        <?php echo View::template('system/htmlInput', array('data'=>array('type' => 'radio', 'name' => 'convertType', 'value' => $name)))?><?php echo $name;?>
                    </div>
                    <br />
                <?php endforeach; ?>
                    <div class='form-group'><?php echo View::template('system/htmlInput', array('data'=>array('type' => 'hidden', 'name' => 'file', 'value' => $filename)))?></div>
                    <div class='form-group'>
                        <?php
                        echo View::template('system/htmlInput', array('data' => array(
                                'type' => 'submit',
                                'value' => 'Продължаваме напред',
                                'class' => 'btn btn-primary')
                        ))
                        ?>
                    </div>
            </form>
        </div>
        <div class ='col-sm-1 text-left'></div>
    </div>
</div>
