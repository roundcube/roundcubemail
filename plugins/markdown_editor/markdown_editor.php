<?php

class markdown_editor extends rcube_plugin
{
    public $task = 'mail';

    #[Override]
    public function init()
    {
        $this->add_hook('message_compose_body', [$this, 'load']);
    }

    public function load($args)
    {
        // TODO: Download and serve these files ourselves
        $this->include_script('https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js');
        $this->include_stylesheet('https://uicdn.toast.com/editor/latest/toastui-editor.min.css');
        $this->include_script('markdown_editor.js');
        return $args;
    }
}
