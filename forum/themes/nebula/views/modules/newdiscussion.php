<?php if (!defined('APPLICATION')) exit();
echo Anchor(T('Start a New Discussion'), '/post/discussion'.(array_key_exists('CategoryID', $Data) ? '/'.$Data['CategoryID'] : ''), 'buttonified sidebar_button');
