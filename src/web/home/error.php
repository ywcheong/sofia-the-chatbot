<?php
    require_once('../../structure.php');
    $MASTER = new MasterAction("WEB");
    require_once('../../const.php');
    $MASTER->setAction('is_public', TRUE); 
?>Error, <a href="<?php echo ($MASTER->getAction('WEB_ROOT')); ?>">Goto home</a>