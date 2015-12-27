<br />
<br />
<br />
<br />


<style>
    .separator{    
        border-left: thick double #666666;
        height: 300px; 
        border-color: #ccc;
    }

    #existingFiles{
        overflow-y: scroll;
        position: relative;
        height: 200px;
        font-family: Verdana;
        font-size: 12px;
    }
    #footer{
        height: 100px;
    }
</style>

<div class="container-fluid container">
    <div class="row">
        <div class ='col-sm-1 text-left'></div>
        <div class ='col-sm-3 text-left'>
            <div class='form-group'> 
                <label>
                    <h3>Видове импорти</h3> 
                </label>
            </div>
            <?php foreach ($importTypes as $importType):?>
            <div class='form-group'> <?php echo $importType?> </div>
            <?php endforeach;?>
        </div>
        <div class ='col-sm-4 separator pull-left '>
            <form method="POST" action="" enctype="multipart/form-data" role="form">
                <div class='row'>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label ><h3>Качване на файл:</h3></label>
                            <br/>
                            <br/>
                            <br/>
                            <?php
                            echo View::template('system/htmlInput', array('data' => array(
                                    'type' => 'file',
                                    'name' => 'uploadFile',
                                    'class' => 'btn btn-default',
                                )
                            ))
                            ?>	
                        </div>
                    </div>
                </div>
                <div class='row'>&nbsp;</div>
                <div class='row'>
                    <div class="col-md-12">
                        <?php
                        echo View::template('system/htmlInput', array('data' => array(
                                'type' => 'submit',
                                'value' => 'Конвертирай',
                                'class' => 'btn btn-primary')
                        ))
                        ?>
                    </div>
                </div>
            </form>
        </div>
        <div class ='col-sm-4 separator pull-left'>
            <label ><h3>Качени файлове:</h3></label>
            <div id="existingFiles">
                <?php foreach ($existingFiles as $file): $file = end(explode('/', $file));?>
                - <a href="<?php echo Settings::getRoute('pageSetExport').'?file='.$file; ?>"><?php echo $file ?></a><br />
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div id = 'footer'></div>
