<br />
<br />
<br />
<br />

<div class="container-fluid container">
    <div class="row">
        <div class ='col-sm-3 text-left'>
            <a href="<?php echo Settings::getRoute('pageHome')?>" class="btn btn-primary btn active" role="button">Начало</a>
        </div>
        <div class ='col-sm-8 text-left'> 
            <div class='form-group'> 
                <label>
                    <h3>Хотели</h3> 
                </label>
            </div>
            <form method="POST" action="<?php echo $formAction?>" >
                <?php foreach ($hotelsNames as $name):?>
                    <div class='form-group col-sm-3 pull-left'>
                        <?php echo View::template('system/htmlInput', array('data'=>array('type' => 'checkbox', 'name' => 'hotelsNames[]', 'value' => $name, 'checked' => 'checked' )))?><?php echo $name;?>
                    </div>
                <?php endforeach; ?>
                <div class = 'clearfix'></div>
                    <div class='form-group'><?php echo View::template('system/htmlInput', array('data'=>array('type' => 'hidden', 'name' => 'file', 'value' => $filename)))?></div>
                    <div class='form-group'><?php echo View::template('system/htmlInput', array('data'=>array('type' => 'hidden', 'name' => 'convertType', 'value' => $convertType)))?></div>
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

